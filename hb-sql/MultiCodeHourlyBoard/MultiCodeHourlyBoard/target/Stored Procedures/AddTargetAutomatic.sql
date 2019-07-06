/*
	Author/Date	:	Cristian Dinu, 29.08.2018
	Description	:	add target for specific Line + Shift
	LastChange	:
*/

CREATE PROCEDURE [target].[AddTargetAutomatic]
	@LineID			int,
	@ShiftLogID		int,
	@DailyID		int OUTPUT
as
	set nocount on

	declare @autoUserID			int = users.AutomaticOperator(),
			@previousdDailyID	int,
			@typeID				char(2),
			@errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint
	declare @hours table([Hour] tinyint)
	declare @dailyIDs idTable


	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(25,
		N'@LineID=' + ISNULL(CONVERT(nvarchar(10), @LineID), N'NULL') + N', ' +
		N'@ShiftLogID=' + ISNULL(CONVERT(nvarchar(10), @ShiftLogID), N'NULL'))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		begin tran
				
			-- insert target from previous day-week, if any
			select top 1
				@previousdDailyID = vd.ID,
				@typeID = vd.TypeID
			from [target].vDaily vd
				cross join dbo.ShiftLog sl 
			where vd.LineID = @LineID
				and vd.Billboard = 1
				and vd.[Data] < [global].GetDate()
				and sl.ID = @ShiftLogID
				and DATEPART(WEEKDAY, vd.[Data]) = DATEPART(WEEKDAY, sl.[Data])
			order by vd.[Data] desc

			if @@ROWCOUNT <> 0
			begin
				insert into [target].Daily(TypeID, LineID, ShiftLogID, [Value], UpdateUserID, UpdateDate)
				select TypeID, LineID, ShiftLogID, [Value], @autoUserID, [global].[GetDate]()
				from [target].Daily
				where ID = @previousdDailyID
				set @DailyID = SCOPE_IDENTITY()

				insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
				select @DailyID, [Hour], 0, 0, @autoUserID, [global].[GetDate]()
				from [target].Hourly
				where DailyID = @previousdDailyID

				if @typeID = 'PN'
					insert into [target].[PartNumber](DailyID, PartNumberID, [Priority], InitialQty, [Value], UpdateUserID, UpdateDate)
					select @DailyID, PartNumberID, [Priority], InitialQty, [Value], UpdateUserID, UpdateDate
					from [target].[PartNumber]
					where DailyID = @DailyID
			end
			else
			begin
				-- insert new target with 0 value
				insert into [target].Daily(TypeID, LineID, ShiftLogID, [Value], UpdateUserID, UpdateDate)
				values('DY', @LineID, @ShiftLogID, 0, @autoUserID, [global].[GetDate]())
				set @DailyID = SCOPE_IDENTITY()

				-- add hourly 0 values
				insert into @hours([Hour]) values (1), (2), (3), (4), (5), (6), (7), (8)

				insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
				select @DailyID, [Hour], 0, 0, @autoUserID, [global].[GetDate]()
				from @hours
			end

			-- set new target in billboard
			insert into @dailyIDs(ID) values(@DailyID)
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