CREATE TABLE [users].[Account] (
    [ID]                         INT             IDENTITY (1, 1) NOT NULL,
	[CreatedAt]                  DATETIME CONSTRAINT [DF_Account_CreatedAt] DEFAULT ([global].[GetDate]()) NOT NULL,
    [UpdatedAt]                  DATETIME NULL,
    [Deleted]                    BIT                DEFAULT ((0)) NULL,
    [Username]                   NVARCHAR (50)      NOT NULL,
    [Password]                   NVARCHAR (50)      NOT NULL,
    [AccountProviderUniqueAppID] NVARCHAR (100)     NOT NULL,
    [AccountProviderID]          SMALLINT           NOT NULL,
    [ProfileID]                  INT             NOT NULL,
    CONSTRAINT [PK_Account_ID] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [FK_Account_AccountProvider] FOREIGN KEY ([AccountProviderID]) REFERENCES [users].[AccountProvider] ([ID]),
    CONSTRAINT [FK_Account_Profile] FOREIGN KEY ([ProfileID]) REFERENCES [users].[Profile] ([ID]),
	CONSTRAINT [IX_Account_Profile_AccountProvider] UNIQUE NONCLUSTERED ([ProfileID] ASC, [AccountProviderID] ASC),
);
GO
CREATE TRIGGER [users].[Trigger_Account_UpdatedAt]
    ON [users].[Account]
    AFTER UPDATE
    AS
    BEGIN
        SET NoCount ON

		UPDATE a
        SET a.[UpdatedAt] = [global].[GetDate]()
		from [users].[Account] a
			inner join Inserted i on a.ID = i.ID

    END
GO



