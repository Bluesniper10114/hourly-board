CREATE TABLE [layout].[Cell] (
    [ID]          SMALLINT  IDENTITY(1,1)  NOT NULL,
    [Name]        NVARCHAR(50)  NOT NULL,
    [Description] NVARCHAR(MAX) NULL,
    [LineID]      SMALLINT       NOT NULL,
    [TimeOut]     TINYINT       NULL,
    [Deleted]	  BIT NOT NULL CONSTRAINT [DF_Cell_Deleted] DEFAULT 0, 
    CONSTRAINT [PK_Cell] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [FK_Cell_Line] FOREIGN KEY ([LineID]) REFERENCES [layout].[Line] ([ID]),
    CONSTRAINT [IX_Cell_Name] UNIQUE NONCLUSTERED ([Name] ASC), 
);

