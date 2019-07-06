/*
	Author/Date	:	Cristian Dinu, 10.08.2018
	Description	:	process edited shift breaks
	LastChange	:	
*/

CREATE PROCEDURE [dbo].[SaveBreaks]
	@UserID			int,
	@XML			XML,
	@errorMessage	nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@procedureLogID		bigint,
			@timeStamp			datetime,
			@nextMonday			datetime = [global].NextMonday([global].[GetDate]())
	declare @txml table(
					[ID]				smallint NOT NULL IDENTITY(1,1),
					LocationID			char(2),
					ShiftLogID			int,
					TimeStart			datetime,
					ShiftType			char(1),
					[From]				char(5),
					[To]				char(5),
					NewTimeStart		datetime,
					NewTimeEnd			datetime)

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID, XMLParam)
	values(4, @UserID, @XML)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		-- get info from XML
		insert into @txml(LocationID, ShiftLogID, TimeStart, ShiftType, [From], [To])
		select
			T.[Break].value('../../@location', 'char(2)') as LocationID,
			T.[Break].value('../@shiftLogID', 'int') as ShiftLogID,
			CONVERT(datetime, T.[Break].value('@timeStart', 'char(16)'), 121) as TimeStart,
			T.[Break].value('../@name', 'char(1)') as ShiftType,
			T.[Break].value('from[1]', 'char(5)') as [From],
			T.[Break].value('to[1]', 'char(5)') as [To]
		from @XML.nodes('//break') as T([Break])

		select @timeStamp = CONVERT(datetime, T.[Break].value('.', 'char(23)'), 121)
		from @XML.nodes('/root/timeStamp') as T([Break])
		if @@ROWCOUNT = 0 goto WrongXML

		-- checking zone
		-- if exist recent feature updates, after XML timestamp
		select top 1 @errorMessage = N'During current edit session another user started breaks changes'
		from dbo.ShiftLogBreak
		where UpdateDate > @timeStamp
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- if there are empty fields
		select top 1 @errorMessage = N'There are records with missing LocationID values'
		from @txml
		where LocationID is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing ShiftType values'
		from @txml
		where ShiftType is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing From values'
		from @txml
		where [From] is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing To values'
		from @txml
		where [To] is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if LocationID is correct
		select top 1 @errorMessage = N'There are records with incorrect LocationID values (e.g. ' + LocationID + N')'
		from @txml
		where LocationID not in (select ID from layout.[Location])
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if ShiftType is correct
		select top 1 @errorMessage = N'There are records with incorrect ShiftType values (e.g. ' + ShiftType + N')'
		from @txml
		where ShiftType not like '[A-C]'
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if From and To time value are correct
		select top 1 @errorMessage = N'There are records with From values that are not in time format (e.g. [' + [From] + N'])'
		from @txml
		where [From] not like '[0-2][0-9]:[0-5][0-9]'
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with incorrect From values (e.g. ' + [From] + N')'
		from @txml
		where ISDATE(CONCAT(CONVERT(char(11), @nextMonday, 121), [From])) = 0
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with To values that are not in time format (e.g. [' + [From] + N'])'
		from @txml
		where [From] not like '[0-2][0-9]:[0-5][0-9]'
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with incorrect To values (e.g. ' + [From] + N')'
		from @txml
		where ISDATE(CONCAT(CONVERT(char(11), @nextMonday, 121), [To])) = 0
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if TimeStart and TimeEnd are between shift limits
		update t
		set NewTimeStart = case 
							when CONVERT(char(5), sl.DataStart, 114) < t.[From]
								then CONVERT(datetime, CONCAT(CONVERT(char(11), sl.Data, 121), t.[From]), 121)
							else CONVERT(datetime, CONCAT(CONVERT(char(11), DATEADD(day, 1, sl.Data), 121), t.[From]), 121) end,
			NewTimeEnd = case 
							when CONVERT(char(5), sl.DataStart, 114) < t.[To]
								then CONVERT(datetime, CONCAT(CONVERT(char(11), sl.Data, 121), t.[To]), 121)
							else CONVERT(datetime, CONCAT(CONVERT(char(11), DATEADD(day, 1, sl.Data), 121), t.[To]), 121) end
		from @txml t
			inner join dbo.ShiftLog sl on t.ShiftType = sl.ShiftType
		where sl.Data = @nextMonday

		select top 1 @errorMessage = N'There are records with break start time outside shift limits (e.g. for shift ' + t.ShiftType + N' break starts from ' + t.[From] + N')'
		from @txml t
			inner join dbo.ShiftLog sl on t.ShiftType = sl.ShiftType
			inner join dbo.ShiftLog slp on sl.ID = slp.PreviousShiftLogID
		where sl.Data = @nextMonday
			and (t.NewTimeStart < sl.DataStart or t.NewTimeStart >= slp.DataStart )
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with break end time outside shift limits (e.g. for shift ' + t.ShiftType + N' break ends at ' + t.[To] + N')'
		from @txml t
			inner join dbo.ShiftLog sl on t.ShiftType = sl.ShiftType
			inner join dbo.ShiftLog slp on sl.ID = slp.PreviousShiftLogID
		where sl.Data = @nextMonday
			and (t.NewTimeEnd < sl.DataStart or t.NewTimeEnd >= slp.DataStart )
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if From < To
		select top 1 @errorMessage = N'There are records with incorrect break time limits (e.g. for shift ' + ShiftType + N' break between ' + [From] + N' , ' + [To] + N')'
		from @txml
		where NewTimeStart > NewTimeEnd
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check breaks overlapping
		select top 1 @errorMessage = N'There are breaks overlapping (e.g. for shift ' + _t1.ShiftType + N' break [' + _t1.[From] + N' , ' + _t1.[To] + N'] and break [' + _t2.[From] + N' , ' + _t2.[To] + N'])'
		from @txml _t1
			inner join @txml _t2 on _t1.ShiftLogID = _t2.ShiftLogID
		where _t1.ID <> _t2.ID and
			(_t2.NewTimeStart between _t1.NewTimeStart and _t1.NewTimeEnd
			or _t2.NewTimeEnd between _t1.NewTimeStart and _t1.NewTimeEnd)
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- total break time during shifts <> 40 min
		select top 1 @errorMessage = N'There is at least one shift with total breaks time different than 40 minutes (e.g. shift ' + ShiftType + N')'
		from @txml
		group by ShiftType
		having SUM(DATEDIFF(MINUTE, NewTimeStart, NewTimeEnd)) <> 40
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- data updates zone
		begin tran
			-- deleted removed breaks
			delete slb
			from dbo.ShiftLogBreak slb
				inner join dbo.ShiftLog sl on slb.ShiftLogID = sl.ID
				inner join (
					select sl.ShiftType, DATEDIFF(MINUTE, sl.DataStart, slb.TimeStart) Diff
					from dbo.ShiftLogBreak slb
						inner join dbo.ShiftLog sl on slb.ShiftLogID = sl.ID
						left join @txml t on slb.ShiftLogID = t.ShiftLogID and slb.TimeStart = t.TimeStart
					where t.ShiftLogID is NULL
						and sl.Data = @nextMonday
				) d on sl.ShiftType = d.ShiftType and DATEDIFF(MINUTE, sl.DataStart, slb.TimeStart) = d.Diff
			where sl.Data >= @nextMonday
				
			-- insert new breaks
			insert into dbo.ShiftLogBreak(ShiftLogID, TimeStart, TimeEnd, UpdateDate)
			select sl.ID, DATEADD(MINUTE, i.DiffStart, sl.DataStart), DATEADD(MINUTE, i.DiffEnd, sl.DataStart), [global].[GetDate]()
			from dbo.ShiftLog sl
				inner join (
					select t.ShiftType, DATEDIFF(MINUTE, sl.DataStart, t.NewTimeStart) DiffStart, DATEDIFF(MINUTE, sl.DataStart, t.NewTimeEnd) DiffEnd
					from @txml t
						inner join dbo.ShiftLog sl on t.ShiftType = sl.ShiftType
					where t.TimeStart is NULL
						and sl.Data = @nextMonday
				) i on sl.ShiftType = i.ShiftType
			where sl.Data >= @nextMonday

			-- update breaks
			update slb
			set TimeStart = DATEADD(MINUTE, u.DiffStart, sl.DataStart),
				TimeEnd = DATEADD(MINUTE, u.DiffEnd, sl.DataStart),
				UpdateDate = [global].[GetDate]()
			from dbo.ShiftLogBreak slb
				inner join dbo.ShiftLog sl on slb.ShiftLogID = sl.ID
				inner join (
					select sl.ShiftType,
						DATEDIFF(MINUTE, sl.DataStart, t.TimeStart) Diff,
						DATEDIFF(MINUTE, sl.DataStart, t.NewTimeStart) DiffStart,
						DATEDIFF(MINUTE, sl.DataStart, t.NewTimeEnd) DiffEnd
					from dbo.ShiftLogBreak slb
						inner join dbo.ShiftLog sl on slb.ShiftLogID = sl.ID
						inner join @txml t on slb.ShiftLogID = t.ShiftLogID and slb.TimeStart = t.TimeStart
					where sl.Data = @nextMonday
						and (slb.TimeStart <> t.NewTimeStart or slb.TimeEnd <> t.NewTimeEnd)
				) u on sl.ShiftType = u.ShiftType and DATEDIFF(MINUTE, sl.DataStart, slb.TimeStart) = u.Diff
			where sl.Data >= @nextMonday
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
