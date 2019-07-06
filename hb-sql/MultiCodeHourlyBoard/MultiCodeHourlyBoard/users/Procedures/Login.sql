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
CREATE PROCEDURE [Users].[Login]
	@barcode nvarchar(50),
	@password nvarchar(50),
	@token nvarchar(MAX) OUTPUT,
	@profileId bigint OUTPUT
AS
	set nocount on;
	declare @errorNumber int = 0;

	set @token = null;
	set @profileId = 0;
	-- basic checks for username and password
	if (ltrim(rtrim(@barcode)) = N'') goto Error_UsernameEmpty;
	if (ltrim(rtrim(@password)) = N'') goto Error_PasswordEmpty;

	begin try
		-- find the account
		declare @userAccountId bigint;

		select		@userAccountId = a.id, @profileId = a.ProfileId 
		from		[Users].Account a
		inner join	[Users].Profile p on a.ProfileId = p.id
		where		ISNULL(a.[Username], '') = @barcode 
					and ISNULL(a.[Password], '') = @password 
					and a.[AccountProviderId] = 1 -- TrueHR
					and ISNULL(p.isActive, 0) = 1

		if ((@userAccountId is null) or (@profileId is null)) 
		begin 
			set @errorNumber = -441; 
			goto Error_Other; 
		end -- throw 50441, N'Error_WrongCredentials', 1;	
	
		-- create a new account token for the user
		exec [Users].[ForceLogoutIfTokensHaveExpired];

		SET @token = convert(nvarchar(MAX), newid())
		exec Users.InsertOrUpdateToken @token = @token, @userAccountId = @userAccountId;

	end try
	begin catch
		set @errorNumber = ERROR_NUMBER();
		declare @error nvarchar(4000);
		set @error = N'[' + ERROR_PROCEDURE() + N']' + ERROR_MESSAGE()

		declare @procedureId tinyint = [Log].[GetProcedureId]('[Users].[LoginWithOperatorBarcode]');
		exec [Log].LogError @procedureId = @procedureId, @errorId = 1, @message = @error, @devError = 'internal login error';
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





