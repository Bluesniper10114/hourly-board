/*
	Author/Date	:	Cristian Dinu, 03.08.2018
	Description	:	get current feature rights for editing
	LastChange	:	
*/

CREATE PROCEDURE [users].[GetRights]
	@UserID	int,
	@XML	XML	OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint,
			@requestorLevelID	smallint,
			@timeStamp			datetime,
			@timeOut			smallint

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID)
	values(1, @UserID)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		exec [global].GetSettingKeyValue @Key = N'SESSION_EXPIRES_IN_MINUTES', @ProcedureLogID = @procedureLogID, @Value = @timeOut OUTPUT

		-- get user security level
		select @requestorLevelID = LevelID
		from users.[Profile]
		where ID = @UserID

		set @XML = (
			select CONVERT(char(23), [global].[GetDate](), 121) [timeStamp], @timeout [timeOut],
				(select l.ID '@levelID', l.[Name] [level],
				case 
					when l.ID < @requestorLevelID then 0
					when hso.RequestorLevelID is NULL then 1
					when hso.RequestorLevelID < @requestorLevelID then 0
					else 1
				end 'hourly-sign-off/@enabled',
				case when hso.Operation is NULL then 0 else 1 end [hourly-sign-off],
				case 
					when l.ID < @requestorLevelID then 0
					when sso.RequestorLevelID is NULL then 1
					when sso.RequestorLevelID < @requestorLevelID then 0
					else 1
				end 'shift-sign-off/@enabled',
				case when sso.Operation is NULL then 0 else 1 end [shift-sign-off]
			from users.[Level] l
				left join users.Feature hso on l.ID = hso.TargetLevelID and hso.ID = 'hourly-sign-off'
				left join users.Feature sso on l.ID = sso.TargetLevelID and sso.ID = 'shift-sign-off'
			for xml path('right'), root('rights'), type)
			for xml path('root'), type)
		if @@ROWCOUNT = 0 goto EmptyXML

		update [log].ProcedureLog
		set XMLParam = @xml
		where ID = @procedureLogID
	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @errorMessage =  ERROR_MESSAGE()
		goto ErrorExit
	end catch
return(0)

EmptyXML:
	set @errorMessage = N'Empty XML dataset'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @errorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
