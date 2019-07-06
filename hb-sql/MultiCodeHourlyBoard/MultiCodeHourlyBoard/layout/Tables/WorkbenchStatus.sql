CREATE TABLE [layout].[WorkbenchStatus] (
    [WorkbenchID]           INT             NOT NULL,
	-- user currently logged in on the workbench
	[LoggedInProfileID]		INT			NULL,		
	-- last time an error was generated on the workbench
	[HasErrorTimeStamp]		DATETIME	NULL,
	-- the error on the workbench
	[ErrorId]				INT					NULL,
	-- last time something changed on the workbench (this is how notifications will know that there is something new here)
	[LastChanged]			DATETIME CONSTRAINT [DF_Order_DateIn] DEFAULT ([global].[GetDate]()) NOT NULL,		
	-- total orders on the workbench (info only, it is recalculated every time) 
	-- total volume units of all current orders on the workbench (info only, it is recalculated every time)
    CONSTRAINT [PK_WorkbenchStatus_WorkbenchId] PRIMARY KEY CLUSTERED (WorkbenchID ASC),
    CONSTRAINT [FK_WorkbenchStatus_Workbench] FOREIGN KEY (WorkbenchID) REFERENCES [layout].[Workbench] ([ID]),
    CONSTRAINT [FK_WorkbenchStatus_ErrorId] FOREIGN KEY ([ErrorId]) REFERENCES [dbo].[Error] ([ID])
);
GO
CREATE TRIGGER [layout].[Trigger_WorkbenchStatus_Update_ErrorId]
    ON [layout].[WorkbenchStatus]
    FOR INSERT, UPDATE
    AS
    BEGIN
        SET NoCount ON

		-- when errorId updates, we update the error timestamp. Errors will be shown only for a limited time based on this
		IF UPDATE(ErrorId)
		BEGIN
			update ws
			set HasErrorTimeStamp = [global].[GetDate](),
				LastChanged = [global].[GetDate]()
			from [layout].WorkbenchStatus ws
				inner join Error e on ws.ErrorId = e.ID
				inner join inserted on ws.WorkbenchID = inserted.WorkbenchID
		END
    END