CREATE TABLE [dbo].[DowntimeDictionary]
(
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Text] [nvarchar](100) NOT NULL,
	[Deleted] [bit] NOT NULL CONSTRAINT [DF_DowntimeDictionary_Deleted]  DEFAULT ((0)),
 CONSTRAINT [PK_DowntimeDictionary] PRIMARY KEY CLUSTERED ([ID])
)
