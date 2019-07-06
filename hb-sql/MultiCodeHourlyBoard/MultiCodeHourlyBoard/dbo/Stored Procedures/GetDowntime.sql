/*
	Author/Date	:	Cristian Dinu, 25.08.2018
	Description	:	get downtime intervals
	LastChange	:	
*/

CREATE PROCEDURE [dbo].[GetDowntime]
	@TargetHourlyID	int,
	@XML			XML	OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(16, N'@targetHourlyID=' + CONVERT(nvarchar(10), @TargetHourlyID))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try

		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectTable = N'BillboardLog', @ObjectColumnID = N'TargetHourlyID', @ObjectID = @TargetHourlyID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		set @XML = (
			select CONVERT(nvarchar(10), ShiftData, 120) + N' ' + REPLACE(HourInterval, N' ', N'-') 'forDate', (
				select d.ID '@id', w.[Name] 'machine',
					CONVERT(nchar(5), d.DataStart, 108) + N' ' + CONVERT(nchar(5), d.DataEnd, 108) 'timeInterval',
					DATEDIFF(MINUTE, d.DataStart, d.DataEnd) 'totalDuration',
					(select ID '@id', CONVERT(char(24), UpdateDate, 121) '@timeStamp', 
						Comment 'comment', Duration 'duration'
					from dbo.DowntimeDetails
					where DowntimeID = d.ID
					order by d.DataStart
					for xml path('reason'), root('reasons'), type)
				from dbo.Downtime d
					inner join layout.Workbench w on d.WorkbenchID = w.ID
				where d.TargetHourlyID = @TargetHourlyID
				for xml path('row'), type)
			from dbo.vBillboardLog
			where TargetHourlyID = @TargetHourlyID
			for xml path(''), root('root')) 
		if @@ROWCOUNT = 0 goto EmptyXML

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
	set @errorMessage = N'Empty XML dataset'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @errorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
