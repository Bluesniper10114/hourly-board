/*
 *  Checks if an operator with the given credentials exist (barcode = username, password = password)
 *  Logs in the operator on the given workbench
 *		NOTE: The difference from LoginWithOperatorBarcode is that the operator is not uniquely associated to a workbench
 *	Creates an account token for the profile
 *	Returns the account token and the profileId if successful
 *	It tries repeatedly to generate a unique token. If it fails, it returns an error.
 *
 * @param: @barcode	- operator barcode as displayed on the badge
 * @param: @password - md5 encrypted password
 * @param: @workbenchId - target workbench where to login
 */
CREATE PROCEDURE [users].[Login]
	@barcode nvarchar(50),
	@password nvarchar(50),
	@workbenchId int = null,
	@token nvarchar(MAX) OUTPUT,
	@profileId int OUTPUT
AS
	set nocount on;
	declare @errorNumber int = 0;

	set @token = null;
	set @profileId = 0;
	-- basic checks for username and password
	if (ltrim(rtrim(@barcode)) = N'') goto Error_UsernameEmpty;
	if (ltrim(rtrim(@password)) = N'') goto Error_PasswordEmpty;
	
	declare @workbenchExists bit = 0;

	if (@workbenchId is not null)
	begin
		select @workbenchExists = 1 from [layout].Workbench where ID = @workbenchId;
		if (@workbenchExists = 0) goto Error_WorkbenchDoesNotExist;
	end

	begin try
		-- find the account
		declare @userAccountId int;

		select		@userAccountId = a.ID, @profileId = a.ProfileID 
		from		[users].Account a
		inner join	[users].Profile p on a.ProfileID = p.ID
		where		ISNULL(a.[Username], '') = @barcode 
					and ISNULL(a.[Password], '') = @password 
					and a.[AccountProviderID] = 1 -- TrueHR
					and ISNULL(p.IsActive, 0) = 1

		if ((@userAccountId is null) or (@profileId is null)) 
		begin 
			set @errorNumber = -441; 
			goto Error_Other; 
		end -- throw 50441, N'Error_WrongCredentials', 1;	
	
		-- create a new account token for the user
		exec [users].[ForceLogoutIfTokensHaveExpired];

		begin tran
			declare @targetWorkbenchId int = ISNULL(@workbenchId, 0);

			update wbs
			set LoggedInProfileID = @profileId
			from [layout].WorkbenchStatus wbs  
			where wbs.WorkbenchID = @targetWorkbenchId;


			SET @token = convert(nvarchar(MAX), newid())
			exec [users].InsertOrUpdateToken @token = @token, @userAccountId = @userAccountId, @workbenchId = @targetWorkbenchId;
		commit tran
	end try
	begin catch
		set @errorNumber = ERROR_NUMBER();
		declare @error nvarchar(4000);
		set @error = N'[' + ERROR_PROCEDURE() + N']' + ERROR_MESSAGE()

		-- declare @procedureId tinyint = [log].[GetProcedureID]('[Users].[LoginWithOperatorBarcode]');
		-- exec [log].LogError @procedureId = @procedureId, @errorId = 1, @message = @error, @devError = 'internal login error';
		goto Error;
	end catch
		
RETURN 0;
Error:
	IF (@@TRANCOUNT > 0) rollback tran;
Error_Other:
	return @errorNumber;
Error_UsernameEmpty:
	return -408;
Error_PasswordEmpty:
	return -409;
Error_WorkbenchDoesNotExist:
	return -516;