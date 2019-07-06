/*
	Deletes all the authentication tokens which have expired.
	You can call this periodically or when a user attempts to login
*/
CREATE PROCEDURE [users].[ClearExpiredTokens]
AS
	SET NOCOUNT ON

	update [users].AccountToken 
	set IsActive = 0
	WHERE Expire < [global].[GetDate]()

RETURN 0