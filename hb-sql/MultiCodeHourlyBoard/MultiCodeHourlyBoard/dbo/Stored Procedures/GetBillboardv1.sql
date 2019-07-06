/*
Author/Date	:	Cristian Dinu, 13.01.2019
Description	:	get billboard data with recordset data
LastChange	:
*/

CREATE PROCEDURE [dbo].[GetBillboardv1]
	@MonitorID	int
AS
	set nocount on

	declare @targetDailyID	int,
			@shiftLogID	int,
			@lineID	smallint,	
			@errorNumber	int = 16,
			@errorMessage	nvarchar(max),
			@procedureLogID	bigint

	declare @billboardLog table(
			TargetHourlyID	int,
			HourStart	datetime,
			HourEnd	datetime,
			[Hour]	tinyint,
			[HourInterval]	nchar(11),
			[Target]	smallint,
			CumulativeTarget	smallint,
			ActualAchieved	smallint,
			CumulativeAchieved	smallint,
			Defects	smallint,
			Downtime	int,
			Comment	nvarchar(100),
			Escalated	nvarchar(50),
			SignedOffOperatorID	int,
			SignedOffOperatorBarcode nvarchar(50))

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(11, N'@MonitorID=' + CONVERT(nvarchar(10), @MonitorID))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try

		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'layout', @ObjectTable = N'Monitor', @ObjectID = @MonitorID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		-- get necessary informations for resulting xml dataset
		select top 1
			@targetDailyID = d.ID,
			@shiftLogID = slso.ShiftLogID,
			@lineID = m.LineID
		from layout.Monitor m
			cross join dbo.ShiftLog sl
			inner join dbo.ShiftLogSignOff slso on m.LineID = slso.LineID and sl.ID = slso.ShiftLogID
			left join [target].Daily d on m.LineID = d.LineID and slso.ShiftLogID = d.ShiftLogID
		where m.ID = @MonitorID
			and d.Billboard = 1
			and slso.SignedOffOperatorID is NULL
		order by sl.DataStart

		-- if target is missing there is no plan nor actuals values, all values are 0
		if @targetDailyID is NULL goto MissingTarget

		insert into @billboardLog(TargetHourlyID, HourStart, HourEnd, [Hour], [HourInterval], [Target], CumulativeTarget, ActualAchieved, CumulativeAchieved,
			Defects, Downtime, Comment, Escalated, SignedOffOperatorID, SignedOffOperatorBarcode)
		select TargetHourlyID, HourStart, HourEnd, [Hour], [HourInterval], [Target], CumulativeTarget, ActualAchieved, CumulativeAchieved,
			Defects, Downtime, Comment, Escalated, SignedOffOperatorID, SignedOffOperatorBarcode
		from vBillboardLog
		where TargetDailyID = @targetDailyID

		-- header info
		select
			CONVERT(nvarchar(10), sl.[Data], 20) 'date', sl.ShiftName 'shift',
			l.[Name] 'lineName', sl.LocationName 'locationName',
			CONVERT(decimal(6,2), ROUND((
				select CONVERT(decimal(10,2), MAX(CumulativeAchieved)) /
					CONVERT(decimal(10,2), case 
						when MAX(HourEnd) < [global].[GetDate]() then 440
						when MIN(HourStart) = [global].[GetDate]() then 1
						else DATEDIFF(MINUTE, MIN([HourStart]), [global].[GetDate]()) / 12 * 11
					end)
				from @billboardLog
			), 2)) 'deliveryTime',
			(
				select MAX(ActualAchieved)
				from @billboardLog
			) 'maxHourProduction',
			slso.ID '@shiftLogSignOffID'
		from layout.Line l
			cross join dbo.vShiftLog sl
			inner join dbo.ShiftLogSignOff slso on l.ID = slso.LineID and sl.ID = slso.ShiftLogID
		where l.ID = @lineID
			and sl.ID = @shiftLogID

		-- table info
		select	bl.TargetHourlyID 'id',
			case when bl.SignedOffOperatorID is not NULL then 'yes' else 'no' end 'closed',
			case when fo.[Hour] is not NULL then 'yes' else 'no' end 'firstOpen',
			bl.HourInterval 'hourInterval', bl.[Target] 'target', bl.CumulativeTarget 'cumulativeTarget',
			bl.ActualAchieved 'achieved', bl.CumulativeAchieved 'cumulativeAchieved', bl.Defects 'defects',
			bl.Downtime 'downtime', bl.Comment 'comment', bl.Escalated 'escalated', bl.SignedOffOperatorBarcode 'signoff'
		from @billboardLog bl
			left join (
				select MIN([Hour]) [Hour]
				from @billboardLog
				where SignedOffOperatorID is NULL
			) fo on bl.[Hour] = fo.[Hour]
		order by bl.[Hour]
	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @errorMessage = ERROR_MESSAGE()
		goto ErrorExit
	end catch
return(0)

MissingTarget:
	set @errorMessage = N'Missing target on current shift or shift is closed'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @errorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)