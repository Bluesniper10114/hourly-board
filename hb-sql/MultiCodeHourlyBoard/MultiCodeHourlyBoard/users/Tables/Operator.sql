CREATE TABLE [users].[Operator] (
    [ID]					INT IDENTITY (1, 1) NOT NULL,
    [ValidEntry]			BIT            CONSTRAINT [DF_Operator_ValidEntry] DEFAULT ((1)) NOT NULL,
    [Barcode]				NVARCHAR (50)  NOT NULL,
    [FirstName]				NVARCHAR (255) NOT NULL,
    [LastName]				NVARCHAR (255) NOT NULL,
    [SecurityLevel]			INT            CONSTRAINT [DF_Operator_SecurityLevel] DEFAULT ((0)) NOT NULL,
    [Role]					NVARCHAR (50)  NULL,
    [IsActive]				BIT            CONSTRAINT [DF_Operator_IsActive] DEFAULT ((0)) NOT NULL,
    [Deleted]				BIT            CONSTRAINT [DF_Operator_Deleted] DEFAULT ((0)) NOT NULL,
    [CreateTimeStamp]		DATETIME       CONSTRAINT [DF_Operator_CreateTimeStamp] DEFAULT ([global].[GetDate]()) NOT NULL,
    [LastUpdateTimeStamp]	DATETIME       NULL,
    CONSTRAINT [PK_Operator] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [CK_Operator_IsActive] CHECK ([IsActive]=(0) OR [Deleted]=(0)),
    CONSTRAINT [IX_Operator_Barcode] UNIQUE NONCLUSTERED ([Barcode] ASC) WITH (FILLFACTOR = 90)
);