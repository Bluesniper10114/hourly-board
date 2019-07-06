CREATE TABLE [import].[ActualsLogChanges] (
    [ActualsLogID]       BIGINT       NOT NULL,
    [Date]               DATETIME     NOT NULL,
    [ShiftType]          CHAR (1)     NOT NULL,
    [Machine]            VARCHAR (50) NOT NULL,
    [MachineAlternative] TINYINT      NOT NULL,
    [IsOK]               BIT          NOT NULL,
    [TimeStamp]          DATE         NOT NULL,
    [AddDate]            DATETIME     CONSTRAINT [DF_ActualLog_UpdateDate] DEFAULT ([global].[GetDate]()) NOT NULL
);

