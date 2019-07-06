CREATE PROCEDURE [users].[RegisterUserFromOperator]
	@operatorId int,
	@password nvarchar(50)
AS
	set nocount on;
	
	if (not exists ( select 1 from [users].[Operator] op where op.ID = @operatorId)) goto Error_OperatorNotFound;

	declare @existingOperators int;
	select @existingOperators = count(1) from Profile where ISNULL(OperatorID, 0) = @operatorId;

	if (@existingOperators <> 0) goto Error_ProfileExists;
	begin try
		begin tran
		insert into [users].[Profile] (Deleted, Barcode, FirstName, LastName, LevelID, IsActive, OperatorID)
		select 0, op.Barcode, op.FirstName, op.LastName, 1 /* lowest security level*/, 1, op.ID
		from [users].[Operator] op
		where op.ID = @operatorId

		declare @profileId int;
		select @profileId = SCOPE_IDENTITY();

		insert into [users].[Account] (Deleted, Username, [Password], AccountProviderUniqueAppID, AccountProviderID, ProfileID)
		select 0, op.Barcode, @password, N'', 1, @profileId
		from [users].[Operator] op
		where op.ID = @operatorId

		commit tran
	end try
	begin catch
		if (@@TRANCOUNT > 0) rollback tran;
		Return -412; -- some error occured RegisterUser_InternalError
	end catch
RETURN 0
Error_CannotCreateProfile:
	return -442;
Error_ProfileExists:
	return -443;
Error_OperatorNotFound:
	return -444;