/*
	Author/Date	:	Cristian Dinu, 23.08.2018
	Description	:	close billboard shift by sign off
	LastChange	:	
*/

CREATE PROCEDURE [dbo].[BillboardShiftSignOff]
	@ShiftLogSignOffID	int,
	@OperatorBarcode	nvarchar(50),
	@ErrorMessage		nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@procedureLogID		bigint,
			@operatorID			int,
			@shift				nvarchar(50),
			@signedOff			int

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(15,
		N'@ShiftLogSignOffID=' + ISNULL(CONVERT(nvarchar(10), @ShiftLogSignOffID), N'NULL') + N', ' +
		N'@OperatorBarcode=' + ISNULL(@OperatorBarcode, N'NULL'))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'dbo', @ObjectTable = N'ShiftLogSignOff', @ObjectID = @ShiftLogSignOffID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		exec [global].CheckObjectBarcode @ObjectSchema = N'users', @ObjectTable = N'Operator', @ObjectBarcode = @OperatorBarcode, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID, @ObjectID = @operatorID OUTPUT

		-- check if sign off moment is after shift end
		if EXISTS(
			select slso.ID
			from dbo.ShiftLogSignOff slso
				inner join dbo.vShiftLog sl on slso.ShiftLogID = sl.ID
			where slso.ID = @ShiftLogSignOffID
				and [global].[GetDate]() < sl.DataEnd
		) goto ShiftInProgress

		-- check if there is any other previous unsigned off BillbordLog record 
		select	@shift = slso0.ShiftName,
				@signedOff = slso.SignedOffOperatorID
		from dbo.ShiftLogSignOff slso
			inner join dbo.ShiftLog sl on slso.ShiftLogID = sl.ID
			left join (
				select sl.LocationID, slso.LineID, sl.DataStart, sl.ShiftName
				from dbo.ShiftLogSignOff slso
					inner join dbo.vShiftLog sl on slso.ShiftLogID = sl.ID
				where slso.SignedOffOperatorID is NULL
			) slso0 on sl.LocationID = slso0.locationID and slso.LineID = slso0.LineID and sl.[DataStart] > slso0.[DataStart]
		where slso.ID = @ShiftLogSignOffID
		if @shift is not NULL goto MissingSignedOffShift
		if @signedOff is not NULL goto AlreadySignedOffShift

		update dbo.ShiftLogSignOff
		set [SignedOffOperatorID] = @operatorID,
			[UpdateDate] = [global].[GetDate]()
		where ID = @ShiftLogSignOffID

	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @ErrorMessage =  ERROR_MESSAGE()
		goto ErrorExit
	end catch
return(0)

ShiftInProgress:
	set @ErrorMessage =  N'You cannot sign off the current shift until is not finished.'
	goto ErrorExit
MissingSignedOffShift:
	set @ErrorMessage =  N'There is at least one previous opened shift on same location (e.g. ' + @shift + N'), therefore current shift can not be signed off .'
	goto ErrorExit
AlreadySignedOffShift:
	set @ErrorMessage =  N'Current shift is already signed off.'
	goto ErrorExit
MissingSignOffInfo:
	set @ErrorMessage =  N'Missing ShiftLogSignOff record.'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @ErrorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
