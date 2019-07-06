CREATE PROCEDURE [target].[SetBillboardOnByIDList]
	@UserID		int,
	@DailyIDs	idTable READONLY
AS
	set nocount on

	declare @errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint,
			@customParams		varchar(MAX) = N'@DailyIDs=',
			@id					int = 0
	declare @daily table(ID int, TypeID char(2), LineID int, ShiftLogID int, BillboardDailyID int, ShiftIsOpen bit )

	-- log procedure exec
	while 1=1
	begin
		select top 1 
			@customParams += CONVERT(varchar(10), ID) + N';',
			@id = ID
		from @DailyIDs
		where ID > @id
		order by ID
		if @@ROWCOUNT = 0 break
	end

	insert into [log].ProcedureLog(ProcedureID, ProfileID, CustomParams)
	values(23, @UserID, @customParams)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		-- there is no need for that, sp is executed by other sp

		-- take info from plannnig dataset
		insert into @daily(ID, TypeID, LineID, ShiftLogID, BillboardDailyID, ShiftIsOpen)
		select d.ID, d.TypeID, d.LineID, d.ShiftLogID, db.ID, case when slso.SignedOffOperatorID is NULL then 1 else 0 end
		from [target].Daily d
			left join dbo.ShiftLogSignOff slso on d.LineID = slso.LineID and d.ShiftLogID = slso.ShiftLogID
			left join [target].Daily db on d.LineID = db.LineID and d.ShiftLogID = db.ShiftLogID and db.Billboard = 1
		where d.ID in (select ID from @DailyIDs)
		-- check if dataset is already BillBoardOn
		if EXISTS(select ID from @daily where ID = BillboardDailyID) goto AlreadyBillboardOn
		-- check if corresponding shifts are open
		if EXISTS(select ID from @daily where ShiftIsOpen = 0) goto ClosedShifts
		
		-- data updates zone
		-- transction is not necessary, is already started from sp that call this

		-- previous dataset with same LineID + Data combination and BillboardOn should be set off
		update d
		set Billboard = 0
		from @daily _d
			inner join [target].Daily d on _d.TypeID <> d.TypeID and _d.LineID = d.LineID and _d.ShiftLogID = d.ShiftLogID
		where d.Billboard = 1

		-- set current planning dataset with BillboardOn
		update [target].Daily
		set Billboard = 1
		where ID in (select ID from @daily)

		-- update billboard planning values
		-- if BillboardLog is already set with previous planning dataset
		update bl
		set TargetHourlyID = hb.ID, UpdateDate = [global].[GetDate]()
		from @daily _d
			inner join [target].Hourly h on _d.BillboardDailyID = h.DailyID
			inner join dbo.BillboardLog bl on h.ID = bl.TargetHourlyID
			inner join [target].Hourly hb on _d.ID = hb.DailyID and h.[Hour] = hb.[Hour]

		-- if BillboardLog is empty insert new values
		insert into dbo.BillboardLog(TargetHourlyID, HourInterval)
		select ID, HourInterval
		from [target].vHourly
		where DailyID in (select ID from @daily where BillboardDailyID is NULL)

	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @errorMessage =  ERROR_MESSAGE()
		if @@TRANCOUNT > 0 rollback tran
		goto ErrorExit
	end catch
return(0)

AlreadyBillboardOn:
	set @errorMessage = N'Planning data set is already on Billboard'
	goto ErrorExit
ClosedShifts:
	set @errorMessage = N'Planning data set cannot be set on Billboard because it has at least on shift closed'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @errorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
