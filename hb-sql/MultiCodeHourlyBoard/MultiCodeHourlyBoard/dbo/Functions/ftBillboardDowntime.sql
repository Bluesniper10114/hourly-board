/*
	Function	: [dbo].[ftBillboardDowntime]
	Author/Date	: Cristian Dinu, 22.12.2018
	Description	: return downtime list for specified shiftlog
	LastChange	:	
*/

CREATE function [dbo].[ftBillboardDowntime] (@ShiftLogID int)  
returns @downtime table
(
	LineID			int,
	WorkbenchID		int,
	[Hour]			tinyint,
	Downtime		int
)
as  
begin
	declare @heartBeat smallint = 300 -- secunde
	declare @actualsLog table(
		ID int NOT NULL IDENTITY(1,1) PRIMARY KEY, 
		[Date] datetime NOT NULL, 
		WorkbenchID int NOT NULL, 
		[Hour] tinyint NOT NULL,
		IsBreak bit NOT NULL,
		EventType tinyint NOT NULL)

	-- aduna evenimentele din ActualsLog
	insert into @actualsLog([Date], WorkbenchID, [Hour], IsBreak, EventType)
	select a.[Date], a.WorkbenchID, a.[Hour], a.IsBreak, a.EventType
	from (
		-- inceput schimb
		select distinct sl.DataStart [Date], al.WorkbenchID, 1 [Hour], case when slb.ShiftLogID is NULL then 0 else 1 end IsBreak, 10 EventType 
		from dbo.vShiftLog sl
			inner join dbo.ActualsLog al on sl.ID = al.ShiftLogID
			left join dbo.ShiftLogBreak slb on al.ShiftLogID = slb.ShiftLogID and sl.DataStart between slb.TimeStart and slb.TimeEnd
		where sl.ID = @ShiftLogID

		union all

		-- inceput ora
		select distinct h.HourStart [Date], al.WorkbenchID, h.[Hour], case when slb.ShiftLogID is NULL then 0 else 1 end IsBreak, 11 EventType
		from [target].vHourly h
			inner join dbo.ActualsLog al on h.ShiftLogID = al.ShiftLogID
			left join dbo.ShiftLogBreak slb on al.ShiftLogID = slb.ShiftLogID and h.HourStart between slb.TimeStart and slb.TimeEnd
		where h.ShiftLogID = @ShiftLogID
			and h.[Hour] > 1

		union all

		-- sfarsit schimb
		select distinct sl.DataEnd [Date], al.WorkbenchID, 8 [Hour], case when slb.ShiftLogID is NULL then 0 else 1 end IsBreak, 12 EventType
		from dbo.vShiftLog sl
			inner join dbo.ActualsLog al on sl.ID = al.ShiftLogID
			left join dbo.ShiftLogBreak slb on al.ShiftLogID = slb.ShiftLogID and sl.DataEnd between slb.TimeStart and slb.TimeEnd
		where sl.ID = @ShiftLogID

		union all

		-- inceput pauza
		select distinct slb.TimeStart [Date], al.WorkbenchID, DATEDIFF(HOUR, sl.DataStart, slb.TimeStart) + 1 [Hour], 1 IsBreak, 20 EventType
		from dbo.ShiftLogBreak slb
			inner join dbo.ShiftLog sl on slb.ShiftLogID = sl.ID
			inner join dbo.ActualsLog al on slb.ShiftLogID = al.ShiftLogID
		where slb.ShiftLogID = @ShiftLogID

		union all

		-- sfarsit pauza
		select distinct slb.TimeEnd [Date], al.WorkbenchID, DATEDIFF(HOUR, sl.DataStart, slb.TimeEnd) + 1 [Hour], 1 IsBreak, 21 EventType
		from dbo.ShiftLogBreak slb
			inner join dbo.ShiftLog sl on slb.ShiftLogID = sl.ID
			inner join dbo.ActualsLog al on slb.ShiftLogID = al.ShiftLogID
		where slb.ShiftLogID = @ShiftLogID

		union all

		-- citiri din ActualsLog
		select al.[Date], al.WorkbenchID, al.[Hour], case when slb.ShiftLogID is NULL then 0 else 1 end IsBreak, 30 EventType
		from dbo.vActualsLog al
			left join dbo.ShiftLogBreak slb on al.ShiftLogID = slb.ShiftLogID and al.[Date] between slb.TimeStart and slb.TimeEnd
		where al.ShiftLogID = @ShiftLogID
	) a
	order by a.WorkbenchID, a.[Date]

	-- elimina operatorii cu operatiuni doar in prima ora sau ultima ora
	-- se considera ca sunt operatori care fie au inceput lucrul mai devreme
	-- fie au terminat mai tarziu
	delete _al
	from @actualsLog _al
		inner join (
			select WorkbenchID
			from @actualsLog
			group by WorkbenchID
			having MAX([Hour]) <= 1 or MIN([Hour]) >= 8
		) _wo on _al.WorkbenchID = _wo.WorkbenchID

	insert into @downtime(LineID, WorkbenchID, [Hour], Downtime)
	select w.LineID, a.WorkbenchID, a.[Hour], SUM(a.Durata)
	from (
		select _r0.WorkbenchID, _r0.[Hour],
			DATEDIFF(SECOND, _r0.[Date], _r1.[Date]) Durata, 
			case
				when _r0.[Hour] = 1 and _r1.[Hour] = 1 and _r0.EventType = 10 and _r1.EventType = 30 then 0
				when _r0.IsBreak = 1 and _r1.IsBreak = 1 then 0
				else 1
			end DownTime
		from @actualsLog _r0
			inner join @actualsLog _r1 on _r0.WorkbenchID = _r1.WorkbenchID and _r0.ID = _r1.ID - 1
		where DATEDIFF(SECOND, _r0.[Date], _r1.[Date]) > @heartBeat
			and _r1.[Date] < [global].[GetDate]()
	) a
		inner join layout.vWorkbench w on a.WorkbenchID = w.ID
	where a.DownTime = 1
	group by w.LineID, a.WorkbenchID, a.[Hour]

	return
end;
GO
