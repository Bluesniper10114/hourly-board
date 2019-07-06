CREATE TABLE [import].[ActualsLogDeletedErrors] (
    [ActualsLogID]     BIGINT         NOT NULL,
    [ErrorType]        TINYINT        NOT NULL,
    [ErrorDescription] NVARCHAR (MAX) NOT NULL,
    [TimeStamp]        DATETIME       NOT NULL,
    [DeleteDate]       DATETIME       DEFAULT ([global].[GetDate]()) NOT NULL
);

