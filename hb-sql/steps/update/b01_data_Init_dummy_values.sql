-- dummy values for testing
USE MultiCodeBoard
GO


print 'Operator'
insert into users.Operator(Barcode, FirstName, LastName, SecurityLevel, [Role])
values(N'0001', N'Ion', N'Popescu', 99, N'Assy Operator')
GO

print 'Daily target DY - dummy values'
GO
declare @date datetime,
		@i tinyint = 0
--set @date = DATEADD(WEEK, -1, [global].[NextMonday]([global].[GetDate]()))	-- previous monday
set @date = '08/01/2018'

--while @i < 14
while @date < DATEADD(day, 14, [global].[NextMonday]([global].[GetDate]()))
begin
	if DATEPART(WEEKDAY, @date) between 1 and 7
	begin
		insert into [target].Daily(TypeID, LineID, ShiftLogID, [Value], Billboard, UpdateUserID, UpdateDate)
		select 'DY', l.ID, sl.ID, CONVERT(int, (RAND() * 10000 * (l.ID + sl.ID))) % 200 + 800, 1, 1, [global].[GetDate]()
		from layout.Line l
			cross join dbo.ShiftLog sl
		where --l.[Tags] like '%%' and
			sl.[Data] = @date
	end

	set @date = DATEADD(day, 1, @date)
--	set @i += 1
end
GO

print 'Hourly target DY - dummy values'
GO
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 1, [Value] * 0.12, [Value] * 0.12, 1, [global].[GetDate]() from [target].Daily
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 2, [Value] * 0.13, [Value] * 0.13, 1, [global].[GetDate]() from [target].Daily
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 3, [Value] * 0.13, [Value] * 0.13, 1, [global].[GetDate]() from [target].Daily
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 4, [Value] * 0.13, [Value] * 0.13, 1, [global].[GetDate]() from [target].Daily
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 5, [Value] * 0.11, [Value] * 0.11, 1, [global].[GetDate]() from [target].Daily
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 6, [Value] * 0.13, [Value] * 0.13, 1, [global].[GetDate]() from [target].Daily
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 7, [Value] * 0.13, [Value] * 0.13, 1, [global].[GetDate]() from [target].Daily
GO

insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 8, d.[Value] - h.[Value], d.[Value], 1, [global].[GetDate]()
from [target].Daily d
	inner join (
		select DailyID, SUM([Value]) [Value]
		from [target].Hourly
		group by DailyID
	) h on d.ID = h.DailyID
GO

update h
set CumulativeValue = (select SUM([Value])
	from [target].[Hourly]
	where DailyID = h.DailyID
		and [Hour] <= h.[Hour])
from [target].[Hourly] h
GO

print 'Daily target PN - dummy values'
GO
declare @date datetime
set @date = CONVERT(datetime, CONVERT(varchar(10), [global].[GetDate](), 120), 120)

insert into [target].Daily(TypeID, LineID, ShiftLogID, [Value], Billboard, UpdateUserID, UpdateDate)
select 'PN', l.ID, sl.ID, CONVERT(int, (RAND() * 10000 * (l.ID + sl.ID))) % 200 + 800, 0, 1, [global].[GetDate]()
from layout.Line l
	cross join dbo.ShiftLog sl
where l.[Name] = 'Audi'
	and sl.[Data] = @date

insert into [target].PartNumber([Priority], DailyID, PartNumberID, InitialQty, [Value], UpdateUserID, UpdateDate)
select pn.ID, d.ID, pn.ID,
	case d.ShiftType when 'A' then (select SUM([Value]) * 1.2 from [target].vDaily where TypeID = 'PN') else 0 end,
	d.[Value] * pn.ID / 45, 1, [global].[GetDate]()
from [target].vDaily d
	cross join [layout].PartNumber pn
where d.TypeID = 'PN'

update d
set [Value] = dpn.[Value]
from [target].Daily d
	inner join (
		select DailyID, SUM([Value]) [Value]
		from [target].PartNumber
		group by DailyID
	) dpn on d.ID = dpn.DailyID

print 'Hourly target PN - dummy values'
GO
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 1, [Value] * 0.12, [Value] * 0.12, 1, [global].[GetDate]() from [target].Daily where TypeID = 'PN'
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 2, [Value] * 0.13, [Value] * 0.13, 1, [global].[GetDate]() from [target].Daily where TypeID = 'PN'
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 3, [Value] * 0.13, [Value] * 0.13, 1, [global].[GetDate]() from [target].Daily where TypeID = 'PN'
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 4, [Value] * 0.13, [Value] * 0.13, 1, [global].[GetDate]() from [target].Daily where TypeID = 'PN'
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 5, [Value] * 0.11, [Value] * 0.11, 1, [global].[GetDate]() from [target].Daily where TypeID = 'PN'
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 6, [Value] * 0.13, [Value] * 0.13, 1, [global].[GetDate]() from [target].Daily where TypeID = 'PN'
insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 7, [Value] * 0.13, [Value] * 0.13, 1, [global].[GetDate]() from [target].Daily where TypeID = 'PN'
GO

insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
select ID, 8, d.[Value] - h.[Value], d.[Value], 1, [global].[GetDate]()
from [target].Daily d
	inner join (
		select DailyID, SUM([Value]) [Value]
		from [target].Hourly
		group by DailyID
	) h on d.ID = h.DailyID
where TypeID = 'PN'
GO

update h
set CumulativeValue = (select SUM([Value])
	from [target].[Hourly]
	where DailyID = h.DailyID
		and [Hour] <= h.[Hour])
from [target].[Hourly] h
GO

print 'BillboardLog - dummy values'
GO
insert into dbo.BillboardLog(TargetHourlyID, HourInterval, ActualAchieved, CumulativeAchieved, Defects, Downtime, SignedOffOperatorID)
select h.ID TargetHourlyID, h.HourInterval, h.[HourlyTarget] * 0.98 ActualAchieved, 10000,-- 0, 0,
	case when h.[Hour] in (1,4,5) then h.[HourlyTarget] * 0.02 else 0 end, 0,
--	case when h.[Hour] in (2,5) then CONVERT(int, (RAND() * h.[HourlyTarget])) % 60 else 0 end,
	case when DATEDIFF(HOUR, h.[HourStart], [global].[GetDate]()) > 8 then 1 end
from [target].vHourly h
where DATEDIFF(day, [global].[GetDate](), h.[ShiftData]) <= 1
	and h.Billboard = 1
order by h.[Location], h.[ShiftData], h.[ShiftType], HourInterval

update bl
set CumulativeAchieved = (
	select SUM(ActualAchieved)
	from [dbo].BillboardLog a
		inner join [target].Hourly b on a.TargetHourlyID = b.ID
	where b.DailyID = h.DailyID
		and b.[Hour] <= h.[Hour])
from dbo.BillboardLog bl
	inner join [target].Hourly h on bl.TargetHourlyID = h.ID

update d
set Billboard = 0
from target.Daily d
	inner join target.Hourly h on d.ID = h.DailyID
where h.ID not in (select targethourlyid from dbo.BillboardLog)
GO

print 'ShiftLogSignedOff'
GO
insert into dbo.ShiftLogSignOff(ShiftLogID, LineID, SignedOffOperatorID, [Automatic], UpdateDate)
select ShiftLogID, LineID, 1, 1, [global].[GetDate]()
from dbo.vBillboardLog
where SignedOffOperatorID is not NULL
group by ShiftLogID, LineID
having COUNT([Hour]) = 8
GO

insert into dbo.ShiftLogSignOff(ShiftLogID, LineID)
select distinct ShiftLogID, LineID
from dbo.vBillboardLog
where SignedOffOperatorID is NULL
group by ShiftLogID, LineID
GO

print 'Downtime - dummy records'
GO
insert into dbo.Downtime(TargetHourlyID, WorkbenchID, DataStart, DataEnd)
select a.TargetHourlyID, a.ID,
	DATEADD(MINUTE, 30 - a.Duration/2, a.HourStart),
	DATEADD(MINUTE, 30 + a.Duration/2, a.HourStart)
from (
	select bl.TargetHourlyID, bl.ShiftDataStart, bl.[Hour], bl.LineID, w.ID, bl.HourStart,
		CONVERT(int, RAND() * 10000 * (bl.LineID + w.ID)) % 40 + 5 Duration
	from dbo.vBillboardLog bl
		inner join layout.Cell c on bl.LineID = c.LineID
		inner join layout.Workbench w on c.ID = w.CellID
	where --SignedOffOperatorID is NULL and 
		HourEnd < [global].[GetDate]()
		and [Hour] % 3 = 1
		and w.ID % 4 = 1
) a
order by a.LineID, a.ShiftDataStart, a.[Hour]
GO

insert into dbo.DowntimeDetails(DowntimeID, Comment, Duration, UpdateDate)
select d.ID, dd.[Text], DATEDIFF(MINUTE, d.DataStart, d.DataEnd), [global].[GetDate]()
from dbo.Downtime d
	left join dbo.DowntimeDictionary dd on d.ID % 29 = dd.ID - 1
GO

print 'Monitor - dummy records'
GO
insert into layout.Monitor([Location], [Description], IPAddress, LocationID, LineID)
select N'Monitor - ' + [Name], [Name], N'192.168.100.' + SUBSTRING(CONVERT(nvarchar(4), 1000 + ID), 2, 3), 'TM', ID
from layout.Line
where ID in (select LineID from [target].[Daily])
GO

print 'Dummy capacity values for workbenches with missing values'
update layout.Workbench set HourCapacity = 100 where HourCapacity = 0
