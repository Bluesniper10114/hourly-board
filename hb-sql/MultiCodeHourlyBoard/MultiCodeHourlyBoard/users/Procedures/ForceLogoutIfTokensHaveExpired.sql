CREATE PROCEDURE [Users].[ForceLogoutIfTokensHaveExpired]
AS
	set nocount on;
	declare @token nvarchar(MAX) = null;
	declare @errorNumber int = 0;

	declare @tokensTable table (Token nvarchar(MAX), workbenchId int);

	insert into @tokensTable
	select Token, WorkbenchId
	from Users.AccountToken act
	where ((act.IsActive = 1) or (act.LogoutTime is null))
		and act.Expire < dbo.GetUTCDate();

	update act
	set IsActive = 0,
		LogoutTime = dbo.GetUTCDate(),
		AutomaticLogout = 1
	from Users.AccountToken act
		inner join @tokensTable t on t.Token = act.Token 

RETURN 0;
