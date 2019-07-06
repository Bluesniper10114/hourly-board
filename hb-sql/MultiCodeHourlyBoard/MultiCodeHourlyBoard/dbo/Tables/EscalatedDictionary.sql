CREATE TABLE [dbo].[EscalatedDictionary]
(
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Text] [nvarchar](50) NOT NULL,
	[Deleted] [bit] NOT NULL CONSTRAINT [DF_EscalatedDictionary_Deleted]  DEFAULT ((0)),
 CONSTRAINT [PK_EscalatedDictionary] PRIMARY KEY CLUSTERED ([ID] ASC)
)
