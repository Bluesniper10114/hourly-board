/*
	Author/Date	:	Cristian Dinu, 10.09.2018
	Description	:	process targets by partnumber data set
	LastChange	:
*/

CREATE PROCEDURE [target].[SaveTargetByPartNumber]
	@UserID			int,
	@TargetsXML		XML,
	@errorMessage	nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@timeStamp			datetime,
			@procedureLogID		bigint,
			@sDate				varchar(50),
			@date				datetime,
			@lineID				int,
			@capacity			smallint,
			@eolMachines		smallint,
			@editMode			tinyint	= 0 -- 0=new, 1=edit
	declare @txml_pn table(
					[Priority]	smallint,
					PartNumber	nvarchar(50),
					InitialQty	int,
					Totals		int,
					ShiftType	char(1),
					[Value]		smallint)
	declare @txml_hour table(
					[Hour]			tinyint,
					ShiftType		char(1),
					[Value]			smallint)

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID, XMLParam)
	values(21, @UserID, @TargetsXML)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		-- get info from XML
		select @lineID = T.[targets].value('line[1]/@id', 'int'),
				@sDate = T.[targets].value('date[1]', 'char(10)'),
				@timeStamp = CONVERT(datetime, T.[targets].value('timeStamp[1]', 'char(23)'), 121)
		from @TargetsXML.nodes('//root') as T([targets])
		if @@ROWCOUNT = 0 goto WrongXML

		-- check if LineID is valid
		exec [global].CheckObjectID @ObjectSchema = N'layout', @ObjectTable = N'Line', @ObjectID = @lineID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		-- check if Data is correct
		if ISDATE(@sDate) = 0 goto IncorrectDate
		else set @date = CONVERT(datetime, @sDate, 120)

		-- get Line info
		select @capacity = capacity,
				@eolMachines = eolMachines
		from layout.vActiveLines
		where id = @lineID

		-- get rows from XML
		insert into @txml_pn([Priority], PartNumber, InitialQty, Totals, ShiftType, [Value])
		select
			T.[targets].value('../../@priority', 'smallint') as [Priority],
			T.[targets].value('../../partNumber[1]', 'nvarchar(50)') as [PartNumber],
			T.[targets].value('../../initialQuantity[1]', 'int') as [InitialQty],
			T.[targets].value('../../totals[1]', 'int') as [Totals],
			T.[targets].value('@name', 'char(1)') as [ShiftType],
			ISNULL(T.[targets].value('.', 'int'), 0) as [Value]
		from @TargetsXML.nodes('//shifts/shift') as T([targets])
		order by [Priority]
		if @@ROWCOUNT = 0 goto WrongXML

		insert into @txml_hour([Hour], ShiftType, [Value])
		select
			T.[targets].value('@interval', 'tinyint') as [Hour],
			T.[targets].value('../@name', 'char(1)') as [ShiftType],
			ISNULL(T.[targets].value('.', 'int'), 0) as [Value]
		from @TargetsXML.nodes('//dataset/shift/hour') as T([targets])
		if @@ROWCOUNT = 0 goto WrongXML

		-- add TargetDaily/HourlyID information
		-- id is not saved in XML because in web form <hour> elements can be deleted and recreated

		-- identify what is current and new planning status
		-- 0 = no current line+date combination, new datasets should be generated
		-- 1 = line+date combination already exists, existing dataset will be recreated
		select @editMode = 1
		from [target].vDaily
		where TypeID = 'PN'
			and LineID = @lineID
			and [Data] = @date

		-- checking zone
		-- if exist recent updates, after XML timestamp
		select top 1 @errorMessage = N'During current edit session another user started targets changes session'
		from [target].PartNumber pn
			inner join [target].vDaily d on pn.DailyID = d.ID
		where d.LineID = @LineID
			and d.[Data] = @Date
			and pn.UpdateDate > @timeStamp
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'During current edit session another user started targets changes session'
		from [target].vHourly
		where LineID = @LineID
			and ShiftData = @Date
			and UpdateDate > @timeStamp
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- if there are empty fields
		select top 1 @errorMessage = N'There are records with missing Priority values'
		from @txml_pn
		where [Priority] is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing PartNumber values'
		from @txml_pn
		where PartNumber is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing InitialQuantity values'
		from @txml_pn
		where InitialQty is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing Totals values'
		from @txml_pn
		where Totals is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing PartNumber.ShiftType values'
		from @txml_pn
		where ShiftType is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing PartNumber target values'
		from @txml_pn
		where [Value] is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing Hourly.Interval values'
		from @txml_hour
		where [Hour] is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing Hourly.ShiftType values'
		from @txml_hour
		where ShiftType is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing Hourly target values'
		from @txml_hour
		where [Value] is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if PartNumber.ShiftType record is correct
		select top 1 @errorMessage = N'There are edited records with incorrect PartNumber.ShiftType values (e.g. ' + ShiftType + N')'
		from @txml_pn
		where ShiftType not in ('A', 'B', 'C')
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if Hourly.Hour record is correct
		select top 1 @errorMessage = N'There are edited records with incorrect Hourly.Interval values (e.g. shift ' + ShiftType + N', interval ' + CONVERT(nvarchar(10), [Hour]) + N')'
		from @txml_hour
		where [Hour] not between 1 and 8
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if Priority record is doubled
		select top 1 @errorMessage = N'There are edited records with doubled PartNumber.Priority values (e.g. shift ' + ShiftType + N', Priority ' + CONVERT(nvarchar(10), [Priority]) + N')'
		from @txml_pn
		group by ShiftType, [Priority]
		having COUNT(*) > 1
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if Hourly.Hour record is doubled
		select top 1 @errorMessage = N'There are edited records with doubled Hourly.Interval values (e.g. shift ' + ShiftType + N', interval ' + CONVERT(nvarchar(10), [Hour]) + N')'
		from @txml_hour
		group by ShiftType, [Hour]
		having COUNT(*) > 1
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if Hourly.ShiftType record is correct
		select top 1 @errorMessage = N'There are edited records with incorrect Hour.ShiftType values (e.g. ' + ShiftType + N')'
		from @txml_hour
		where ShiftType not in ('A', 'B', 'C')
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if PartNumber record is correct
		select top 1 @errorMessage = N'There are edited records with incorrect PartNumber values (e.g. ' + PartNumber + N')'
		from @txml_pn
		where PartNumber not in (select PartNumber from layout.PartNumber)
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if quantity values are correct
		select top 1 @errorMessage = N'There are records with negative InitialQuantity values (e.g. ' + CONVERT(nvarchar(10), [Value]) + N')'
		from @txml_pn
		where InitialQty < 0
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with negative Totals values (e.g. ' + CONVERT(nvarchar(10), [Value]) + N')'
		from @txml_pn
		where Totals < 0
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with negative PartNumber/Target values (e.g. ' + CONVERT(nvarchar(10), [Value]) + N')'
		from @txml_pn
		where [Value] < 0
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with negative Hour/Target values (e.g. ' + CONVERT(nvarchar(10), [Value]) + N')'
		from @txml_hour
		where [Value] < 0
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if quantity values are too big
		select top 1 @errorMessage = N'There are records with too big InitialQuantity values (e.g. ' + CONVERT(nvarchar(10), [Value]) + N')'
		from @txml_pn
		where InitialQty > 32000
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with too big Totals values (e.g. ' + CONVERT(nvarchar(10), [Value]) + N')'
		from @txml_pn
		where Totals > 32000
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with too big PartNumber/Target values (e.g. ' + CONVERT(nvarchar(10), [Value]) + N')'
		from @txml_pn
		where [Value] > 32000
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with too big Hour/Target values (e.g. ' + CONVERT(nvarchar(10), [Value]) + N')'
		from @txml_hour
		where [Value] > 32000
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if there are missing/dubled Hourly values (should be 8 / shift)
		select top 1 @errorMessage = N'There are shifts with incorrect number of hourly target values per shift (e.g. ' + ShiftType + N' with ' + CONVERT(nvarchar(10), COUNT([Value])) +  N')'
		from @txml_hour
		group by ShiftType
		having COUNT([Value]) <> 8
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if Total is equal with InitialQuantity
		select top 1 @errorMessage = N'There are PartNumbers where Totals value is different then InitialQuantity value (e.g. ' + PartNumber + N')'
		from @txml_pn
		where Totals <> InitialQty
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if target values sum is equal with Totals for each PartNumber
		select top 1 @errorMessage = N'There are PartNumbers where Totals value is different then shift target values sum (e.g. ' + PartNumber + N')'
		from @txml_pn
		group by PartNumber, Totals
		having Totals <> SUM([Value])
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if Hourly target values sum is equal with PartNumber target values sum
		select top 1 @errorMessage = N'Total value for PartNumber split (' + CONVERT(nvarchar(10), _pn.Totals)
			+ N') is different then total values for Hourly split (' + CONVERT(nvarchar(10), _hour.Totals) + N') '
		from (
				select SUM([Value]) Totals
				from @txml_pn) _pn,
			(
				select SUM([Value]) Totals
				from @txml_hour) _hour
		where _pn.Totals <> _hour.Totals
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if PartNumber target values sum is equal with Hourly target values sum for each shift type
		select top 1 @errorMessage = N'There are shifts where PartNumber target values sum is different than Hourly target values sum (e.g. '
			+ st.ShiftType + N', Total PartNumber = ' + CONVERT(nvarchar(10), ISNULL(_pn.Totals, 0))
			+ N', Total Hourly = ' + CONVERT(nvarchar(10), ISNULL(_hour.Totals, 0)) + N')'
		from (
			select ShiftType
			from dbo.ShiftLog
			where [Data] = @Date
		) st
			left join (
				select ShiftType, SUM([Value]) Totals
				from @txml_pn
				group by ShiftType
			) _pn on st.ShiftType = _pn.ShiftType
			left join (
				select ShiftType, SUM([Value]) Totals
				from @txml_hour
				group by ShiftType
			) _hour on st.ShiftType = _hour.ShiftType
		where ISNULL(_pn.Totals, 0) <> ISNULL(_hour.Totals, 0)
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if shifts are open
		select top 1 @errorMessage = N'Target values for shift ' + _h.ShiftType + N' cannot be saved because is closed'
		from @txml_hour _h
			inner join dbo.ShiftLog sl on _h.ShiftType = sl.ShiftType
			left join dbo.ShiftLogSignOff slso on sl.ID = slso.ShiftLogID
		where sl.[Data] = @date
			and slso.LineID = @lineID
			and slso.SignedOffOperatorID is not NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if target match line capacity
		select top 1 @errorMessage = N'Total target values for shift ' + ShiftType
			+ N' (' + CONVERT(varchar(10), SUM([Value])) + N') exceed the line capacity (' + CONVERT(varchar(10), @capacity) + ')'
		from @txml_hour
		group by ShiftType
		having SUM([Value]) > @capacity

		-- check if target match PartNumber routing
		select top 1 @errorMessage = N'Total production time for shift '
			+ ShiftType + N', based on partnumber target values and routings (' + CONVERT(varchar(10), SUM(_pn.[Value] * pn.Routing)) 
			+ N' min) exceed shift time frame and line layout (440 minutes x ' + CONVERT(varchar(10), @eolMachines) + ' EOL Machines)'
		from @txml_pn _pn
			inner join layout.PartNumber pn on _pn.PartNumber = pn.PartNumber
		group by ShiftType
		having SUM(_pn.[Value] * pn.Routing) > 440 * @eolMachines 

		-- data updates zone
		begin tran
			-- new planning datasets
			if @editMode = 0
			begin
				insert into [target].Daily(TypeID, LineID, ShiftLogID, [Value], UpdateUserID, UpdateDate)
				select 'PN', @LineID, sl.ID, SUM([Value]), @UserID, [global].[GetDate]()
				from @txml_hour _h
					inner join dbo.ShiftLog sl on _h.ShiftType = sl.ShiftType and sl.[Data] = @Date
				group by sl.ID

				-- part number target values
				insert into [target].PartNumber(DailyID, PartNumberID, [Priority], InitialQty, [Value], UpdateUserID, UpdateDate)
				select d.ID, pn.ID, _pn.[Priority],
					case when _pn.ShiftType = 'A' then _pn.InitialQty else 0 end,
					_pn.[Value], @UserID, [global].[GetDate]()
				from @txml_pn _pn
					inner join [target].vDaily d on _pn.ShiftType = d.ShiftType
					inner join layout.PartNumber pn on _pn.PartNumber = pn.PartNumber
				where  d.TypeID = 'PN'
					and d.LineID = @LineID
					and d.[Data] = @date

				-- hourly target values
				insert into [target].Hourly(DailyID, [Hour], [Value], CumulativeValue, UpdateUserID, UpdateDate)
				select d.ID, [Hour], _h.[Value], _h.[Value], @UserID, [global].[GetDate]()
				from @txml_hour _h
					inner join [target].vDaily d on _h.ShiftType = d.ShiftType
				where  d.TypeID = 'PN'
					and d.LineID = @LineID
					and d.[Data] = @date

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
			end

			-- existing planning datasets
			else
			begin
				-- part numbers target values
				-- existing records
				update tpn
				set [InitialQty] = case when _pn.ShiftType = 'A' then _pn.InitialQty else 0 end,
					[PartNumberID] = lpn.ID,
					[Value] = _pn.[Value],
					UpdateUseriD = @UserID,
					UpdateDate = [global].[GetDate]()
				from @txml_pn _pn
					inner join [target].vDaily d on _pn.ShiftType = d.ShiftType
					inner join [target].PartNumber tpn on d.ID = tpn.DailyID and _pn.[Priority] = tpn.[Priority]
					inner join layout.PartNumber lpn on _pn.PartNumber = lpn.PartNumber
				where  d.TypeID = 'PN'
					and d.LineID = @LineID
					and d.[Data] = @date
					and (tpn.PartNumberID <> lpn.ID or tpn.[Value] <> _pn.[Value])

				-- new records
				insert into [target].PartNumber(DailyID, PartNumberID, [Priority], InitialQty, [Value], UpdateUserID, UpdateDate)
				select d.ID, lpn.ID, _pn.[Priority],
					case when _pn.ShiftType = 'A' then _pn.InitialQty else 0 end,
					_pn.[Value], @UserID, [global].[GetDate]()
				from @txml_pn _pn
					inner join [target].vDaily d on _pn.ShiftType = d.ShiftType
					left join [target].PartNumber tpn on d.ID = tpn.DailyID and _pn.[Priority] = tpn.[Priority]
					inner join layout.PartNumber lpn on _pn.PartNumber = lpn.PartNumber
				where  d.TypeID = 'PN'
					and d.LineID = @LineID
					and d.[Data] = @date
					and tpn.ID is NULL

				-- delete missing records
				delete tpn
				from [target].PartNumber tpn
					inner join [target].vDaily d on tpn.DailyID = d.ID
					left join @txml_pn _pn on tpn.DailyID = d.ID and tpn.[Priority] = _pn.[Priority]
				where  d.TypeID = 'PN'
					and d.LineID = @LineID
					and d.[Data] = @date
					and _pn.[Priority] is NULL

				-- hourly target values
				update h
				set [Value] = _h.[Value],
					CumulativeValue = _h.[Value],
					UpdateUseriD = @UserID,
					UpdateDate = [global].[GetDate]()
				from @txml_hour _h
					inner join [target].vDaily d on _h.ShiftType = d.ShiftType
					inner join [target].Hourly h on d.ID = h.DailyID and _h.[Hour] = h.[Hour]
				where  d.TypeID = 'PN'
					and d.LineID = @LineID
					and d.[Data] = @date
					and h.[Value] <> _h.[Value]
			end

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
	goto ErrorExit
IncorrectDate:
	set @errorMessage = N'Wrong Date value (' + @sDate + N')'
ErrorExit:
	declare @returnError int
	exec [global].TraceError @ErrorNumber = @errorNumber, @ErrorMessage = @errorMessage, @ProcedureLogID = @procedureLogID, @ReturnError = @returnError OUTPUT
	return(@returnError)
