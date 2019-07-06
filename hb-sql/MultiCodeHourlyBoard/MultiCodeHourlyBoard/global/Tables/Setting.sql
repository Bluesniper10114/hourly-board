CREATE TABLE [global].[Setting] (
    [Key]	VARCHAR (50)  NOT NULL,
    [Value]	NVARCHAR (MAX) NOT NULL,
    [Note]	NVARCHAR (MAX) NULL,
    CONSTRAINT [PK_Settings] PRIMARY KEY CLUSTERED ([Key] ASC)
);