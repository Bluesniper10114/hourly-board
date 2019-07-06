CREATE TABLE [ver].[Version]
(
	[Id] INT NOT NULL PRIMARY KEY,
	[Version] [nvarchar](8) NULL,
	[VersionDescription] [nvarchar](max) NULL,
	[DateStarted] [datetime] NOT NULL,
	[DateEnded] [datetime] NULL,
	[CompatibleServiceVersion] [nvarchar](50) NULL
)