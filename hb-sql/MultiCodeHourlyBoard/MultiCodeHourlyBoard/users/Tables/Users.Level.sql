CREATE TABLE [Users].[Level] (
    [id]    SMALLINT       IDENTITY (1, 1) NOT NULL,
    [Name] NVARCHAR (50)  NOT NULL,
    [Help]  NVARCHAR (MAX) NULL,
    CONSTRAINT [PK_UserLevel_id] PRIMARY KEY CLUSTERED ([id] ASC)
);

