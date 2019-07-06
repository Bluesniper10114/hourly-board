CREATE TABLE [users].[AccountLoginHistory] (
    [ID]            INT      NOT NULL,
    [AccountID]		INT      NOT NULL,
    [LoginTime]     DATETIME NOT NULL,
    [LogoutTime]    DATETIME NULL,
    [IsActive]      BIT	CONSTRAINT [DF_UserAccountLoginHistory_IsActive] DEFAULT ((1)) NOT NULL, 
    CONSTRAINT [FK_AccountLoginHistory_Account] FOREIGN KEY ([AccountID]) REFERENCES [users].[Account](ID),
	CONSTRAINT [CK_AccountLoginHistory_LogouTime] CHECK ([LogoutTime] > [LoginTime]) 
);