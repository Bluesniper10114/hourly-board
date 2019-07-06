CREATE TABLE [log].[Procedure] (
    [ID]            TINYINT          NOT NULL,
	[Name]          NVARCHAR(MAX)   NOT NULL,
    [Api]			BIT NULL, 
    CONSTRAINT [PK_Procedure] PRIMARY KEY CLUSTERED ([ID] ASC)
);