-- TODO: create trigger instead of procedure
CREATE PROCEDURE [users].[AddUser]
	@firstname NVARCHAR(255),
	@lastname NVARCHAR(255),
	@levelId SMALLINT,
	@username NVARCHAR(50),
	@password NVARCHAR(50)
AS
	
    SET NOCOUNT ON
	DECLARE @result BIT = 0;
    BEGIN TRY
        BEGIN TRAN
        DECLARE @profileId INT

        INSERT INTO [users].[Profile](FirstName, LastName, LevelID, CreatedAt, IsActive, Barcode)
        VALUES (@firstname, @lastname, @levelId, [global].[GetDate](), 1, @username)

        SELECT @profileId = SCOPE_IDENTITY()

        INSERT INTO [users].[Account](ProfileID, [Username], [Password], AccountProviderID, AccountProviderUniqueAppID, CreatedAt)
        VALUES (@profileId, @username, @password, 1, 0, [global].[GetDate]())

        SET @result = 1;
        COMMIT TRAN
    END TRY
    BEGIN CATCH
		IF (@@TRANCOUNT > 0) ROLLBACK TRAN;
        SET @result = 0;
    END CATCH

RETURN @result