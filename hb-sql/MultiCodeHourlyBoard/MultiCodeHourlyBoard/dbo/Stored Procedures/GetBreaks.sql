/*
	Author/Date	:	Cristian Dinu, 07.08.2018
	Description	:	get current breaks list for editing
	LastChange	:	
*/

CREATE PROCEDURE [dbo].[GetBreaks]
	@UserID	int,
	@NextWeek bit = 1,
	@XML	XML	OUTPUT
AS
	set nocount on

	declare @errorNumber		int = 16,
			@errorMessage		nvarchar(max),
			@procedureLogID		bigint,
			@timeOut			smallint,
			@nextMonday			datetime,
			@xmlMonday			datetime

	-- log procedure exec
	insert into [log].ProcedureLog(ProcedureID, ProfileID)
	values(3, @UserID)
	select @procedureLogID = SCOPE_IDENTITY()

	begin try
		-- check if input parameters are valid
		exec [global].CheckObjectID @ObjectSchema = N'users', @ObjectTable = N'Profile', @ObjectID = @UserID, @CheckIsNull = 1, @ProcedureLogID = @procedureLogID
		exec [global].GetSettingKeyValue @Key = N'SESSION_EXPIRES_IN_MINUTES', @ProcedureLogID = @procedureLogID, @Value = @timeOut OUTPUT
		set @nextMonday = [global].NextMonday([global].[GetDate]())
		set @xmlMonday = case @NextWeek when 1 then @nextMonday else DATEADD(WEEK, -1, @nextMonday) end

		set @XML = (
			select CONVERT(char(23), [global].[GetDate](), 121) [timeStamp], @timeout [timeOut],
				CONVERT(nvarchar(10), @nextMonday, 120) + N' Shift A' [startingWith], 
				(
				select l.ID '@location', 
					(select sl.ShiftType '@name', sl.ID '@shiftLogID',
						CONVERT(char(5), sl.DataStart, 114) 'from',
						case when DATEDIFF(DAY, sl.[Data], slp.DataStart) = 1 then 'Yes' end 'to/@nextDay',
						CONVERT(char(5), slp.DataStart, 114) 'to',
						(
							select  CONVERT(char(16), TimeStart, 121) '@timeStart',
								case when DATEDIFF(DAY, sl.[Data], TimeStart) = 1 then 'Yes' end 'from/@nextDay',
								CONVERT(char(5), TimeStart, 114) [from],
								case when DATEDIFF(DAY, sl.[Data], TimeEnd) = 1 then 'Yes' end 'to/@nextDay',
								CONVERT(char(5), TimeEnd, 114) [to]
							from dbo.ShiftLogBreak
							where ShiftLogID = sl.ID
							for xml path('break'), type)
					from dbo.ShiftLog sl
						inner join dbo.ShiftLog slp on sl.ID = slp.PreviousShiftLogID
					where sl.LocationID = l.ID and sl.[Data] = @xmlMonday
					order by sl.LocationID, sl.ShiftType
					for xml path('shift'), type)
				from layout.Location l
				for xml path('breaks'), type)
			for xml path('root'), type)
		if @@ROWCOUNT = 0 goto EmptyXML

		update [log].ProcedureLog
		set XMLParam = @XML
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
