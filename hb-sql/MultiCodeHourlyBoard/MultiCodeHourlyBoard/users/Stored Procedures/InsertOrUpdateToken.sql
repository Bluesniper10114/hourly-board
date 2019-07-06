CREATE PROCEDURE [users].[InsertOrUpdateToken]
	@token nvarchar(MAX),
	@userAccountId int,
	@workbenchId int
AS
	SET NOCOUNT ON

	if (@userAccountId is null) return -1;

	declare @expireDelta int; -- seconds to keep the token alive
	declare @expireDate datetime;

	select @expireDelta = cast (s.[Value] as int) 
	from [global].[Setting] s
	where s.[Key] = 'AUTH_TOKEN_EXPIRES_IN_SECONDS';

	set @expireDate = DATEADD(SECOND, @expireDelta, [global].[GetDate]())

	update [users].AccountToken 
	set Expire = @expireDate
	where Token = @token;

	if @@rowcount = 0
	begin
		INSERT INTO [users].AccountToken(AccountID, Token, Expire, WorkbenchID) VALUES(@userAccountId, @token, @expireDate, @workbenchId);
	end
RETURN 1