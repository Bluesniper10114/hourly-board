CREATE TABLE [dbo].[ShiftLog] (
    [ID]                 INT      IDENTITY (1, 1) NOT NULL,
    [LocationID]         CHAR (2) NOT NULL,
    [ShiftType]          CHAR (1) NOT NULL,
    [Data]               DATETIME NOT NULL,
    [DataStart]          DATETIME NOT NULL,
    [PreviousShiftLogID] INT      NULL,
    CONSTRAINT [PK_ShiftLog] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [CK_ShiftLog_PreviousShiftLogID] CHECK ([PreviousShiftLogID] IS NULL OR [PreviousShiftLogID]<[ID]),
    CONSTRAINT [CK_ShiftLog_ShiftType] CHECK ([ShiftType]='C' OR [ShiftType]='B' OR [ShiftType]='A'),
    CONSTRAINT [FK_ShiftLog_Location] FOREIGN KEY ([LocationID]) REFERENCES [layout].[Location] ([ID]),
    CONSTRAINT [FK_ShiftLog_PreviousShiftLog] FOREIGN KEY ([PreviousShiftLogID]) REFERENCES [dbo].[ShiftLog] ([ID]),
    CONSTRAINT [IX_ShiftLog_ShiftData] UNIQUE NONCLUSTERED ([ShiftType] ASC, [Data] ASC) WITH (FILLFACTOR = 90)
);
GO

CREATE NONCLUSTERED INDEX [IX_ShiftLog_ShiftType]
    ON [dbo].[ShiftLog]([ShiftType] ASC);
GO

CREATE NONCLUSTERED INDEX [IX_ShiftLog_DataStart]
    ON [dbo].[ShiftLog]([DataStart] ASC);
GO

CREATE NONCLUSTERED INDEX [IX_ShiftLog_Data]
    ON [dbo].[ShiftLog]([Data] ASC);
GO

