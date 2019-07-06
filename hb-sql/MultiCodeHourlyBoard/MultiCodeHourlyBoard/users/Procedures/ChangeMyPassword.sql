CREATE PROCEDURE [Users].[ChangeMyPassword]
	@token nvarchar(MAX),
	@newPassword nvarchar(50)
AS
	set nocount on;

	declare @profileId bigint;
	exec @profileId = Users.[GetProfileIdFromToken] @token = @token;

	declare @result int;
	exec @result = Users.[ChangePasswordForProfile] @profileId = @profileId, @newPassword = @newPassword
return @result;

