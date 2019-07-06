CREATE PROCEDURE [Users].[RegisterUserFromOperator]
	@operatorId bigint,
	@password nvarchar(50)
AS
	set nocount on;
	
	if (not exists ( select 1 from Users.[Operator] op where op.id = @operatorId)) goto Error_OperatorNotFound;

	declare @existingOperators int;
	select @existingOperators = count(1) from Profile where ISNULL(OperatorId, 0) = @operatorId;

	if (@existingOperators <> 0) goto Error_ProfileExists;
	begin try
		begin tran
		insert into Users.[Profile] ( deleted, Barcode, FirstName, LastName, LevelId, isActive, OperatorId)
		select 0, op.Barcode, op.FirstName, op.LastName, 1 /* lowest security level*/, 1, op.id
		from Users.[Operator] op
		where op.id = @operatorId

		declare @profileId bigint;
		select @profileId = SCOPE_IDENTITY();

		insert into Users.[Account] (deleted, Username, [Password], AccountProviderUniqueAppId, AccountProviderId, ProfileId)
		select 0, op.Barcode, @password, N'', 1, @profileId
		from Users.[Operator] op
		where op.id = @operatorId

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
