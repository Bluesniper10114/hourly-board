CREATE TABLE [log].[ProcedureLog] (
    [ID]               BIGINT          IDENTITY (1, 1) NOT NULL,
	[ProcedureID]			TINYINT				NOT NULL,
	[ProfileID]				BIGINT				NULL,
	[CustomParams]			NVARCHAR(MAX)		NULL,
	[XMLParam]				XML					NULL,
	[Message]				NVARCHAR(MAX)		NULL,
    [ErrorID]				INT					NULL,
    [DboError]				INT					CONSTRAINT [DF_ProcedureLog_DboError] DEFAULT ((0)) NULL,
    [DevError]				NVARCHAR (MAX)		NULL,
    [TimeStamp]				DATETIME			CONSTRAINT [DF_ProcedureLog_TimeStamp] DEFAULT ([global].[GetDate]()) NOT NULL,
    CONSTRAINT [PK_ProcedureLog] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [FK_ProcedureLog_Procedure] FOREIGN KEY ([ProcedureID]) REFERENCES [log].[Procedure] ([ID])
);