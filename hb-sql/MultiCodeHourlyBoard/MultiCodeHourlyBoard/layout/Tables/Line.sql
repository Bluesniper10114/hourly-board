CREATE TABLE [layout].[Line] (
    [ID]			SMALLINT  IDENTITY(1,1) NOT NULL,
    [Name]          NVARCHAR(50)  NOT NULL,
    [Description]   NVARCHAR(MAX) NULL,
    [Tags]			NVARCHAR(MAX) NULL,
    [LocationID]	CHAR(2) NOT NULL, 
    [TimeOut]		TINYINT NULL, 
    [Deleted]		BIT NOT NULL CONSTRAINT [DF_Line_Deleted] DEFAULT 0, 
    CONSTRAINT [PK_Line] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [IX_Line_Name] UNIQUE NONCLUSTERED ([Name] ASC),
    CONSTRAINT [FK_Line_Location] FOREIGN KEY ([LocationID]) REFERENCES [layout].[Location] ([ID])
);


GO

--CREATE FULLTEXT INDEX ON [layout].[Line] ([Tags]) KEY INDEX [PK_LayoutLine] ON [LineCatalog] WITH CHANGE_TRACKING AUTO
--GO