/*
	Author/Date	:	Cristian Dinu, 03.08.2018
	Description	:	Global procedure for save error in ProcedureLog table
	LastChange	:	
*/

CREATE PROCEDURE [global].[TraceError]
	@ErrorNumber	int,
	@ErrorMessage	nvarchar(MAX),
	@ProcedureLogID	bigint,
	@ReturnError	int OUTPUT
as
	set @ReturnError = -1
	if @@TRANCOUNT = 0
			update [log].ProcedureLog set DboError = @ErrorNumber, DevError = @ErrorMessage where ID = @ProcedureLogID

	-- all stored procedures use begin try ... catch model for manage errors
	-- so raiserror should be activate, except the case that calling procedures is the the outer (the first one)
	-- because no exception (raiserror) should be activated to Marian application (only return < 0)
	if @@NESTLEVEL > 2 raiserror(@ErrorMessage, 16, 1)

	-- search for any return parameter in error message string
	-- custom error messages (ID's from Error table) can be passed to Marian application by return value of exec sp (negative values)
	declare @i int
	set @i = CHARINDEX(N'[return=', @ErrorMessage, 1) + 8
	if @i <> 8	set @ReturnError = - CONVERT(int, SUBSTRING(@ErrorMessage, @i, CHARINDEX(N']', @ErrorMessage, @i) - @i))

return(0)
