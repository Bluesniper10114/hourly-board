/*
	Author/Date	:	Cristian Dinu, 10.08.2018
	Description	:	process edited downtime details
	LastChange	:	
*/

CREATE PROCEDURE [dbo].[SaveDowntime]
	@TargetHourlyID int,
	@XML			XML,
	@errorMessage	nvarchar(max) OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@procedureLogID		bigint
	declare @txml table(
					ID			int,
					DowntimeID	int,
					Comment		nvarchar(100),
					Duration	smallint,
					[TimeStamp]	datetime)

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, XMLParam)
	values(17, @XML)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try

		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectTable = N'BillboardLog', @ObjectColumnID = N'TargetHourlyID', @ObjectID = @TargetHourlyID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID

		-- get info from XML
		insert into @txml(ID, DowntimeID, Comment, Duration, [TimeStamp])
		select
			T.[Downtime].value('@id', 'int') as [ID],
			T.[Downtime].value('../../@id', 'int') as [DowntimeID],
			T.[Downtime].value('comment[1]', 'nvarchar(100)') as [Comment],
			T.[Downtime].value('duration[1]', 'smallint') as [Duration],
			CONVERT(datetime, T.[Downtime].value('@timeStamp', 'char(23)'), 121) as [TimeStamp]
		from @xml.nodes('//reason') as T([Downtime])

		-- checking zone
		-- if exist recent updates, after XML timestamp
		select top 1 @errorMessage = N'During current edit session another user started downtime details changes'
		from @txml
		where [TimeStamp] is not NULL
			and [TimeStamp] < (
				select MAX(dd.UpdateDate)
				from dbo.DowntimeDetails dd
					inner join dbo.Downtime d on dd.DowntimeID = d.ID
				where d.TargetHourlyID = @TargetHourlyID)
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- if there are empty fields
		select top 1 @errorMessage = N'There are records with missing DowntimeID values'
		from @txml
		where DowntimeID is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing Comment values'
		from @txml
		where Comment is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		select top 1 @errorMessage = N'There are records with missing Duration values'
		from @txml
		where Duration is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if ID is correct
		select top 1 @errorMessage = N'There are records with incorrect DowntimeDetailsID values (e.g. ' + CONVERT(nvarchar(10), _t.ID) + N')'
		from @txml _t
			left join dbo.DowntimeDetails dd on _t.ID = dd.ID
			left join dbo.Downtime d on dd.DowntimeID = d.ID and d.TargetHourlyID = @TargetHourlyID
		where _t.ID is not NULL
			and d.ID is NULL
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if DowntimeID is correct
		select top 1 @errorMessage = N'There are records with incorrect DowntimeID values (e.g. ' + CONVERT(nvarchar(10), DowntimeID) + N')'
		from @txml
		where DowntimeID not in (select ID from dbo.Downtime)
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if Duration is correct
		select top 1 @errorMessage = N'There are records with negative Duration values (e.g. ' + CONVERT(nvarchar(10), Duration) + N')'
		from @txml
		where Duration < 0
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- check if Duration is correct
		select top 1 @errorMessage = N'There are records with incorrect Duration values, greater than downtime interval (' + CONVERT(nvarchar(10), Duration) + N' minutes)'
		from @txml _t
			inner join dbo.Downtime d on _t.DowntimeID = d.ID
		where _t.Duration > DATEDIFF(MINUTE, d.DataStart, d.DataEnd)
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- total duration <= downtime interval
		select top 1 @errorMessage = N'Total reasons duration (' + CONVERT(nvarchar(10), SUM(_t.Duration))
			+ N'min) is different than downtime total duration (' + CONVERT(nvarchar(10), DATEDIFF(MINUTE, d.DataStart, d.DataEnd)) + N'min)'
		from @txml _t
			inner join dbo.Downtime d on _t.DowntimeID = d.ID
		group by _t.DowntimeID, d.DataStart, d.DataEnd
		having SUM(_t.Duration) <> DATEDIFF(MINUTE, d.DataStart, d.DataEnd)
		if @@ROWCOUNT <> 0 goto ErrorExit

		-- data updates zone
		begin tran
			-- delete removed downtime details
			delete dd
			from dbo.DowntimeDetails dd
				inner join dbo.Downtime d on dd.DowntimeID = d.ID
			where d.TargetHourlyID = @TargetHourlyID
				and dd.ID not in (select ID from @txml)
				
			-- insert new downtime details
			insert into dbo.DowntimeDetails(DowntimeID, Comment, Duration, UpdateDate)
			select DowntimeID, Comment, Duration, [global].[GetDate]()
			from @txml
			where ID is NULL

			-- update downtime details
			update dd
			set Comment = _t.Comment,
				Duration = _t.Duration,
				UpdateDate = [global].[GetDate]()
			from dbo.DowntimeDetails dd
				inner join @txml _t on dd.ID = _t.ID
			where dd.Comment <> _t.Comment
				or dd.Duration <> _t.Duration
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
