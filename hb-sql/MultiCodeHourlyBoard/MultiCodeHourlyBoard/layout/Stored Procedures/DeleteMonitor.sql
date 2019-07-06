/*
	Author/Date	:	Cristian Dinu, 19.08.2018
	Description	:	delete monitor record
	LastChange	:	
*/

CREATE PROCEDURE [layout].[DeleteMonitor]
	@UserID			int,
	@MonitorID		int,
	@ErrorMessage	nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@procedureLogID		bigint

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID, CustomParams)
	values(10, @UserID,
		N'@MonitorID=' + CONVERT(nvarchar(10), @MonitorID))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		exec [global].CheckObjectID @ObjectSchema = N'layout', @ObjectTable = N'Monitor', @ObjectID = @MonitorID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		update layout.Monitor
		set Deleted = 1
		where ID = @MonitorID

	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @ErrorMessage =  ERROR_MESSAGE()
		goto ErrorExit
	end catch
return(0)

ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @ErrorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
