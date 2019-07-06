CREATE PROCEDURE [users].[DeleteUser]
	@profileId bigint
AS
	set nocount on;

	BEGIN TRY
		BEGIN TRAN
		declare @deleteProfileSucceeded bit = 1;
		declare @deleteAccountSucceeded bit = 1;
		update [users].[Profile]
		set Deleted = 1, IsActive = 0
		where ID = @profileId;
		if (@@ROWCOUNT > 0) SET @deleteProfileSucceeded = 1;

		update [users].[Account]
		set Deleted = 1
		where ProfileID = @profileId;
		if (@@ROWCOUNT > 0) SET @deleteAccountSucceeded = 1;
		if ((@deleteAccountSucceeded = 0) and (@deleteProfileSucceeded = 0))
		BEGIN
			ROLLBACK TRAN;
			RETURN -2; -- something is wrong with the profile, could not delete, or it's already deleted
		END

		COMMIT TRAN
	END TRY
	BEGIN CATCH
		IF (@@TRANCOUNT > 0) ROLLBACK TRAN;
		PRINT ERROR_MESSAGE();
		RETURN -1;
	END CATCH
return 0