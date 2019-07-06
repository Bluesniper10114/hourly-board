CREATE PROCEDURE [users].[ChangePasswordForProfile]
	@profileId bigint,
	@newPassword nvarchar(50)
AS
	set nocount on;

	if(@profileId is null) goto Error_ProfileNotFound;

	update		[users].[Account]
	set			[Password] = @newPassword
	where		ProfileID = @profileId;

	if (@@ROWCOUNT = 0) goto Error_AccountNotFound;

RETURN 0;
Error_AccountNotFound:
	return -403;
Error_ProfileNotFound:
	return -404;