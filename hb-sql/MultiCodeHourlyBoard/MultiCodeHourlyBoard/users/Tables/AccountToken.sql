CREATE TABLE [users].[AccountToken] (
    [ID]            INT             IDENTITY (1, 1) NOT NULL,
    [AccountID]		INT             NOT NULL,
	[WorkbenchID]	int				   NULL,
    [Token]         NVARCHAR (MAX)     NOT NULL,
    [Expire]        DATETIME CONSTRAINT [DF_AccountToken_Expire] DEFAULT (dateadd(minute,(60), [global].[GetDate]())) NOT NULL,
    [LoginTime]     DATETIME CONSTRAINT [DF_AccountToken_LoginTime] DEFAULT ([global].[GetDate]()) NOT NULL,
    [LogoutTime]    DATETIME NULL,
    [IsActive] BIT NOT NULL DEFAULT (1), 
	[AutomaticLogout]	BIT NULL,
    CONSTRAINT [PK_AccountToken] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [FK_AccountToken_Account] FOREIGN KEY (AccountID) REFERENCES [users].[Account] ([ID])
);