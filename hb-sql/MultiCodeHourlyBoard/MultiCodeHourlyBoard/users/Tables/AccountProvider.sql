CREATE TABLE [users].[AccountProvider] (
    [ID]   SMALLINT NOT NULL,
    [Name] NVARCHAR (50) NOT NULL,
    CONSTRAINT [PK_UserAccountProvider_ID] PRIMARY KEY CLUSTERED ([ID] ASC)
);