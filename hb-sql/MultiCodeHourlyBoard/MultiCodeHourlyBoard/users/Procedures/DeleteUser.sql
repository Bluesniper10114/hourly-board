CREATE PROCEDURE [Users].[DeleteUser]
	@profileId bigint
AS
	set nocount on;

	declare @deleteProfileSucceeded bit = 1;
	declare @deleteAccountSucceeded bit = 1;
	update Users.Profile
	set deleted = 1, isActive = 0
	where id = @profileId;
	if (@@ROWCOUNT > 0) SET @deleteProfileSucceeded = 1;

	update Users.Account
	set deleted = 1
	where ProfileId = @profileId;
	if (@@ROWCOUNT > 0) SET @deleteAccountSucceeded = 1;
	if ((@deleteAccountSucceeded = 0) or (@deleteProfileSucceeded = 0))
	BEGIN
		ROLLBACK TRAN;
		RETURN -2; -- something is wrong with the profile, could not delete, or it's already deleted
	END

return 0

