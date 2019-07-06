CREATE TABLE [dbo].[ShiftLogBreak] (
    [ShiftLogID] INT      NOT NULL,
    [TimeStart]  DATETIME NOT NULL,
    [TimeEnd]    DATETIME NOT NULL,
    [UpdateDate] DATETIME NOT NULL, 
    CONSTRAINT [PK_ShiftLogBreak] PRIMARY KEY CLUSTERED ([ShiftLogID] ASC, [TimeStart] ASC),
    CONSTRAINT [FK_ShiftLogBreak_ShiftLog] FOREIGN KEY ([ShiftLogID]) REFERENCES [dbo].[ShiftLog] ([ID]), 
    CONSTRAINT [CK_ShiftLogBreak_TimeEnd] CHECK (TimeEnd > TimeStart) 
);


GO

CREATE TRIGGER [dbo].[ShiftLogBreak_CheckTimeBetweenShiftLimits]
ON [dbo].[ShiftLogBreak]
FOR INSERT, UPDATE
    AS
    BEGIN
        SET NoCount ON

		declare @time nvarchar(20)
		select top 1 @time = CONVERT(nvarchar(20), i.TimeStart, 120)
		from inserted i
			inner join dbo.vShiftLog sl on i.ShiftLogID = sl.ID
		where i.TimeStart < sl.DataStart
			or i.TimeStart >= sl.DataEnd

		if @time is not NULL
		begin
			rollback tran
			raiserror(N'Break start time %s is not in the time limit of associated shift.', 16, 1, @time)
		end
				
		select top 1 @time = CONVERT(nvarchar(20), i.TimeStart, 120)
		from inserted i
			inner join dbo.vShiftLog sl on i.ShiftLogID = sl.ID
		where i.TimeEnd < sl.DataStart
			or i.TimeEnd >= sl.DataEnd

		if @time is not NULL
		begin
			rollback tran
			raiserror(N'Break end time %s is not in the time limit of associated shift.', 16, 1, @time)
		end
    END