/*
 *  Checks if an operator with the given credentials exist (barcode = username, password = password)
 *  Logs in the operator on the given workbench
 *		NOTE: if the operator is already logged in on another workbench, he gets automatically logged out
 *	Creates an account token for the profile
 *	Returns the account token and the profileId if successful
 *	It tries repeatedly to generate a unique token. If it fails, it returns an error.

	pseudocode
 	#1 validate input params
	#2 find the account by barcode and password
	#3 force logout if tokens have expired (set LogoutTime = [global].[GetDate](), IsActive = 0, LoggedInProfileId in WBStatus on null)
	#4 throw error if user is already logged in on another workbench
	#5 do the login

 *
 * @param: @barcode	- operator barcode as displayed on the badge
 * @param: @password - md5 encrypted password
 * @param: @workbenchId - target workbench where to login
 */
CREATE PROCEDURE [users].[LoginOnWorkbench]
	@barcode nvarchar(50),
	@password nvarchar(50),
	@workbenchId int = null,
	@token nvarchar(MAX) OUTPUT,
	@profileId int OUTPUT
AS
	set nocount on;
	declare @errorNumber int = 0;
 	--#1 validate input params

	-- basic checks for username and password
	if (ltrim(rtrim(@barcode)) = N'') goto Error_UsernameEmpty;
	if (ltrim(rtrim(@password)) = N'') goto Error_PasswordEmpty;

	set @token = null;
	set @profileId = 0;
	
	declare @workbenchExists bit = 0;
	select @workbenchExists = 1 from [layout].Workbench where ID = @workbenchId or (@workbenchId is null);
	if (@workbenchExists = 0) goto Error_WorkbenchDoesNotExist;

	begin try
		declare @userAccountId int;

		select		@userAccountId = a.ID, @profileId = a.ProfileID 
		from		[users].Account a
		inner join	[users].Profile p on a.ProfileID = p.ID
		where		ISNULL(a.[Username], '') = @barcode 
					and ISNULL(a.[Password], '') = @password 
					and a.[AccountProviderID] = 1 -- TrueHR
					and ISNULL(p.IsActive, 0) = 1

		if ((@userAccountId is null) or (@profileId is null) or (@workbenchId is null)) 
		begin 
			set @errorNumber = -441; 
			goto Error_Other; 
		end -- throw 50441, N'Error_WrongCredentials', 1;	
	
		-- #3 force logout if tokens have expired (set LogoutTime = [global].[GetDate](), IsActive = 0, LoggedInProfileId in WBStatus on null)
		exec [users].[ForceLogoutIfTokensHaveExpired];
		
		-- #4 throw error if user is already logged in on another workbench
		-- look up user on other workbenches (this workbench is excluded)
		declare @currentWorkbenchId int;
		select @currentWorkbenchId = wb.ID
		from [layout].[Workbench] wb
		inner join [layout].[WorkbenchStatus] wbs on wbs.WorkbenchID = wb.ID
		where ISNULL(wbs.LoggedInProfileID, 0) = @profileId and (wb.ID <> @workbenchId)

		-- user is logged in elsewhere, throw error
		if (@currentWorkbenchId is not null)
		begin 
			set @errorNumber = -551; 
			goto Error_Other; 
		end  --throw 50551, N'Error_UserLoggedInOnOtherWorkbench', 1;

		-- #5 do the login
		-- check if there is another user logged in on the worbench except myself

		DECLARE @loggedInUser BIGINT
		SELECT @loggedInUser = LoggedInProfileID FROM [layout].WorkbenchStatus where WorkbenchID = @workbenchId and ISNULL(LoggedInProfileID, 0) <> @profileId
		IF @loggedInUser is not null
		begin 
			set @errorNumber = -554; 
			goto Error_Other; 
		end 	--throw 50554, N'Login_OtherUserAlreadyLoggedInOnWorkbench', 1;

		begin tran
		-- login operator on @workbenchId
		update [layout].[WorkbenchStatus]
		set LoggedInProfileID = @profileId
		where [layout].[WorkbenchStatus].WorkbenchID = @workbenchId

		-- create a new account token for the user
		SET @token = convert(nvarchar(MAX), newid())
		exec [users].InsertOrUpdateToken @token = @token, @userAccountId = @userAccountId, @workbenchId = @workbenchId;
		commit tran
	end try
	begin catch
		set @errorNumber = ERROR_NUMBER();
		declare @error nvarchar(4000);
		set @error = N'[' + ERROR_PROCEDURE() + N']' + ERROR_MESSAGE()

		declare @procedureId tinyint = [log].[GetProcedureID]('[Users].[LoginWithOperatorBarcode]');
		exec [log].LogError @procedureId = @procedureId, @errorId = 1, @message = @error, @devError = 'internal error login on workbench';
		goto Error;
	end catch
		
RETURN 0;
Error:
	if (@@TRANCOUNT > 0) rollback tran;
Error_Other:
	return @errorNumber;
Error_UsernameEmpty:
	return -408;
Error_PasswordEmpty:
	return -409;
Error_WorkbenchDoesNotExist:
	return -516;
Error_UserLoggedInOnOtherWorkbench:
	return -551;