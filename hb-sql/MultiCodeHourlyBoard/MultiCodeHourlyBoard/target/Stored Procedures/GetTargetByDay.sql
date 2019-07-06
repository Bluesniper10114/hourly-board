/*
	Author/Date	:	Cristian Dinu, 28.08.2018
	Description	:	get targets edited in PlannigByDate methode
	LastChange	:	
*/

CREATE PROCEDURE [target].[GetTargetByDay]
	@UserID			int,
	@DailyTargetID	int,			-- if is not NULL, @targetXML will return targets for corresponding line (no matter @Tags value)
	@Tags			nvarchar(100),	-- if is not NULL, @targetXML will return targets for all line that mach tags values
									-- if both are NULL, @targetXML return NULL
	@weeksXML		XML	OUTPUT,		-- xml data set for week/day screen aria
	@targetsXML		XML	OUTPUT		-- xml for line targets screen aria
AS
	set nocount on
	set datefirst 1

	declare @errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint,
			@timeOut			smallint,
			@lastMonday			datetime,
			@firstOpenShiftID	int,
			@maxNoOfLine		tinyint = 10,	
			@moreLine			nchar(3)

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID, CustomParams)
	values(18, @UserID,
		N'@DailyTargetID=' + ISNULL(CONVERT(nvarchar(10), @DailyTargetID), N'NULL') + N',' +
		N'@Tags=' + ISNULL(@Tags, N'NULL'))
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		if @DailyTargetID is not NULL
			exec [global].CheckObjectID @ObjectSchema = N'target', @ObjectTable = N'Daily', @ObjectID = @DailyTargetID, @CheckIsNull = 0, @ProcedureLogID = @procedureLogID
		exec [global].GetSettingKeyValue @Key = N'SESSION_EXPIRES_IN_MINUTES', @ProcedureLogID = @procedureLogID, @Value = @timeOut OUTPUT

		-- setting constant parameters
		set @lastMonday = DATEADD(day, -7, [global].NextMonday([global].[GetDate]()))
		select @firstOpenShiftID = MIN(ID) from dbo.vShiftLog where DataStart > [global].[GetDate]()


		declare @days table(ShiftLogID int, Data datetime)
		declare @lines table(ID smallint IDENTITY(1,1), LineID int, Name nvarchar(50), Tags nvarchar(max), Capacity smallint, FirstOpenShiftID int)
		declare @targets table(ID int, LineID int, ShiftLogID int, [Day] tinyint, ShiftType char(1), Value smallint)

		insert into @days(ShiftLogID, [Data])
		select ID, [Data]
		from dbo.ShiftLog
		where DATEDIFF(DAY, @lastMonday, [Data]) between 0 and 13
		order by [Data]

		if @DailyTargetID is not NULL
			insert into @lines(LineID, [Name], Tags, Capacity)
			select d.LineID, vl.[name], vl.tags, vl.capacity
			from [target].Daily d
				inner join [layout].vActiveLines vl on d.LineID = vl.id
			where d.ID = @DailyTargetID
		else if @Tags is not NULL
		begin

