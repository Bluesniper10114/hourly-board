CREATE TABLE [dbo].[ShiftLogSignOff] (
    [ID]					INT  IDENTITY(1,1) NOT NULL,
    [ShiftLogID]			INT      NOT NULL,
    [LineID]				SMALLINT NOT NULL,
    [SignedOffOperatorID]   INT      NULL,
    [Automatic]				BIT      CONSTRAINT [DF_ShiftLogSignOff_Automatic] DEFAULT ((0)) NOT NULL,
    [UpdateDate]			DATETIME NULL,
    CONSTRAINT [PK_ShiftLogSignOff] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [FK_ShiftLogSignOff_ShiftLog] FOREIGN KEY ([ShiftLogID]) REFERENCES [dbo].[ShiftLog] ([ID]),
    CONSTRAINT [FK_ShiftLogSignOff_Line] FOREIGN KEY ([LineID]) REFERENCES [layout].[Line] ([ID]),
    CONSTRAINT [FK_ShiftLogSignOff_SignedOffOperator] FOREIGN KEY ([SignedOffOperatorID]) REFERENCES [users].[Operator] ([ID]),
    CONSTRAINT [IX_ShiftLogSignOff] UNIQUE NONCLUSTERED ([LineID] ASC, [ShiftLogID] ASC) WITH (FILLFACTOR = 90)
);

