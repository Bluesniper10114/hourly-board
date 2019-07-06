CREATE PROCEDURE [users].[ForceLogoutIfTokensHaveExpired]
AS
	set nocount on;
	declare @token nvarchar(MAX) = null;
	declare @errorNumber int = 0;

	declare @tokensTable table (Token nvarchar(MAX), WorkbenchID int);

	begin try
		begin tran
		insert into @tokensTable
		select Token, WorkbenchID
		from [users].AccountToken act
		where ((act.IsActive = 1) or (act.LogoutTime is null))
			and act.Expire < [global].[GetDate]()

		update act
		set IsActive = 0,
			LogoutTime = [global].[GetDate](),
			AutomaticLogout = 1
		from [users].AccountToken act
			inner join @tokensTable t on t.Token = act.Token 

		update wbs
		set LoggedInProfileID = null
		from [layout].[WorkbenchStatus] wbs
			inner join @tokensTable t on wbs.WorkbenchID = t.WorkbenchID
		commit tran
	end try
	begin catch
		if (@@TRANCOUNT > 0) rollback tran;
		set @errorNumber = -1;
	end catch

RETURN @errorNumber;