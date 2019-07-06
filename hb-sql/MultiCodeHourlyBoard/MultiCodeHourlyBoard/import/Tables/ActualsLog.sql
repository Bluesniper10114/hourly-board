CREATE TABLE [import].[ActualsLog] (
    [ID]                 BIGINT       IDENTITY (1, 1) NOT NULL,
    [Date]               DATETIME     NOT NULL,
    [ShiftType]          CHAR (1)     NOT NULL,
    [Machine]            VARCHAR (50) NOT NULL,
    [MachineAlternative] TINYINT      NOT NULL,
    [IsOK]               BIT          NOT NULL,
    [TimeStamp]          DATETIME     DEFAULT ([global].[GetDate]()) NOT NULL,
    CONSTRAINT [PK_ActualsLog] PRIMARY KEY CLUSTERED ([ID] ASC)
);


GO
CREATE NONCLUSTERED INDEX [IX_Import_ActualsLog_Date]
    ON [import].[ActualsLog]([Date] ASC);


GO
CREATE NONCLUSTERED INDEX [IX_Import_ActualsLog_Machine]
    ON [import].[ActualsLog]([Machine] ASC, [MachineAlternative] ASC);


GO

CREATE TRIGGER [import].[ActualsLog_InsertDelete]
    ON [import].[ActualsLog]
    FOR INSERT, DELETE
    AS
    BEGIN
        SET NoCount ON

		if EXISTS(select WorkbenchID from import.Machine where ReadyForImport = 0)
		begin
			rollback tran
			raiserror(N'System not ready to accept data import', 16, 1)
		end
		else
			update m
			set LastTimeStamp = lts.LastTimeStamp
			from import.Machine m
				inner join (
					select Machine, MachineAlternative, MAX([Date]) LastTimeStamp
					from Inserted
					group by Machine, MachineAlternative
				) lts on m.Machine = lts.Machine and m.MachineAlternative = lts.MachineAlternative
			where m.LastTimeStamp < lts.LastTimeStamp

			insert into import.ActualsLogChanges(ActualsLogID, [Date], ShiftType, Machine, MachineAlternative, IsOK, [TimeStamp])
			select ID, [Date], ShiftType, Machine, MachineAlternative, IsOK, [TimeStamp]
			from Deleted
    END
GO

CREATE TRIGGER [import].[ActualsLog_Update]
    ON [import].[ActualsLog]
    INSTEAD OF UPDATE
    AS
    BEGIN
        SET NoCount ON

		if EXISTS(select WorkbenchID from import.Machine where ReadyForImport = 0)
		begin
			rollback tran
			raiserror(N'System not ready to accept data import', 16, 1)
		end
		else
		begin
			insert into import.ActualsLogChanges(ActualsLogID, [Date], ShiftType, Machine, MachineAlternative, IsOK, [TimeStamp])
			select ID, [Date], ShiftType, Machine, MachineAlternative, IsOK, [TimeStamp]
			from Deleted

			update a
			set [Date] = i.[Date],
				ShiftType = i.ShiftType,
				Machine = i.Machine,
				MachineAlternative = i.MachineAlternative,
				IsOK = i.IsOK,
				[TimeStamp] = [global].[GetDate]()
			from [import].[ActualsLog] a
				inner join Inserted i on a.ID = i.ID

			update m
			set LastTimeStamp = lts.LastTimeStamp
			from import.Machine m
				inner join (
					select Machine, MachineAlternative, MAX([Date]) LastTimeStamp
					from Inserted
					group by Machine, MachineAlternative
				) lts on m.Machine = lts.Machine and m.MachineAlternative = lts.MachineAlternative
			where m.LastTimeStamp < lts.LastTimeStamp
		end
    END