CREATE TABLE [layout].[Workbench] (
    [ID]                  INT   IDENTITY(1,1)  NOT NULL,
    [Name]                VARCHAR (50)  NOT NULL,
    [Description]         VARCHAR (MAX) NULL,
    [ExternalReference]   NVARCHAR (50) NOT NULL,	-- station number
    [CellID]              SMALLINT      NOT NULL,
    [PreviousWorkbenchID] INT           NULL,
    [EOL]                 BIT           NOT NULL,
    [Routing]             SMALLINT      NOT NULL,
    [HourCapacity]        SMALLINT      NOT NULL CONSTRAINT [DF_Workbench_HourCapacity] DEFAULT 0,
	[TypeID]			  varchar(10)	NULL,
    [TimeOut] TINYINT NULL, 
    CONSTRAINT [PK_Workbench] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [FK_Workbench_Type] FOREIGN KEY ([TypeID]) REFERENCES [layout].[WorkbenchType] ([ID]),
    CONSTRAINT [FK_Workbench_Cell] FOREIGN KEY ([CellID]) REFERENCES [layout].[Cell] ([ID]),
    CONSTRAINT [FK_Workbench_PreviousWorkbench] FOREIGN KEY ([PreviousWorkbenchID]) REFERENCES [layout].[Workbench] ([ID]),
    CONSTRAINT [IX_Workbench_Name] UNIQUE NONCLUSTERED ([Name] ASC)
);

