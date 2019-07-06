/*
	Author/Date	:	Cristian Dinu, 08.09.2018
	Description	:	get targets edited in PlannigByPartNumber methode
	LastChange	:	
*/
CREATE PROCEDURE [target].[GetTargetByPartNumber]
	@UserID			int,
	@DailyTargetID	int,			-- if is not NULL, @targetXML will return targets for corresponding line (no matter @Tags value)
	@LineID			int,			-- if is not NULL, @targetXML will return cells and associated machines
	@Date			datetime,		-- if @LineID and @Date are not NULL, @targetXML will return targets as case of @DailyTargetID not NULL
	@targetsXML		XML	OUTPUT		-- xml for line + date targets
AS
	set nocount on

	declare @errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint,
			@timeOut			smallint,
			@capacity			smallint

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID, CustomParams)
	values(20, @UserID,
		N'@DailyTargetID=' + ISNULL(CONVERT(nvarchar(10), @DailyTargetID), N'NULL') + N',' +
		N'@LineID=' + ISNULL(CONVERT(nvarchar(10), @LineID), N'NULL') + N',' +
		N'@Date=' + ISNULL(CONVERT(nvarchar(10), @Date, 120), N'NULL'))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		if @DailyTargetID is not NULL
			exec [global].CheckObjectID @ObjectSchema = N'target', @ObjectTable = N'Daily', @ObjectID = @DailyTargetID, @CheckIsNull = 0, @ProcedureLogID = @procedureLogID
		if @LineID is not NULL
			exec [global].CheckObjectID @ObjectSchema = N'layout', @ObjectTable = N'Line', @ObjectID = @LineID, @CheckIsNull = 0, @ProcedureLogID = @procedureLogID
		exec [global].GetSettingKeyValue @Key = N'SESSION_EXPIRES_IN_MINUTES', @ProcedureLogID = @procedureLogID, @Value = @timeOut OUTPUT

		declare @rows table([priority] smallint, partNumber nvarchar(50), initialQuantity int, routing smallint, totals int, partNumberID int)
		declare @targets table(DailyID int, ShiftType char(1), Closed bit)

		if @DailyTargetID is not NULL
			select @lineID = LineID, @Date = [Data]
			from [target].vDaily
			where ID = @DailyTargetID

		if @LineID is not NULL and @Date is not NULL
		begin
			insert into @targets(DailyID, ShiftType, Closed)
			select d.ID, d.ShiftType,
				case when sso.SignedOffOperatorID is not NULL then 1 else 0 end
			from [target].vDaily d
				left join dbo.ShiftLogSignOff sso on d.ShiftLogID = sso.ShiftLogID and d.LineID = sso.LineID
			where d.TypeID = 'PN'
				and d.LineID = @LineID
				and d.[Data] = @Date

			insert into @rows([priority], partNumber, initialQuantity, routing, totals, partNumberID)
			select tpn.[Priority], pn.PartNumber, SUM(tpn.InitialQty), pn.Routing, SUM(tpn.[Value]), pn.ID
			from [target].PartNumber tpn
				inner join layout.PartNumber pn on tpn.PartNumberID = pn.ID
			where tpn.DailyID in (select DailyID from @targets)
			group by tpn.[Priority], pn.PartNumber, pn.Routing, pn.ID
			order by tpn.[Priority]
		end

		-- calculate line capacity
		select @capacity = capacity
		from layout.vActiveLines
		where id = @LineiD

		-- generate XML
		set @targetsXML = (
			select CONVERT(char(23), [global].[GetDate](), 121) [timeStamp], @timeout [timeOut],
				l.ID 'line/@id', @capacity 'line/@shiftCapacity', l.[Name] 'line', CONVERT(nvarchar(10), @Date , 120) 'date',
				(select top 1 ShiftType from @targets where Closed = 1 order by ShiftType desc) 'firstClosedShift',
				(
					select c.Name 'name',
					(
						select Name 'machine/@name', Routing 'machine'
						from layout.Workbench
						where CellID = c.ID
						for xml path(''), root('machines'), type
					)
					from layout.Cell c
					where c.LineID = l.ID
					for xml path('cell'), root('cells'), type
				),
				(
					select _r.[priority] '@priority', _r.partNumber, _r.initialQuantity, _r.routing, _r.totals,
					(
						select _d.ShiftType 'shift/@name',
 							tpn.[Value] 'shift'
						from [target].PartNumber tpn
							inner join @targets _d on tpn.DailyID = _d.DailyID
						where tpn.PartNumberID = _r.partNumberID
						for xml path(''), root('shifts'), type
					)
					from @rows _r
					order by '@priority'
					for xml path('row'), root('rows'), type
				),
				(
					select _d.ShiftType '@name',
						_d.DailyID '@dailyID',
					(
						select [Hour] 'hour/@interval',
							[Value] 'hour'
						from [target].Hourly
						where DailyID = _d.DailyID
						order by 'hour/@interval'
						for xml path(''), type
					)
					from @targets _d
					order by '@name'
					for xml path('shift'), root('dataset'), type
				)
			from layout.Line l
			where ID = @LineID
			for xml path('root'), type, ELEMENTS XSINIL)
		if @@ROWCOUNT = 0 goto EmptyXML

		update [log].ProcedureLog
		set XMLParam = @targetsXML
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
