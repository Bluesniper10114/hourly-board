CREATE TABLE [layout].[Location] (
    [ID]         CHAR(2)           NOT NULL,
    [Name]       NVARCHAR (50) NOT NULL,
    [Deleted] BIT CONSTRAINT [DF_Location_Deleted] DEFAULT ((0)) NOT NULL,
    CONSTRAINT [PK_Location] PRIMARY KEY CLUSTERED ([ID] ASC),
	CONSTRAINT [IX_Location_Name] UNIQUE NONCLUSTERED ([Name] ASC), 
);