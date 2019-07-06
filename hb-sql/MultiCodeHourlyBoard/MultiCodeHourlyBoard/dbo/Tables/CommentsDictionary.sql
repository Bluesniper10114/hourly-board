CREATE TABLE [dbo].[CommentsDictionary]
(
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Text] [nvarchar](100) NOT NULL,
	[Deleted] [bit] NOT NULL CONSTRAINT [DF_CommentsDictionary_Deleted]  DEFAULT ((0)),
 CONSTRAINT [PK_CommentsDictionary] PRIMARY KEY CLUSTERED ([ID])
)
