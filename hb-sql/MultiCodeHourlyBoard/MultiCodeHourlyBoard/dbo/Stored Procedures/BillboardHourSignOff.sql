/*
	Author/Date	:	Cristian Dinu, 23.08.2018
	Description	:	close billboard hour by sign off
	LastChange	:	
*/

CREATE PROCEDURE [dbo].[BillboardHourSignOff]
	@TargetHourlyID		int,
	@OperatorBarcode	nvarchar(50),
	@ErrorMessage		nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@procedureLogID		bigint,
			@sTargetHourlyID	nvarchar(50),
			@operatorID			int,
			@hourInterval		nvarchar(50),
			@signedOff			bit = 0
	set @sTargetHourlyID = CONVERT(nvarchar(50), @TargetHourlyID)

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(14,
		N'@TargetHourlyID=' + ISNULL(@sTargetHourlyID, N'NULL') + N', ' +
		N'@OperatorBarcode=' + ISNULL(@OperatorBarcode, N'NULL'))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'dbo', @ObjectTable = N'BillboardLog', @ObjectColumnID = N'TargetHourlyID', @ObjectID = @sTargetHourlyID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		exec [global].CheckObjectBarcode @ObjectSchema = N'users', @ObjectTable = N'Operator', @ObjectBarcode = @OperatorBarcode, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID, @ObjectID = @operatorID OUTPUT

		-- check if sign off moment is after hour interval end
		if EXISTS(
			select TargetHourlyID
			from dbo.vBillboardLog
			where TargetHourlyID = @TargetHourlyID
				and [global].[GetDate]() between HourStart and HourEnd
		) goto HourInProgress

		-- check if there is any other previous unsigned off BillbordLog record 
		select @hourInterval = bl0.HourInterval,
				@signedOff = bl.SignedOffOperatorBarcode
		from dbo.vBillboardLog bl
			left join dbo.vBillboardLog bl0 on bl.TargetDailyID = bl0.TargetDailyID and bl.[Hour] > bl0.[Hour] and bl0.SignedOffOperator is NULL
		where bl.TargetHourlyID = @TargetHourlyID
		if @hourInterval is not NULL goto MissingSignedOffHourInterval
		if @signedOff is not NULL goto AlreadySignedOffHourInterval

		update dbo.BillboardLog
		set [SignedOffOperatorID] = @operatorID,
			[UpdateDate] = [global].[GetDate]()
		where TargetHourlyID = @TargetHourlyID

	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @ErrorMessage =  ERROR_MESSAGE()
		goto ErrorExit
	end catch
return(0)

HourInProgress:
	set @ErrorMessage =  N'You cannot sign off the current hour interval until is not finished.'
	goto ErrorExit
MissingSignedOffHourInterval:
	set @ErrorMessage =  N'There is at least one previous opened hour interval (e.g. ' + @hourInterval + N'), therefore you can not sign off current interval.'
	goto ErrorExit
AlreadySignedOffHourInterval:
	set @ErrorMessage =  N'Current hour interval is already signed off.'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @ErrorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