/*
			-- prepare @Tags for CONTAIMNES
			-- remove all trailing spaces and replace commas with blanks
			set @Tags = REPLACE(RTRIM(LTRIM(@tags)), N',', N' ')
			-- a single blank should exist between words 
			while CHARINDEX(N'  ', @tags, 1) <> 0
			begin
				set @tags = REPLACE(@tags, N'  ', N' ')
			end
			---- add AND operator (for CONTAINS)
			--set @tags = REPLACE(@tags, N' ', N' AND ')

			set @tags = N'%' + REPLACE(@tags, N' ', N'%') + N'%'

			insert into @lines(LineID, [Name], Tags)
			select ID, [Name], Tags
			from [layout].Line
			--where CONTAINS(Tags, @Tags)
			where Tags like @Tags
--				or [Name] like @Tags
			order by [Name]
*/

			insert into @lines(LineID, [Name], Tags)
			select distinct l.ID, l.[Name], l.Tags
			from [layout].Line l
				inner join [layout].LineTag lt on l.ID = lt.LineID
			where lt.Tag = @Tags
			order by [Name]

			-- limit max no of line in XML
			if @@ROWCOUNT > @maxNoOfLine set @moreLine = 'yes'
			if @moreLine = 'yes'
				delete @lines where ID > @maxNoOfLine
		end
		else goto NoLines

		-- add Capacity and FirstOpenShiftID info
		update _l
		set Capacity = ISNULL(lc.Capacity, 0),
			FirstOpenShiftID = ISNULL(sl.FirstOpenShiftID, @firstOpenShiftID)
		from @lines _l
			left join (
				select c.LineID, ROUND(SUM(w.HourCapacity) * 8 * 11 / 12, 0) Capacity
				from layout.Cell c
					inner join layout.Workbench w on c.ID = w.CellID
				where w.EOL = 1
				group by c.LineID
			) lc on _l.LineID = lc.LineID
			left join (
				select LineID, MIN(ShiftLogID) FirstOpenShiftID
				from dbo.ShiftLogSignOff
				where SignedOffOperatorID is NULL
				group by LineID
			) sl on _l.LineID = sl.LineID

		insert into @targets(ID, LineID, ShiftLogID, [Day], [ShiftType], [Value])
		select d.ID, _l.LineID, sl.ID, DATEDIFF(DAY, @lastMonday, sl.[Data]) + 1, sl.ShiftType, d.[Value]
		from @lines _l
			cross join dbo.ShiftLog sl
			left join [target].Daily d on _l.LineID = d.LineID and sl.ID = d.ShiftLogID and d.TypeID = 'DY'
		where sl.ID in (select ShiftLogID from @days)
		order by _l.[Name], sl.[Data], sl.ShiftType

		set @targetsXML = (
			select a.*
			from (
				select 1 as Tag,
					NULL as Parent,
					@moreLine as [targets!1!moreLinesAvailable],
					CONVERT(char(23), [global].[GetDate](), 121) [targets!1!timeStamp],
					@timeout [targets!1!timeOut], 
					NULL as [forLine!2!name],
					NULL as [forLine!2!tags],
					NULL as [forLine!2!shiftCapacity],
					NULL as [forLine!2!firstOpenShiftLogId],
					NULL as [forLine!2!id],
					NULL as [target!3!shiftLogId],
					NULL as [target!3!day],
					NULL as [target!3!name],
					NULL as [target!3!id],
					NULL AS [target!3]
				union all
				select distinct
					2 as Tag,
					1 as Parent,
					@moreLine as [target!1!moreLinesAvailable],
					CONVERT(char(23), [global].[GetDate](), 121) [timeStamp!1],
					@timeout [timeOut!1], 
					[Name],
					ISNULL(Tags, N''),
					Capacity,
					FirstOpenShiftID,
					LineID,
					NULL,
					NULL,
					NULL,
					NULL,
					NULL
				from @lines _l
				union all
				select 3 as Tag,
					2 as Parent,
					@moreLine as [target!1!moreLinesAvailable],
					CONVERT(char(23), [global].[GetDate](), 121) [timeStamp!1],
					@timeout [timeOut!1], 
					_l.[Name],
					_l.Tags,
					_l.Capacity,
					_l.FirstOpenShiftID,
					_t.LineID,
					_t.ShiftLogID,
					_t.[Day],
					_t.ShiftType,
					_t.ID,
					_t.Value
				from @lines _l
					inner join @targets _t on _l.LineID = _t.LineID) a
			order by a.[forLine!2!name], a.[target!3!day], a.[target!3!name]
			for xml explicit, root('root'))
		if @@ROWCOUNT = 0 goto EmptyXML

		update [log].ProcedureLog
		set XMLParam = @targetsXML
		where ID = @procedureLogID

NoLines:
		set @weeksXML = (
			select
				(select DATENAME(WEEK, Data) '@id', CONVERT(char(10), MIN(Data), 120) 'start', CONVERT(char(10), MAX(Data), 120) 'end'
				from @days
				group by DATENAME(WEEK, Data)
				order by '@id'
				for xml path('week'), root('weeks'), type)
			for xml path('root'), type)
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
