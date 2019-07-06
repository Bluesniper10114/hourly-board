CREATE PROCEDURE [log].[LogError]
	@procedureId tinyint,
	@message nvarchar(4000),
	@errorId int = null,
	@dboError int = null,
	@devError nvarchar(4000) = null
AS
	set nocount on

	insert into [log].ProcedureLog
		(ProcedureID, [Message], ErrorID, DevError, DboError)
	values
		(@procedureId, @message, @errorId, @devError, @dboError)
RETURN 0