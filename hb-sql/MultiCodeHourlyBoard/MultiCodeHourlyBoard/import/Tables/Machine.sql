CREATE TABLE [import].[Machine] (
    [WorkbenchID]        INT           NOT NULL,
    [Machine]            NVARCHAR (50) NOT NULL,
    [MachineAlternative] TINYINT       DEFAULT ((0)) NOT NULL,
    [Server]             TINYINT       NULL,
    [LastTimeStamp]      DATETIME      DEFAULT ('12/01/2018') NOT NULL,
    [ReadyForImport]     BIT           DEFAULT ((1)) NOT NULL,
    PRIMARY KEY CLUSTERED ([WorkbenchID] ASC),
    CONSTRAINT [CK_Machine_Server] CHECK ([Server]=(2) OR [Server]=(1)),
    CONSTRAINT [FK_Machine_Workbench] FOREIGN KEY ([WorkbenchID]) REFERENCES [layout].[Workbench] ([ID])
);


GO
CREATE UNIQUE NONCLUSTERED INDEX [IX_Machine]
    ON [import].[Machine]([Machine] ASC, [MachineAlternative] ASC);

