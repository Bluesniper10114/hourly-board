/*
	Author/Date	:	Cristian Dinu, 09.10.2018
	Description	:	actuals vs target & notOK report
	LastChange	:
	Exec [report].[ActualsVsTargetAndNotOK]
		@LineID = 1,
		@ShiftType = 'A^B^C',
		@Month = '2018-09'
*/
CREATE PROCEDURE [report].[ActualsVsTargetAndNotOK]
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

	insert into @ShiftData(ShiftType)
	select ShiftType from report.Split(@ShiftType, '^')  

	select @DateStart = DataStart, @DateEnd = DataEnd from [report].[vMonth]
	where Month = @Month

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(25, N'@LineID=' + CONVERT(nvarchar(10), @LineID) + N','
				+ N'@ShiftType' + @ShiftType
				+ N'@DateStart=' + CONVERT(nvarchar(10), @DateStart, 121) + N','
				+ N'@DateEnd=' + CONVERT(nvarchar(10), @DateEnd, 121))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try

		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'layout', @ObjectTable = N'Line', @ObjectID = @LineID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		-- report data
		select LineID, ShiftType, ShiftData, DAY(ShiftData) [Day],
			MAX(CumulativeTarget) [Target],
			MAX(CumulativeAchieved) Achieved,
			SUM(Defects) NOK
		from dbo.vBillboardLog
		where LineID = @LineID
			and (ShiftType in (select ShiftType from @ShiftData))
			and ShiftData between @DateStart and @DateEnd
		group by LineID, ShiftType, ShiftData
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
