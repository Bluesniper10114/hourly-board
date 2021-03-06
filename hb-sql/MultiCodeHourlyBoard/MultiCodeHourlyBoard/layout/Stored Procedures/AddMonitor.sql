﻿/*
	Author/Date	:	Cristian Dinu, 19.08.2018
	Description	:	add a new monitor
	LastChange	:	
*/

CREATE PROCEDURE [layout].[AddMonitor]
	@UserID			int,
	@Location		nvarchar(50),
	@Description	nvarchar(255),
	@IPAddress		nvarchar(50),
	@LocationID		char(2),
	@LineID			smallint,
	@ErrorMessage	nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@procedureLogID		bigint

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID, CustomParams)
	values(8, @UserID,
		N'@Location=' + @Location + N', ' +
		N'@Description=' + @Description + N', ' +
		N'@IPAddress=' + @IPAddress + N', ' +
		N'@LocationID=' + @LocationID + N', ' +
		N'@LineID=' + CONVERT(nvarchar(10), @LineID))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		exec [global].CheckObjectID @ObjectSchema = N'layout', @ObjectTable = N'Location', @ObjectID = @LocationID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		exec [global].CheckObjectID @ObjectSchema = N'layout', @ObjectTable = N'Line', @ObjectID = @LineID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		insert into layout.Monitor([Location], [Description], IPAddress, LocationID, LineID)
		values(@Location, @Description, @IPAddress, @LocationID, @LineID)

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
