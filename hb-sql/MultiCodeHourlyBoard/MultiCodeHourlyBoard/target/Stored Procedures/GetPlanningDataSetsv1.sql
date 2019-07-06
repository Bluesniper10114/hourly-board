/*
	@filtersXML:
	<root>
	 <lines><line>1</line><line>2</line></lines>
	 <dates><date>2019-02-13</date><date>2019-02-14</date></dates>
	</root>
*/


CREATE PROCEDURE [target].[GetPlanningDataSetsv1]
	@UserID		int,
	@FiltersXML	XML, 
	@TargetsXML	XML	OUTPUT,
	@errorMessage nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@procedureLogID		bigint,
			@timeOut			smallint,
			@nextMonday			datetime
	declare @lines	table(LineID int)
	declare @dates	table([Data] datetime) 

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID)
	values(7, @UserID)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		exec [global].GetSettingKeyValue @Key = N'SESSION_EXPIRES_IN_MINUTES', @ProcedureLogID = @procedureLogID, @Value = @timeOut OUTPUT
		set @nextMonday = [global].NextMonday([global].[GetDate]())

		-- get info from XML
		insert into @lines(LineID)
		select
			T.[lines].value('.', 'int') as [Value]
		from @FiltersXML.nodes('//line') as T([lines])

		insert into @dates([Data])
		select
			T.[dates].value('.', 'datetime') as [Value]
		from @FiltersXML.nodes('//date') as T([dates])

		set @TargetsXML = (
			select CONVERT(char(23), [global].[GetDate](), 121) [timeStamp], @timeout [timeOut],
				CONVERT(nvarchar(10), @nextMonday, 120) + N' Shift A' [startingWith], 
				(
					select d.ID '@dailyTargetID', d.LocationID '@location', l.Tags '@tags',
						case when slso.SignedOffOperatorID is NULL then 'Yes' else 'No' end '@open',
						d.LineID 'line/@id', l.[Name] line, CONVERT(nvarchar(10), d.[Data], 120) [date], d.ShiftType [shift],
						d.TypeID [type], case d.Billboard when 1 then 'Yes' else 'No' end [billboard],
						h1.[Value] qtyHour_1, h2.[Value] qtyHour_2, h3.[Value] qtyHour_3,
						h4.[Value] qtyHour_4, h5.[Value] qtyHour_5, h6.[Value] qtyHour_6,
						h7.[Value] qtyHour_7, h8.[Value] qtyHour_8, d.[Value] qtyTotal
					from [target].vDaily d
						left join dbo.ShiftLogSignOff slso on d.LineID = slso.LineID and d.ShiftLogID = slso.ShiftLogID
						inner join layout.Line l on d.LineID = l.ID
						left join [target].Hourly h1 on d.ID = h1.DailyID and h1.[Hour] = 1
						left join [target].Hourly h2 on d.ID = h2.DailyID and h2.[Hour] = 2
						left join [target].Hourly h3 on d.ID = h3.DailyID and h3.[Hour] = 3
						left join [target].Hourly h4 on d.ID = h4.DailyID and h4.[Hour] = 4
						left join [target].Hourly h5 on d.ID = h5.DailyID and h5.[Hour] = 5
						left join [target].Hourly h6 on d.ID = h6.DailyID and h6.[Hour] = 6
						left join [target].Hourly h7 on d.ID = h7.DailyID and h7.[Hour] = 7
						left join [target].Hourly h8 on d.ID = h8.DailyID and h8.[Hour] = 8
					where d.LineID in (select LineID from @lines)
						and d.[Data] in (select [Data] from @dates) 
					order by d.LocationID, l.[Name], d.[Data], d.TypeID, d.ShiftType
					for xml path('row'), type, ELEMENTS XSINIL)
			for xml path('root'), type)
		if @@ROWCOUNT = 0 goto EmptyXML

		update [log].ProcedureLog
		set XMLParam = @TargetsXML
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