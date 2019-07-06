/*
	Author/Date	:	Marian Brostean, 04.03.2017
	Description	:	Start or finish version increase
	LastChange	:	
*/
CREATE PROCEDURE [ver].[MarkVersion]
	@buildNumber INT,
	@fullVersion NVARCHAR(8) = null,
	@description NVARCHAR(MAX) = null,
	@start BIT
AS
BEGIN
	SET NOCOUNT ON;

	DECLARE @exists INT = 0;
	SELECT @exists = count(1) from [ver].[Version] where [Id] = @buildNumber

	IF (@start = 1)
	BEGIN
		IF (@exists <> 0) 
		BEGIN
			RAISERROR('Version already exists!', 16, 1)
			RETURN (-1)
		END

		-- Insert statements for procedure here
		INSERT INTO [ver].[Version]
			([Id],
			[Version],
			[VersionDescription],
			[DateStarted])
		VALUES
			(@buildNumber,
			@fullVersion,
			@description,	
			getDate());
	END
	ELSE
	BEGIN
		IF (@exists = 0) 
		BEGIN
			RAISERROR('Version does not exist!', 16, 1)
			RETURN (-1)
		END

		UPDATE [ver].[Version]
		SET DateEnded = getdate()
		WHERE [Id] = @buildNumber
	END
	RETURN (0);
END