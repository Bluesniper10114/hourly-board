CREATE TABLE [dbo].[ActualsLog] (
    [ID]          BIGINT   NOT NULL,
    [ShiftLogID]  INT      NOT NULL,
    [WorkbenchID] INT      NOT NULL,
    [IsOK]        BIT      NOT NULL,
    [Date]        DATETIME NOT NULL,
    [TimeStamp]   DATETIME NOT NULL,
    [AddDate]     DATETIME CONSTRAINT [DF_ActualLog_UpdateDate] DEFAULT ([global].[GetDate]()) NOT NULL,
    CONSTRAINT [PK_ActualsLog] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [FK_ActualsLog_ShiftLog] FOREIGN KEY ([ShiftLogID]) REFERENCES [dbo].[ShiftLog] ([ID]),
    CONSTRAINT [FK_ActualsLog_Workbench] FOREIGN KEY ([WorkbenchID]) REFERENCES [layout].[Workbench] ([ID])
);
GO

CREATE NONCLUSTERED INDEX [IX_ActualsLog_Workbench]
    ON [dbo].[ActualsLog]([WorkbenchID] ASC);
GO

CREATE UNIQUE NONCLUSTERED INDEX [IX_ActualsLog_DateWorkbench]
    ON [dbo].[ActualsLog]([Date] ASC, [WorkbenchID] ASC);