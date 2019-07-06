CREATE TABLE [layout].[WorkbenchType] (
    [ID]           varchar(10)		NOT NULL,
    [Name]         NVARCHAR (50)	NOT NULL,
    [Description]  NVARCHAR (MAX)	NULL,
    CONSTRAINT [PK_WorkbenchType] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [IX_WorkbenchType_Name] UNIQUE NONCLUSTERED ([Name] ASC)
);