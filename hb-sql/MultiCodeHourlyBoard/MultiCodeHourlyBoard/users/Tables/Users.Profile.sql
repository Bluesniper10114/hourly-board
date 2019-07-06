CREATE TABLE [Users].[Profile] (
    [id]          BIGINT             IDENTITY (1, 1) NOT NULL,
    [deleted]     BIT                CONSTRAINT [DF_UserProfile_deleted] DEFAULT ((0)) NULL,
    [FirstName]   NVARCHAR (255)     NOT NULL,
    [LastName]    NVARCHAR (255)     NOT NULL,
    [LevelId]	  SMALLINT           CONSTRAINT [DF_UserProfile_Level] DEFAULT ((0)) NOT NULL,
    [Barcode]     NVARCHAR (50)      NULL,
    [isActive]      BIT                CONSTRAINT [DF_UserProfile_Active] DEFAULT ((1)) NULL,
    [OperatorId]  BIGINT             NULL,
    [createdAt]   DATETIMEOFFSET (3) CONSTRAINT [DF_UserProfile_createdAt] DEFAULT (dbo.GetUTCDate()) NOT NULL,
    [updatedAt]   DATETIMEOFFSET (3) NULL,
	[ColorId]		smallint		NOT NULL DEFAULT (1),
    CONSTRAINT [PK_Profile_id] PRIMARY KEY CLUSTERED ([id] ASC),
    CONSTRAINT [CK_Profile_Barcode] UNIQUE NONCLUSTERED ([Barcode] ASC), 
    CONSTRAINT [FK_ColorId_Color] FOREIGN KEY ([ColorId]) REFERENCES [Users].[Color]([id])
);

