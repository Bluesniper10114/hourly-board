/*
	Author/Date	:	Cristian Dinu, 29.08.2018
	Description	:	process targets by day data set
	LastChange	:
*/

CREATE PROCEDURE [target].[SaveTargetByDay]
	@UserID			int,
	@TargetsXML		XML,
	@errorMessage	nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@timeStamp			datetime,
			@procedureLogID		bigint
	declare @txml table(
				DailyID		int,
				LineID		int,
				ShiftLogID	int,
				sValue		varchar(10),
				[Value]		smallint)
	declare @hourly table(
				DailyID		int,
				[Hour]		tinyint,
				[Value]		smallint,
				TimeStart	datetime,
				TimeEnd		datetime)
	declare @hours table([Hour] tinyint)

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID, XMLParam)
	values(19, @UserID, @TargetsXML)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try

		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		-- get info from XML
		insert into @txml(DailyID, LineID, ShiftLogID, sValue)
		select
			T.[targets].value('@id', 'int') as [DailyID],
			T.[targets].value('../@id', 'int') as [LineID],
			T.[targets].value('@shiftLogId', 'int') as [ShiftLogID],
			T.[targets].value('.', 'varchar(10)') as [Value]
		from @TargetsXML.nodes('//target') as T([targets])

		select @timeStamp = CONVERT(datetime, T.[Break].value('.', 'char(23)'), 121)
		from @TargetsXML.nodes('/root/targets/@timeStamp') as T([Break])
		if @@ROWCOUNT = 0 goto WrongXML

		-- checking zone
		-- if exist recent updates, after XML timestamp
		select top 1 @errorMessage = N'During current edit session another user started targets changes session'
		from @txml _t
			inner join [target].Daily d on _t.LineID = d.LineID and _t.ShiftLogID = d.ShiftLogID
		where d.TypeID = 'DY'
			and d.UpdateDate > @timeStamp
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- if there are empty fields
		select top 1 @errorMessage = N'There are records with missing LineID values'
		from @txml
		where LineID is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing ShiftLogID values'
		from @txml
		where ShiftLogID is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with non-numeric Target values'
		from @txml
		where LEN(sValue) > 0
			and ISNUMERIC(sValue) = 0

		select top 1 @errorMessage = N'There are records with too big Target values'
		from @txml
		where CONVERT(int, sValue) > 32000
		if @@ROWCOUNT <> 0 goto ErrorExit
		else
			update @txml
			set [Value] = CONVERT(smallint, sValue)
			where LEN(sValue) > 0

		-- check if DailyID is correct
		select top 1 @errorMessage = N'There are records with incorrect DailyID values (e.g. ' + CONVERT(nvarchar(10), DailyID) + N')'
		from @txml
		where DailyID is not NULL
			and DailyID not in (select ID from [target].Daily)
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if DailyID record is TypeID = 'DY'
		select top 1 @errorMessage = N'There are edited records with incorrect TargetTypeID values (e.g. ' + CONVERT(nvarchar(10), DailyID) + N')'
		from @txml
		where DailyID is not NULL
			and DailyID not in (select ID from [target].Daily where TypeID = 'DY')
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if ShiftLogID is correct
		select top 1 @errorMessage = N'There are records with incorrect ShiftLogID values (e.g. ' + CONVERT(nvarchar(10), ShiftLogID) + N')'
		from @txml
		where ShiftLogID not in (select ID from dbo.ShiftLog)
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if LineID is correct
		select top 1 @errorMessage = N'There are records with incorrect ShiftLogID values (e.g. ' + CONVERT(nvarchar(10), LineID) + N')'
		from @txml
		where LineID not in (select ID from layout.Line)
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if Value is correct
		select top 1 @errorMessage = N'There are records with negative Target values (e.g. ' + CONVERT(nvarchar(10), [Value]) + N')'
		from @txml
		where [Value] < 0
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if existing Daily records are same LineID and ShiftLogID
		select top 1 @errorMessage = N'There are edited records with LineID or ShiftLogID corrupted (e.g. ' + CONVERT(nvarchar(10), _t.DailyID) + N')'
		from @txml _t
			inner join [target].Daily d on _t.DailyID = d.ID
		where _t.LineID <> d.LineID
			or _t.ShiftLogID <> d.ShiftLogID
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- total shift target > line capacity
		select top 1 @errorMessage = N'Total target on ' + CONVERT(nchar(10), sl.[Data], 120) + N' - shift '
			+ sl.ShiftType + N' (' + FORMAT(_t.[Value], '#,##0') + N') is greater then '
			+ lc.[Name] + N' line capacity (' + FORMAT(lc.LineCapacity, '#,##0') + N')'
		from @txml _t
			inner join (
				select l.ID, l.[Name], ROUND(SUM(w.HourCapacity) * 8 * 11 / 12, 0) LineCapacity
				from layout.Line l
					inner join layout.Cell c on l.ID = c.LineID
					inner join layout.Workbench w on c.ID = w.CellID
				where w.EOL = 1
				group by l.ID, l.[Name]
			) lc on _t.LineID = lc.ID
			inner join dbo.ShiftLog sl on _t.ShiftLogID = sl.ID
		where _t.[Value] is not NULL
			and _t.[Value] > lc.LineCapacity
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- data updates zone
		begin tran
			-- insert new targets
			insert into [target].Daily(TypeID, LineID, ShiftLogID, [Value], UpdateUserID, UpdateDate)
			select 'DY', LineID, ShiftLogID, [Value], @UserID, [global].[GetDate]()
			from @txml
			where DailyID is NULL
				and [Value] is not NULL

			-- update daily targets
			update d
			set [Value] = _t.[Value],
				UpdateUserID = @UserID,
				UpdateDate = [global].[GetDate]()
			from [target].Daily d
				inner join @txml _t on d.ID = _t.DailyID
			where _t.[Value] is not NULL
				and d.[Value] <> _t.[Value]

			-- delete daily targets
			delete h
			from [target].Hourly h
				inner join @txml _t on h.DailyID = _t.DailyID
			where _t.[Value] is NULL

			delete d
			from [target].Daily d
				inner join @txml _t on d.ID = _t.DailyID
			where _t.[Value] is NULL

			-- save DailyID for new records
			update _t
			set DailyID = d.ID
			from @txml _t
				inner join [target].Daily d on _t.LineID = d.LineID and _t.ShiftLogID = d.ShiftLogID
			where d.TypeID = 'DY'
				and _t.DailyID is NULL


			-- edit hourly values
			-- create hourly values
			insert into @hours([Hour]) values (1), (2), (3), (4), (5), (6), (7), (8)

			-- set equaly value for each hour without breaks
			insert into @hourly(DailyID, [Hour], [Value], TimeStart, TimeEnd)
			select _t.DailyID, _h.[Hour],
				case _h.[Hour] % 2
					when 0 then CEILING(CONVERT(decimal(10,2), _t.[Value]) / 440 * 480 / 8)
					else FLOOR(CONVERT(decimal(10,2), _t.[Value]) / 440 * 480 / 8)
				end,
				DATEADD(HOUR, _h.[Hour] - 1, sl.DataStart), DATEADD(HOUR, _h.[Hour], sl.DataStart)
 			from @txml _t
				cross join @hours _h
				inner join dbo.ShiftLog sl on _t.ShiftLogID = sl.ID
			where _t.[Value] is not NULL

			--  adjust value for hour intervals affected by breaks
			update _h
			set [Value] = case
							when slb0.ShiftLogID is not NULL then ROUND(CONVERT(decimal(10,2), _h.[Value]) * DATEDIFF(MINUTE, _h.TimeStart, slb0.TimeStart) / 60, 0)
							else 0
						end +
						case
							when slb1.ShiftLogID is not NULL then ROUND(CONVERT(decimal(10,2), _h.[Value]) * DATEDIFF(MINUTE, slb1.TimeEnd, _h.TimeEnd) / 60, 0)
							else 0
						end
			from @hourly _h
				left join dbo.ShiftLogBreak slb0 on slb0.TimeStart between _h.TimeStart and _h.TimeEnd
				left join dbo.ShiftLogBreak slb1 on slb1.TimeEnd between _h.TimeStart and _h.TimeEnd
			where slb0.ShiftLogID is not NULL
				or slb1.ShiftLogID is not NULL

			-- adjust last hour interval with differencws from rounding
			update _h8
			set [Value] += d.[Value] - _d.[Value]
			from @hourly _h8
				inner join [target].Daily d on _h8.DailyID = d.ID
				inner join (
					select DailyID, SUM([Value]) [Value]
					from @hourly
					group by DailyID
				) _d on _h8.DailyID = _d.DailyID
			where _h8.[Hour] = 8

			-- update Hourly table
			insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
			select _h.DailyID, _h.[Hour], _h.[Value], _h.[Value], @UserID, [global].[GetDate]()
			from @hourly _h
				left join [target].Hourly h on _h.DailyID = h.DailyID and _h.[Hour] = h.[Hour]
			where h.ID is NULL

			update h
			set [Value] = _h.[Value],
				CumulativeValue = _h.[Value],
				UpdateUserID = @UserID, UpdateDate = [global].[GetDate]()
			from @hourly _h
				inner join [target].Hourly h on _h.DailyID = h.DailyID and _h.[Hour] = h.[Hour]
			where h.[Value] <> -h.[Value]

			-- generate cumulative values
			update h
			set CumulativeValue = (select SUM([Value])
				from [target].[Hourly]
				where DailyID = h.DailyID
					and [Hour] <= h.[Hour])
			from [target].[Hourly] h
			where h.DailyID in (select DailyID from @txml)

			-- set target on billboard if new records are the first on LineID + Date combination
			declare @dailyIDs idTable

			insert into @dailyIDs(ID)
			select MIN(d.ID)
			from [target].Daily d
				left join [target].Daily dd on d.LineID = dd.LineID and d.ShiftLogID = dd.ShiftLogID and d.ID <> dd.ID
			where d.Billboard = 0
				and dd.ID is NULL
			group by d.LineID, d.ShiftLogID

			if @@ROWCOUNT > 0
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

EmptyXML:
	set @errorMessage = N'Empty XML dataset'
	goto ErrorExit
WrongXML:
	set @errorMessage = N'Wrong XML dataset'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @errorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
