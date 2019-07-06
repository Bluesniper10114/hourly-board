CREATE TABLE [Users].[AccountToken] (
    [id]            BIGINT             IDENTITY (1, 1) NOT NULL,
    [AccountId]		BIGINT             NOT NULL,
	[WorkbenchId]	int				   NULL,
    [Token]         NVARCHAR (MAX)     NOT NULL,
    [Expire]        DATETIMEOFFSET (3) CONSTRAINT [DF_AccountToken_Expire] DEFAULT (dateadd(minute,(60), dbo.GetUTCDate())) NOT NULL,
    [LoginTime]     DATETIMEOFFSET (3) CONSTRAINT [DF_AccountToken_LoginTime] DEFAULT (dbo.GetUTCDate()) NOT NULL,
    [LogoutTime]    DATETIMEOFFSET (3) NULL,
    [IsActive] BIT NOT NULL DEFAULT (1), 
	[AutomaticLogout]	BIT NULL,
    CONSTRAINT [PK_AccountToken] PRIMARY KEY CLUSTERED ([id] ASC),
    CONSTRAINT [FK_AccountToken_Account] FOREIGN KEY ([AccountId]) REFERENCES [Users].[Account] ([id])
);

