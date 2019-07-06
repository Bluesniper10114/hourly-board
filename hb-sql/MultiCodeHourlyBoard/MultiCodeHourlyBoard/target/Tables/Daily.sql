CREATE TABLE [target].[Daily] (
    [ID]           INT      IDENTITY (1, 1) NOT NULL,
	[TypeID]	   CHAR(2)  NOT NULL,
    [LineID]       SMALLINT NOT NULL,
    [ShiftLogID]   INT      NOT NULL,
    [Value]        SMALLINT NOT NULL,
	[Billboard]	   BIT		NOT NULL CONSTRAINT [DF_TargetDaily_Billboard] DEFAULT 0,
    [UpdateUserID] INT      NOT NULL,
    [UpdateDate]   DATETIME NOT NULL,
    CONSTRAINT [PK_TargetDaily] PRIMARY KEY CLUSTERED ([ID] ASC),
    CONSTRAINT [CK_TargetDaily_Value] CHECK ([Value]>=(0)),
    CONSTRAINT [FK_TargetDaily_Type] FOREIGN KEY ([TypeID]) REFERENCES [target].[Type] ([ID]),
    CONSTRAINT [FK_TargetDaily_Line] FOREIGN KEY ([LineID]) REFERENCES [layout].[Line] ([ID]),
    CONSTRAINT [FK_TargetDaily_ShiftLog] FOREIGN KEY ([ShiftLogID]) REFERENCES [dbo].[ShiftLog] ([ID]),
    CONSTRAINT [FK_TargetDaily_UpdateUser] FOREIGN KEY ([UpdateUserID]) REFERENCES [users].[Profile] ([ID]),
    CONSTRAINT [IX_Daily] UNIQUE NONCLUSTERED ([TypeID] ASC, [LineID] ASC, [ShiftLogID] ASC) WITH (FILLFACTOR = 90)
);


GO

CREATE TRIGGER [target].[targetDaily_CheckUniqueShiftOnBillboard_InsertUpdate]
    ON [target].[Daily]
    FOR DELETE, INSERT, UPDATE
AS
BEGIN
    SET NoCount ON

	declare @shift nvarchar(50)

	if UPDATE(Billboard) or UPDATE(ShiftLogID)
	begin
		select top 1 @shift = N'Line ' + l.[Name] + N' on ' + CONVERT(nvarchar(10), sl.[Data], 121) + N' shift ' + sl.ShiftType
		from [target].[Daily] d
			inner join dbo.ShiftLog sl on d.ShiftLogID = sl.ID
			inner join layout.Line l on d.LineID = l.ID
		where d.Billboard = 1
		group by sl.[Data], l.[Name], sl.ShiftType
		having COUNT(*) > 1

		if @shift is not NULL
		begin
			rollback tran
			raiserror(N'With present planning data set modifications you will have in billboard 2 or more records with the same date/shift %s>.', 16, 1, @shift)
		end
	end
END