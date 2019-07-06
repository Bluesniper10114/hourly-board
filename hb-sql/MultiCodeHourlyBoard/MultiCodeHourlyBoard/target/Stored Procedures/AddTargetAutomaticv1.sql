/*
	Author/Date	:	Cristian Dinu, 08.02.2019
	Description	:	add target for specific date all lines, all shifts, only for 
	LastChange	:
*/


CREATE PROCEDURE [target].[AddTargetAutomaticv1]
	@Data	datetime
as
	set nocount on

	declare @autoUserID			int = users.AutomaticOperator(),
			@errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint
	declare @daily table(LineID int, TypeID char(2), ShiftLogID int, [Value] smallint, PreviousDailyID int)
	declare @hours table([Hour] tinyint)
	declare @dailyIDs idTable


	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(25,
		N'@Data=' + ISNULL(CONVERT(nvarchar(10), @Data, 105), N'NULL'))
	select @procedureLogID = SCOPE_IDENTITY()

	insert into @daily(LineID, TypeID, ShiftLogID, [Value], PreviousDailyID)
	select l.ID, pd.TypeID, sl.ID, pd.[Value], ld.DailyID
	from layout.Line l
		cross join dbo.ShiftLog sl
		left join [target].vDaily vd on l.ID = vd.LineID and sl.ID = vd.ShiftLogID and vd.Billboard = 1
		-- last existing target on previous week day of @data
		left join (
			select LineID, ShiftType, MAX(ID) DailyID
			from [target].vDaily
			where [Data] < @Data
				and Billboard = 1
				and DATEPART(WEEKDAY, [Data]) = DATEPART(WEEKDAY, @Data)
			group by LineID, ShiftType
		) ld on l.ID = ld.LineID and sl.ShiftType = ld.ShiftType
		left join [target].vDaily pd on ld.DailyID = pd.ID
	where l.Deleted = 0
		and sl.Data = @Data
		and vd.ID is NULL

	begin try
		begin tran
			-- insert target from previous day-week, if any
			insert into [target].Daily(TypeID, LineID, ShiftLogID, [Value], UpdateUserID, UpdateDate)
			select TypeID, LineID, ShiftLogID, [Value], @autoUserID, [global].[GetDate]()
			from @daily
			where PreviousDailyID is not NULL

			if @@ROWCOUNT <> 0
			begin
				insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
				select vd.ID, vh.[Hour], vh.HourlyTarget, vh.HourlyCumulativeTarget, vd.UpdateUserID, vd.UpdateDate
				from [target].vHourly vh
					inner join [target].vDaily vd on vh.LineID = vd.LineID and vh.TypeID = vd.TypeID and vh.ShiftType = vd.ShiftType
				where vh.DailyID in (select PreviousDailyID from @daily)
					and vd.[Data] = @Data

				insert into [target].[PartNumber](DailyID, PartNumberID, [Priority], InitialQty, [Value], UpdateUserID, UpdateDate)
				select vd.ID, pn.PartNumberID, pn.[Priority], pn.InitialQty, pn.[Value], vd.UpdateUserID, vd.UpdateDate
				from [target].[PartNumber] pn
					inner join [target].vDaily vpd on pn.DailyID = vpd.ID
					inner join [target].vDaily vd on vpd.LineID = vd.LineID and vpd.TypeID = vd.TypeID and vpd.ShiftType = vd.ShiftType
				where pn.DailyID in (select PreviousDailyID from @daily)
					and vd.[Data] = @Data
			end

			-- insert new target with 0 value
			insert into [target].Daily(TypeID, LineID, ShiftLogID, [Value], UpdateUserID, UpdateDate)
			select 'DY', LineID, ShiftLogID, 0, @autoUserID, [global].[GetDate]()
			from @daily
			where PreviousDailyID is NULL

			-- add hourly 0 values
			if @@ROWCOUNT <> 0
			begin
				insert into @hours([Hour]) values (1), (2), (3), (4), (5), (6), (7), (8)

				insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
				select d.ID, _h.[Hour], 0, 0, @autoUserID, [global].[GetDate]()
				from @hours _h
					cross join @daily _d
					inner join [target].Daily d on _d.LineID = d.LineID and _d.TypeID = d.TypeID
				where _d.PreviousDailyID is NULL
			end

			-- set new target in billboard
			insert into @dailyIDs
			select ID
			from [target].vDaily
			where [Data] = @Data
				and UpdateUserID = @autoUserID

			exec [target].[SetBillboardOnByIDList] @UserID = @autoUserID, @DailyIDs = @dailyIDs

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