CREATE TABLE [target].[PartNumber] (
    [ID]           INT      IDENTITY (1, 1) NOT NULL,
	[DailyID]	   INT		NOT NULL,
    [PartNumberID] INT      NOT NULL,
	[Priority]	   SMALLINT	NOT NULL,
	[InitialQty]   SMALLINT	NOT NULL,
    [Value]        SMALLINT NOT NULL,
    [UpdateUserID] INT      NOT NULL,
    [UpdateDate]   DATETIME NOT NULL,
    CONSTRAINT [PK_TargetPartNumber] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [CK_TargetPartNumber_Value] CHECK ([Value]>=(0)),
    CONSTRAINT [FK_TargetPartNumber_Daily] FOREIGN KEY ([DailyID]) REFERENCES [target].[Daily] ([ID]),
    CONSTRAINT [FK_TargetPartNumber_LayoutPartNumber] FOREIGN KEY ([PartNumberID]) REFERENCES [layout].[PartNumber] ([ID]),
    CONSTRAINT [FK_TargetPartNumber_UpdateUser] FOREIGN KEY ([UpdateUserID]) REFERENCES [users].[Profile] ([ID]),
    CONSTRAINT [IX_TargetPartNumber] UNIQUE NONCLUSTERED ([DailyID] ASC, [Priority] ASC) WITH (FILLFACTOR = 90)
);

