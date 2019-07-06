/*
	Deletes all the authentication tokens which have expired.
	You can call this periodically or when a user attempts to login
*/
CREATE PROCEDURE [Users].[ClearExpiredTokens]
AS
	SET NOCOUNT ON

	update Users.AccountToken 
	set IsActive = 0
	WHERE Expire < dbo.GetUTCDate()

RETURN 0
GO