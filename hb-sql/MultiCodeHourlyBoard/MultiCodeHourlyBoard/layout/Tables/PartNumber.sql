CREATE TABLE [layout].[PartNumber] (
    [ID]                INT           NOT NULL,
    [PartNumber]        NVARCHAR(50)  NOT NULL,
    [Description]       NVARCHAR(MAX) NULL,
    [Routing]           SMALLINT      NOT NULL,
    [Deleted]			BIT NOT NULL CONSTRAINT [DF_PartNumber_Deleted] DEFAULT 0, 
    CONSTRAINT [PK_LayoutPartNumber] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [IX_LayoutPartNumber_PartNumber] UNIQUE NONCLUSTERED ([PartNumber] ASC)
);


GO

CREATE INDEX [IX_PartNumber_PartNumber] ON [layout].[PartNumber] ([PartNumber])
