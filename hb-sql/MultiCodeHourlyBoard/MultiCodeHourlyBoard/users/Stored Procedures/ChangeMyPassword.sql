CREATE PROCEDURE [users].[ChangeMyPassword]
	@token nvarchar(MAX),
	@newPassword nvarchar(50)
AS
	set nocount on;

	declare @profileId bigint;
	exec @profileId = [users].[GetProfileIdFromToken] @token = @token;

	declare @result int;
	exec @result = [users].[ChangePasswordForProfile] @profileId = @profileId, @newPassword = @newPassword
return @result;