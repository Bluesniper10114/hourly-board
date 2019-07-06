CREATE TABLE [dbo].[BillboardLog] (
    [TargetHourlyID]      INT            NOT NULL,
    [HourInterval]        NVARCHAR (50)  NOT NULL,
    [ActualAchieved]      SMALLINT       NOT NULL CONSTRAINT [DF_BillboardLog_ActualAchieved] DEFAULT 0,
    [CumulativeAchieved] SMALLINT       NOT NULL CONSTRAINT [DF_BillboardLog_CumulativeAchieved] DEFAULT 0,
    [Defects]             SMALLINT       NOT NULL CONSTRAINT [DF_BillboardLog_Defects] DEFAULT 0,
    [Downtime]            INT       NOT NULL CONSTRAINT [DF_BillboardLog_Downtime] DEFAULT 0,
    [Comment]             NVARCHAR (100) NULL,
    [Escalated]           NVARCHAR (50)  NULL,
    [SignedOffOperatorID] INT            NULL,
    [UpdateDate]          DATETIME       CONSTRAINT [DF_BillboardLog_UpdateDate] DEFAULT ([global].[GetDate]()) NULL,
    CONSTRAINT [PK_BillboardLog] PRIMARY KEY CLUSTERED ([TargetHourlyID] ASC),
    CONSTRAINT [CK_BillboardLog_ActualAchieved] CHECK ([ActualAchieved]>=(0)),
    CONSTRAINT [CK_BillboardLog_CumulativeAchieved] CHECK ([CumulativeAchieved]>=[ActualAchieved]),
    CONSTRAINT [CK_BillboardLog_Defects] CHECK ([Defects]>=(0)),
    CONSTRAINT [CK_BillboardLog_Downtime] CHECK ([Downtime]>=(0)),
    CONSTRAINT [FK_Billboard_SignedOffOperator] FOREIGN KEY ([SignedOffOperatorID]) REFERENCES [users].[Operator] ([ID]),
    CONSTRAINT [FK_Billboard_TargetHourly] FOREIGN KEY ([TargetHourlyID]) REFERENCES [target].[Hourly] ([ID])
);



