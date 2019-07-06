CREATE PROCEDURE [log].[LogErrorInfo]
	@procedureId tinyint
AS
		declare @errorNumber int = 0;
		set @errorNumber = ERROR_NUMBER();
		declare @error nvarchar(4000);
		set @error = N'[' + ERROR_PROCEDURE() + N']' + ERROR_MESSAGE()
		exec [log].LogError @procedureId = @procedureId, @errorId = 1, @message = @error, @devError = 'internal error';
RETURN 0