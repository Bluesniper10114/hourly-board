CREATE TABLE [dbo].[Downtime]
(
	[ID] INT IDENTITY(1,1) NOT NULL,
	[TargetHourlyID] INT NOT NULL,
	[WorkbenchID] int NOT NULL,
	[DataStart] DATETIME NOT NULL,
	[DataEnd] dATETIME NOT NULL
    CONSTRAINT [PK_Downtime] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [CK_Downtime_DataEnd] CHECK ([DataEnd] > [DataStart]),
    CONSTRAINT [FK_Downtime_TargetHourly] FOREIGN KEY ([TargetHourlyID]) REFERENCES [target].[Hourly] ([ID]),
    CONSTRAINT [FK_Downtime_Workbench] FOREIGN KEY ([WorkbenchID]) REFERENCES [layout].[Workbench] ([ID])
)

GO

CREATE TRIGGER [dbo].[DownTime_TotalDuration_InsertUpdateDelete]
    ON [dbo].[Downtime]
    FOR DELETE, INSERT, UPDATE
    AS
    BEGIN
        SET NoCount ON

		update bl
		set Downtime += i.Duration
		from dbo.BillboardLog bl
			inner join (
				select TargetHourlyID, SUM(DATEDIFF(MINUTE, DataStart, DataEnd)) Duration
				from Inserted
				group by TargetHourlyID
			) i on bl.TargetHourlyID = i.TargetHourlyID

		update bl
		set Downtime -= d.Duration
		from dbo.BillboardLog bl
			inner join (
				select TargetHourlyID, SUM(DATEDIFF(MINUTE, DataStart, DataEnd)) Duration
				from Deleted
				group by TargetHourlyID
			) d on bl.TargetHourlyID = d.TargetHourlyID
    END