CREATE TABLE [dbo].[DowntimeDetails]
(
	[ID] INT IDENTITY(1,1) NOT NULL,
	[DowntimeID] INT NOT NULL,
	[Comment] NVARCHAR(100) NOT NULL,
	[Duration] SMALLINT NOT NULL,
    [UpdateDate] DATETIME NOT NULL, 
    CONSTRAINT [PK_DowntimeDetails] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [CK_DowntimeDetails_Duration] CHECK ([Duration] >= (0)),
    CONSTRAINT [FK_DowntimeDetails_Downtime] FOREIGN KEY ([DowntimeID]) REFERENCES [dbo].[Downtime] ([ID])
)
