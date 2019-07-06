CREATE PROCEDURE [Users].[InsertOrUpdateToken]
	@token nvarchar(MAX),
	@userAccountId bigint
AS
	SET NOCOUNT ON

	if (@userAccountId is null) return -1;

	declare @expireDate datetimeoffset(3);

	set @expireDate = DATEADD(SECOND, Users.TokenExpirationInSeconds(), dbo.GetUTCDate());

	update [Users].AccountToken 
	set Expire = @expireDate
	where Token = @token;

	if @@rowcount = 0
	begin
		INSERT INTO [Users].AccountToken(AccountId,Token,Expire) VALUES(@userAccountId,@token,@expireDate);
	end
RETURN 1
GO