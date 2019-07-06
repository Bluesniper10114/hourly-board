CREATE PROCEDURE [Users].[AddUser]
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
        DECLARE @profileId BIGINT

        INSERT INTO Users.Profile(FirstName, LastName, LevelId, createdAt, isActive, Barcode)
        VALUES (@firstname, @lastname, @levelId, dbo.GetUTCDate(), 1, @username)

        SELECT @profileId = SCOPE_IDENTITY()

        INSERT INTO Users.Account(ProfileId, Username, Password, AccountProviderId, AccountProviderUniqueAppId, createdAt)
        VALUES (@profileId, @username, @password, 1, 0, dbo.GetUTCDate())

        SET @result = 1;
        COMMIT TRAN
    END TRY
    BEGIN CATCH
		IF (@@TRANCOUNT > 0) ROLLBACK TRAN;
        SET @result = 0;
    END CATCH

RETURN @result