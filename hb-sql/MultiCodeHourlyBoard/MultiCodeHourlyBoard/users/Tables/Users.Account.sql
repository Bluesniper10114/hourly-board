CREATE TABLE [Users].[Account] (
    [id]                             BIGINT             IDENTITY (1, 1) NOT NULL,
	[createdAt]                  DATETIMEOFFSET (3) CONSTRAINT [DF_Account_createdAt] DEFAULT ([dbo].[GetUTCDate]()) NOT NULL,
    [updatedAt]                      DATETIMEOFFSET (3) NULL,
    [deleted]                        BIT                DEFAULT ((0)) NULL,
    [Username]                       NVARCHAR (50)      NOT NULL,
    [Password]                       NVARCHAR (50)      NOT NULL,
    [AccountProviderUniqueAppId] NVARCHAR (100)     NOT NULL,
    [AccountProviderId]          SMALLINT           NOT NULL,
    [ProfileId]                  BIGINT             NOT NULL,
    CONSTRAINT [PK_Account_id] PRIMARY KEY CLUSTERED ([id] ASC),
    CONSTRAINT [FK_Account_AccountProvider] FOREIGN KEY ([AccountProviderId]) REFERENCES [Users].[AccountProvider] ([id]),
    CONSTRAINT [FK_Account_Profile] FOREIGN KEY ([ProfileId]) REFERENCES [Users].[Profile] ([id]),
);
GO

CREATE TRIGGER [Users].[Trigger_Account_UpdatedAt]
    ON [Users].[Account]
    AFTER UPDATE
    AS
    BEGIN
        SET NoCount ON

		UPDATE a
        SET a.[updatedAt] = dbo.GetUTCDate()
		from [Users].[Account] a
			inner join inserted i on a.id = i.id

    END
GO

