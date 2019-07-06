CREATE TABLE [Users].[AccountLoginHistory] (
    [id]            BIGINT             NOT NULL,
    [AccountId] BIGINT             NOT NULL,
    [LoginTime]     DATETIMEOFFSET (3) NOT NULL,
    [LogoutTime]    DATETIMEOFFSET (3) NULL,
    [IsActive]      BIT                CONSTRAINT [DF_UserAccountLoginHistory_IsActive] DEFAULT ((1)) NOT NULL, 
    CONSTRAINT [FK_AccountLoginHistory_Account] FOREIGN KEY ([AccountId]) REFERENCES Users.[Account](id)
);

