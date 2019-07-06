/*
	Author/Date	:	Cristian Dinu, 07.08.2018
	Description	:	get current workbenches list for editing
	LastChange	:	
*/

CREATE PROCEDURE [layout].[GetProductionLines]
	@UserID	int,
	@XML	XML	OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint,
			@timeStamp			datetime,
			@timeOut			smallint

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID)
	values(5, @UserID)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		exec [global].GetSettingKeyValue @Key = N'SESSION_EXPIRES_IN_MINUTES', @ProcedureLogID = @procedureLogID, @Value = @timeOut OUTPUT

		set @XML = (
			select CONVERT(char(23), [global].[GetDate](), 121) [timeStamp],
				(select LocationID [location], [Name] [name], [Description] [description],
					[Tags] [tags], [TimeOut] [timeOut]
				from layout.Line
				order by LocationID, [Name]
				for xml path('line'), root('lines'), type, elements XSINIL),
				(select l.LocationID [location], c.[Name] [name], c.[Description] [description],
					l.[Name] [line], ISNULL(c.[TimeOut], l.[TimeOut]) [timeOut]
				from layout.Cell c
					inner join layout.Line l on c.LineID = l.ID
				order by l.LocationID, l.[Name], c.[Name]
				for xml path('cell'), root('cells'), type, elements XSINIL),
				(select l.LocationID [location], l.[Name] [line], c.[Name] [cell], 
					w.[Name] [name], w.[Description] [description], w.ExternalReference [reference],
					pw.[Name] [previousMachine], w.EOL [eol], wt.[Name] [machineType],
					w.HourCapacity [capacity], w.Routing [routing], ISNULL(w.[TimeOut], ISNULL(c.[TimeOut], l.[TimeOut])) [timeOut]
				from layout.Workbench w
					inner join layout.Cell  c on w.CellID = c.ID
					inner join layout.Line l on c.LineID = l.ID
					left join layout.Workbench pw on w.PreviousWorkbenchID = pw.ID
					left join layout.WorkbenchType wt on w.TypeID = wt.ID
				order by l.LocationID, l.Name, c.Name, w.Name
				for xml path('machine'), root('machines'), type, elements XSINIL)
			for xml path('root'), type)
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
