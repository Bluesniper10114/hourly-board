/*
	Author/Date	:	Cristian Dinu, 21.08.2018
	Description	:	save comment on billboard hourly row
	LastChange	:	
*/

CREATE PROCEDURE [dbo].[BillboardSaveComment]
	@TargetHourlyID	int,
	@Comment		nvarchar(100),
	@ErrorMessage	nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@procedureLogID		bigint,
			@signedOff			int

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, CustomParams)
	values(12,
		N'@TargetHourlyID=' + ISNULL(CONVERT(nvarchar(10), @TargetHourlyID), N'NULL') + N', ' +
		N'@Comment=' + ISNULL(@Comment, N'NULL'))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'dbo', @ObjectTable = N'BillboardLog', @ObjectColumnID = N'TargetHourlyID', @ObjectID = @TargetHourlyID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		if @Comment is NULL goto CommentNull

		-- check if BillbordLog record is already signed off
		select @signedOff = SignedOffOperatorID
		from dbo.BillboardLog
		where TargetHourlyID = @TargetHourlyID
		if @signedOff is not NULL goto AlreadySignedOffHourInterval
			
		update dbo.BillboardLog
		set [Comment] = @Comment,
			[UpdateDate] = [global].[GetDate]()
		where TargetHourlyID = @TargetHourlyID

	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @ErrorMessage =  ERROR_MESSAGE()
		goto ErrorExit
	end catch
return(0)

CommentNull:
	set @ErrorMessage = 'Comment is NULL'
	goto ErrorExit
AlreadySignedOffHourInterval:
	set @ErrorMessage =  N'Current hour interval is already signed off, therefore no change can be made.'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @ErrorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
