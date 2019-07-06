CREATE TABLE [target].[Hourly] (
    [ID]				INT      IDENTITY (1, 1) NOT NULL,
    [DailyID]			INT      NOT NULL,
    [Hour]				TINYINT  NOT NULL,
    [Value]				SMALLINT NOT NULL,
    [CumulativeValue]  SMALLINT NOT NULL,
    [UpdateUserID] INT      NOT NULL,
    [UpdateDate]   DATETIME NOT NULL,
    CONSTRAINT [PK_TargetHourly] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [CK_TargeHourly_Value] CHECK ([Value]>=(0)),
    CONSTRAINT [CK_TargetHourly_CumulativeValue] CHECK ([CumulativeValue] >= [Value]),
    CONSTRAINT [CK_TargetHourly_Hour] CHECK ([Hour]>=(1) AND [Hour]<=(8)),
    CONSTRAINT [FK_TargetHourly_TargetDaily] FOREIGN KEY ([DailyID]) REFERENCES [target].[Daily] ([ID]),
    CONSTRAINT [FK_TargetHourly_UpdateUser] FOREIGN KEY ([UpdateUserID]) REFERENCES [users].[Profile] ([ID]),
    CONSTRAINT [IX_Hourly] UNIQUE NONCLUSTERED ([DailyID] ASC, [Hour] ASC) WITH (FILLFACTOR = 90)
);

