CREATE TABLE [Users].[AccountProvider] (
    [id]   SMALLINT      IDENTITY (1, 1) NOT NULL,
    [Name] NVARCHAR (50) NOT NULL,
    CONSTRAINT [PK_UserAccountProvider_id] PRIMARY KEY CLUSTERED ([id] ASC)
);

