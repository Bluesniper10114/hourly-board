/*
	Author/Date	:	Cristian Dinu, 03.08.2018
	Description	:	global procedure, check any object ID
	LastChange	:	
*/

CREATE PROCEDURE [global].[GetSettingKeyValue]
	@Key				nvarchar(50),
	@ProcedureLogID		bigint,
	@Value				nvarchar(MAX) OUTPUT
AS
	declare	@errorMessage nvarchar(max)

	if @Key is NULL goto KeyIsNull
	
	select @Value = [Value] from [global].Setting where [Key] = @Key
	if @@ROWCOUNT = 0 goto MissingSettingKey

return(0)

KeyIsNull:
	set @errorMessage = N'[DevError]@Key is NULL.'
	goto ErrorExit
MissingSettingKey:
	set @errorMessage = N'[DevError]Miising value for setting key ' + @Key
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = 16, @ErrorMessage = @errorMessage, @ProcedureLogID = @ProcedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
