/*
	Author/Date	:	Cristian Dinu, 09.10.2018
	Description	:	downtime reasons report
	LastChange	:
	Exec [report].[DowntimeReason]
		@LineID = 1,
		@ShiftType = 'A^B',
		@Month = '2018-09'

*/
CREATE PROCEDURE [report].[DowntimeReason]
	@LineID			int,			-- mandatoru
	@ShiftType		varchar(10),	-- mandatoru
	@Month			varchar(7)		-- mandatoru
AS
	set nocount on

	declare @errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint

	declare	@DateStart		datetime,	
			@DateEnd		datetime	

	declare @ShiftData table
	(ShiftType char(1))

	insert into @ShiftData
	select ShiftType from report.Split(@ShiftType, '^')  

	select @DateStart = DataStart, @DateEnd = DataEnd from [report].[vMonth]
	where Month = @Month

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(26, N'@LineID=' + CONVERT(nvarchar(10), @LineID) + N','
				+ N'@ShiftType' + @ShiftType
				+ N'@DateStart=' + CONVERT(nvarchar(10), @DateStart, 121) + N','
				+ N'@DateEnd=' + CONVERT(nvarchar(10), @DateEnd, 121))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try

		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'layout', @ObjectTable = N'Line', @ObjectID = @LineID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		select h.LineID, h.ShiftType, h.ShiftData, DAY(h.ShiftData) [Day],
			 dd.Comment Reason, SUM(dd.Duration) Duration
		from dbo.DowntimeDetails dd
			inner join dbo.Downtime d on dd.DowntimeID = d.ID
			inner join [target].vHourly h on d.TargetHourlyID = h.ID
		where LineID = @LineID
			and (ShiftType in (select ShiftType from @ShiftData))
			and ShiftData between @DateStart and @DateEnd
		group by h.LineID, h.ShiftType, h.ShiftData, dd.Comment
	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @errorMessage =  ERROR_MESSAGE()
		goto ErrorExit
	end catch
return(0)

ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @ErrorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
;
