CREATE PROCEDURE [users].[Logout]
	@token nvarchar(MAX)
AS
	set nocount on;
	DECLARE @workbenchId BIGINT
	DECLARE @profileId BIGINT

	exec [users].[ForceLogoutIfTokensHaveExpired];

	begin try
		begin tran
		update act
		set act.LogoutTime = [global].[GetDate](),
			act.IsActive = 0
		from [users].AccountToken act
		inner join [users].[Account] a on act.AccountID = a.ID
		left join [layout].WorkbenchStatus wbs on act.WorkbenchID = wbs.WorkbenchID
		where act.Token = @token;

		update wbs
		set wbs.LoggedInProfileID = null
		from [users].AccountToken act
		inner join [layout].WorkbenchStatus wbs on act.WorkbenchID = wbs.WorkbenchID
		where act.Token = @token;

		commit tran
		return 0;
	end try
	begin catch
		if (@@TRANCOUNT > 0) rollback tran;
		PRINT ERROR_MESSAGE()
	end catch
RETURN -1 -- failed