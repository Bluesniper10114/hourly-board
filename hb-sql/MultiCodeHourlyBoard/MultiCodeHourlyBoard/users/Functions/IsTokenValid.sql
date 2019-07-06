/*
* Checks if a token is valid. Returns 1 if the token exists.
*/
CREATE FUNCTION [users].[IsTokenValid]
(
	@token nvarchar(MAX)
)
RETURNS BIT
AS
BEGIN
	
	declare @valid bit = 0;
	--TODO: check if @profileId has rights to cancel
	SELECT @valid = 1
	FROM [users].[AccountToken]
	WHERE Token = @token 
	AND IsActive = 1
	return @valid;
END