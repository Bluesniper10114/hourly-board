CREATE PROCEDURE [dbo].[BillboardUpdateActuals]
	@ShiftLogID int
as
	set nocount on

	declare @lineID			int,
			@dailyID		int,
			@errorNumber	int = 16,
			@errorMessage	nvarchar(max),
			@procedureLogID	bigint

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(24,	N'@ShiftLogID=' + ISNULL(CONVERT(nvarchar(10), @ShiftLogID), N'NULL'))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		begin tran

			-- check if target exists
			while 1=1
			begin
				select top 1 @lineID = LineID
				from dbo.vActualsLog
				where ShiftLogID = @ShiftLogID
					and DailyID is NULL
				if @@ROWCOUNT = 0 break

				exec [target].[AddTargetAutomatic] @LineID = @lineID, @ShiftLogID = @shiftLogID, @DailyID = @dailyID OUTPUT
			end

			-- add achieved info
			update bl
			set ActualAchieved = bla.Achieved, CumulativeAchieved = bla.Achieved, Defects = bla.Defects, Downtime = 0
			from dbo.BillboardLog bl
				inner join (
					select bl.TargetHourlyID, COUNT(al.ID) Achieved, COUNT(al.ID) - SUM(CONVERT(smallint, al.IsOK)) Defects 
					from dbo.vActualsLog al
						inner join dbo.vBillboardLog bl on al.LineID = bl.LineID and al.[Date] >= bl.HourStart and al.[Date] < bl.HourEnd
					where al.ShiftLogID = @ShiftLogID
						and al.EOL = 1
					group by bl.TargetHourlyID
				) bla on bl.TargetHourlyID = bla.TargetHourlyID

			declare @iHour tinyint = 1
			while @iHour <= 8
			begin
				update bl
				set CumulativeAchieved = bla.CumulativeAchieved
				from dbo.BillboardLog bl
					inner join (
						select LineID, MAX(TargetHourlyID) TargetHourlyID, SUM(ActualAchieved) CumulativeAchieved
						from dbo.vBillboardLog
						where ShiftLogID = @ShiftLogID
							and [Hour] <= @iHour
						group by LineID
					) bla on bl.TargetHourlyID = bla.TargetHourlyID

				set @iHour += 1
			end	

			-- add downtime info
			update bl
			set Downtime = d.Downtime
			from dbo.vBillboardLog bl
				inner join (
					select LineID, [Hour], SUM(Downtime) Downtime
					from [dbo].[ftBillboardDowntime] (@ShiftLogID)
					group by LineID, [Hour]
				) d on bl.LineID = d.LineID and bl.[Hour] = d.[Hour]

		if @@TRANCOUNT > 0 commit tran
	
	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @errorMessage =  ERROR_MESSAGE()
		if @@TRANCOUNT > 0 rollback tran
		goto ErrorExit
	end catch

return(0)

ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @errorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
