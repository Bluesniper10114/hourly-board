CREATE TABLE [import].[ActualsLogErrors] (
    [ActualsLogID]     BIGINT         NOT NULL,
    [ErrorType]        TINYINT        NOT NULL,
    [ErrorDescription] NVARCHAR (MAX) NOT NULL,
    [Date]             DATETIME       NOT NULL,
    [TimeStamp]        DATETIME       DEFAULT ([global].[GetDate]()) NOT NULL,
    CONSTRAINT [PK_ActualsLogErrors] PRIMARY KEY CLUSTERED ([ActualsLogID] ASC, [ErrorType] ASC)
);


GO
CREATE NONCLUSTERED INDEX [IX_ActualsLogErrors_ActualLogID]
    ON [import].[ActualsLogErrors]([ActualsLogID] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_ActualsLogErrors_TimeStamp]
    ON [import].[ActualsLogErrors]([TimeStamp] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_ActualsLogErrors_Date]
    ON [import].[ActualsLogErrors]([Date] ASC);


GO

CREATE TRIGGER [import].[ActualsLogErrors_Delete]
    ON [import].[ActualsLogErrors]
    FOR DELETE
    AS
    BEGIN
        SET NoCount ON
		insert into [import].[ActualsLogDeletedErrors](ActualsLogID, ErrorType, ErrorDescription, [TimeStamp])
		select [ActualsLogID], ErrorType, ErrorDescription, [TimeStamp]
		from Deleted
		where [ActualsLogID] not in (select ID from [import].[ActualsLog])
    END