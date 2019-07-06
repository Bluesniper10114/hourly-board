CREATE PROCEDURE [Users].[Logout]
	@token nvarchar(MAX)
AS
	set nocount on;
	DECLARE @workbenchId BIGINT
	DECLARE @profileId BIGINT

	exec [Users].[ForceLogoutIfTokensHaveExpired];

	update act
	set act.LogoutTime = dbo.GetUTCDate(),
		act.IsActive = 0
	from Users.AccountToken act
	inner join Users.[Account] a on act.AccountId = a.id
	where act.Token = @token;

RETURN -1 -- failed
