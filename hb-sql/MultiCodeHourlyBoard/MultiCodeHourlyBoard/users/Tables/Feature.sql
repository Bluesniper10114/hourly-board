CREATE TABLE [users].[Feature]
(
	[ID] varchar(250) NOT NULL,
	[RequestorLevelID] SMALLINT NOT NULL, -- Level of the user requesting access
	[TargetLevelID] SMALLINT NOT NULL, -- operation performed on another role (e.g. change password for another user)
	[Operation] CHAR NULL,
    [UpdateUserID] INT NOT NULL,
    [UpdateDate]   DATETIME NOT NULL,
    -- can be one of "R" = read, "W" = write, "X" = read and write
	CONSTRAINT [PK_Feature] PRIMARY KEY CLUSTERED ([ID] ASC, [TargetLevelID] ASC),
	CONSTRAINT [FK_Feature_RequestorLevel] FOREIGN KEY ([RequestorLevelID]) REFERENCES [users].[Level] ([ID]),
	CONSTRAINT [FK_Feature_TargetLevel] FOREIGN KEY ([TargetLevelID]) REFERENCES [users].[Level] ([ID]),
    CONSTRAINT [FK_Feature_UpdateUser] FOREIGN KEY ([UpdateUserID]) REFERENCES [users].[Profile] ([ID]), 
    CONSTRAINT [CK_Feature_RequestorLevelID] CHECK (RequestorLevelID = 1 or RequestorLevelID < TargetLevelID)
)
