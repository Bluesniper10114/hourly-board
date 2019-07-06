/*
	Author/Date	:	Cristian Dinu, 08.10.2018
	Description	:	get historical billboard data
	LastChange	:
*/
CREATE PROCEDURE [report].[HistoricalShift]
	@LineID			int,		-- mandatory
	@Date			datetime,	-- mandatory
	@ShiftType		char(1),	-- mandatory
	@XML			XML				OUTPUT,
	@ErrorMessage	nvarchar(MAX)	OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@procedureLogID		bigint

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(24, N'@LineID=' + CONVERT(nvarchar(10), @LineID) + N','
				+ N'@Date=' + CONVERT(nvarchar(10), @Date, 121) + N','
				+ N'@ShiftType' + @ShiftType)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try

		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'layout', @ObjectTable = N'Line', @ObjectID = @LineID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		-- check if selected Line/Date/ShiftType is signedoff
		select @ErrorMessage = N'Specified shift is not yet signed off. See monitors list'
		from dbo.ShiftLogSignOff slso
			inner join dbo.ShiftLog sl on slso.ShiftLogID = sl.ID
		where slso.LineID = @LineID
			and sl.[Data] = @date
			and sl.ShiftType = @ShiftType
			and slso.SignedOffOperatorID is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		set @XML = (
			select top 1
				CONVERT(nvarchar(10), d.[Data], 20) 'date', d.ShiftName 'shift',
				l.[Name] 'lineName', d.LocationName 'locationName',
				CONVERT(decimal(6,2), ROUND((
					select CONVERT(decimal(10,2), MAX(CumulativeAchieved)) /
						CONVERT(decimal(10,2), case 
							when MAX(HourEnd) < [global].[GetDate]() then 440
							when MIN(HourStart) = [global].[GetDate]() then 1
							else DATEDIFF(MINUTE, MIN([HourStart]), [global].[GetDate]()) / 12 * 11
						end)
					from dbo.vBillboardLog
					where TargetDailyID = d.ID
				), 2)) 'deliveryTime',
				(
					select MAX(ActualAchieved)
					from dbo.vBillboardLog
					where TargetDailyID = d.ID
				) 'maxHourProduction',
				o.FirstName + N' ' + o.LastName 'shiftSignOff',
				(select
					(select	bl.TargetHourlyID '@id',
						bl.HourInterval 'hourInterval', bl.[Target] 'target', bl.CumulativeTarget 'cumulativeTarget',
						bl.ActualAchieved 'achieved', bl.CumulativeAchieved 'cumulativeAchieved', bl.Defects 'defects',
						bl.Downtime 'downtime', bl.Comment 'comment', bl.Escalated 'escalated', bl.SignedOffOperator 'signoff'
					from dbo.vBillboardLog bl
						inner join [target].vHourly h on bl.TargetHourlyID = h.ID
					where bl.TargetDailyID = d.ID
					order by bl.[Hour]
					for xml path('hour'), type, elements XSINIL)
				for xml path('hours'), type)
			from layout.Line l
				inner join [target].vDaily d on l.ID = d.LineID
				inner join dbo.ShiftLogSignOff slso on d.LineID = slso.LineID and d.ShiftLogID = slso.ShiftLogID
				INNER JOIN [users].Operator o on slso.SignedOffOperatorID = o.ID
			where l.ID = @LineID
				and d.ShiftType = @ShiftType
				and d.[Data] = @Date
				and d.Billboard = 1
			for xml path('billboard'), type)
		if @XML is NULL goto EmptyXML

		update [log].ProcedureLog
		set XMLParam = @xml
		where ID = @procedureLogID
	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @errorMessage =  ERROR_MESSAGE()
		goto ErrorExit
	end catch
return(0)

EmptyXML:
	set @ErrorMessage = N'There are no results data for specified filtering conditions'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @ErrorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
