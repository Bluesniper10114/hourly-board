CREATE PROCEDURE [target].[SetOnBillboard]
	@UserID			int,
	@DailyTargetID	int,
	@errorMessage	nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@procedureLogID		bigint,
			@dailyIDs			idTable

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID, CustomParams)
	values(22, @UserID,
		N'@DailyTargetID=' + ISNULL(CONVERT(nvarchar(10), @DailyTargetID), N'NULL'))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		exec [global].CheckObjectID @ObjectSchema = N'target', @ObjectTable = N'Daily', @ObjectID = @DailyTargetID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		
		-- data updates zone
		begin tran
			insert into @dailyIDs values(@DailyTargetID)
			exec [target].[SetBillboardOnByIDList] @UserID = @UserID, @DailyIDs = @dailyIDs
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
