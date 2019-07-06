/*
	Author/Date	:	Cristian Dinu, 03.08.2018
	Description	:	process edited feature rights
	LastChange	:	
*/

CREATE PROCEDURE [users].[SaveRights]
	@UserID	int,
	@XML	XML
AS
	set nocount on

	declare @errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint,
			@requestorLevelID	smallint,
			@timeStamp			datetime
	declare @txml table(
					ID					varchar(250) NOT NULL,
					TargetLevelID		smallint NOT NULL,
					NewValue			bit NOT NULL,
					OldValue			bit NOT NULL,
					RequestorLevelID	smallint,
					UpdateUserID		int,
					UpdateDate			datetime)

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID, XMLParam)
	values(2, @UserID, @XML)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		-- get user security level
		select @requestorLevelID = LevelID
		from users.[Profile]
		where ID = @UserID

		-- get info from XML
		insert into @txml(ID, TargetLevelID, NewValue, OldValue, RequestorLevelID, UpdateUserID, UpdateDate)
		select txml.ID, txml.LevelID, txml.NewValue,
			case when f.Operation is NULL then 0 else 1 end New_Value, f.RequestorLevelID, f.UpdateUserID, f.UpdateDate
		from (
			select
				'hourly-sign-off' as ID,
				T.[Right].value('@levelID', 'smallint') as LevelID,
				T.[Right].value('hourly-sign-off[1]', 'bit') as NewValue
			from @xml.nodes('/root/rights/right') as T([Right])

			union all

			select
				'shift-sign-off' as ID,
				T.[Right].value('@levelID', 'smallint') as LevelID,
				T.[Right].value('shift-sign-off[1]', 'bit')	as NewValue
			from @xml.nodes('/root/rights/right') as T([Right])
		) txml
			left join users.Feature f on txml.ID = f.ID and txml.LevelID = f.TargetLevelID
		if @@ROWCOUNT = 0 goto EmptyXML

		select @timeStamp = CONVERT(datetime, T.[Right].value('.', 'char(23)'), 121)
		from @xml.nodes('/root/timeStamp') as T([Right])
		if @@ROWCOUNT = 0 goto WrongXML

		-- checking zone
		-- if exist recent feature updates, after XML timestamp
		select @errorMessage = N'During current edit session another user (' + p.FirstName + ' ' + p.LastName + ') started user rights changes'
		from @txml t
			inner join users.[Profile] p on t.UpdateUserID = p.ID
		where t.UpdateDate is not NULL 
			and t.UpdateDate > @timeStamp
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- if requestor security level is lower than target level, except admin
		if @requestorLevelID <> 1
		begin
			select @errorMessage = N'Current rights can be changed only for lower security level than requestor'
			from @txml
			where NewValue <> OldValue 
				and TargetLevelID < @requestorLevelID
			if @@ROWCOUNT <> 0 goto ErrorExit
		end

		-- if user has no rights to change current rights setting
		select @errorMessage = N'Security level for requestor user is lower than target security level ('
			+ p.FirstName + ' ' + p.LastName + '[' + l.[Name] + '])'
		from @txml t
			inner join users.[Profile] p on t.UpdateUserID = p.ID
			inner join users.[Level] l on p.LevelID = l.ID
		where t.NewValue <> t.OldValue 
			and t.RequestorLevelID is not NULL
			and t.RequestorLevelID < @requestorLevelID
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- data updates zone
		-- there is only insert & update table record because Operation = NULL means no right
		-- insert rights
		begin tran
			insert into users.Feature(ID, RequestorLevelID, TargetLevelID, Operation, UpdateUserID, UpdateDate)
			select t.ID, @requestorLevelID, t.TargetLevelID, case t.NewValue when 1 then 'X' end, @UserID, [global].[GetDate]()
			from @txml t
				left join users.Feature f on t.ID = f.ID and t.TargetLevelID = f.TargetLevelID
			where t.NewValue <> t.OldValue
				and f.ID is NULL

			-- update rigths
			update f
			set Operation = case t.NewValue when 1 then 'X' else NULL end,
				UpdateUserID = @UserID,
				UpdateDate = [global].[GetDate]()
			from users.Feature f
				inner join @txml t on f.ID = t.ID and f.TargetLevelID = t.TargetLevelID
			where t.NewValue <> t.OldValue
		if @@TRANCOUNT > 0 commit tran

	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @errorMessage =  ERROR_MESSAGE()
		if @@TRANCOUNT > 0 rollback tran
		goto ErrorExit
	end catch
return(0)

EmptyXML:
	set @errorMessage = N'Empty XML dataset'
	goto ErrorExit
WrongXML:
	set @errorMessage = N'Wrong XML dataset'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @errorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
