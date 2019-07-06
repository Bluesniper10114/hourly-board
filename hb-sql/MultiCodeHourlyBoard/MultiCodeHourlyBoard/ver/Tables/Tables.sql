CREATE TABLE [ver].[Tables](
	[Schema] [varchar](250) NOT NULL,
	[Name] [varchar](250) NOT NULL,
	[Dictionary] [bit] NOT NULL DEFAULT ((1)),
	[VersionId] [int] NULL,
	CONSTRAINT [FK_Tables_Version] FOREIGN KEY([VersionId]) REFERENCES [ver].[Version] ([Id]), 
    CONSTRAINT [PK_Tables_Schema_Name] PRIMARY KEY ([Schema] ASC, [Name] ASC)
)
GO
CREATE TRIGGER [ver].[Trigger_VersionId_SetVersionId_On_Insert]
    ON [ver].[Tables]
    FOR INSERT
AS
    BEGIN
        SET NoCount ON

		DECLARE @versionId INT;
		SELECT @versionId = MAX(Id) FROM [ver].[Version] 

		UPDATE t
		SET VersionId = @versionId
		FROM [ver].[Tables] t
		INNER JOIN INSERTED i ON t.[Schema] = i.[Schema] AND t.[Name] = i.[Name]
			WHERE t.VersionId IS NULL
    END