/*
	Author/Date	:	Cristian Dinu, 07.12.2018
	Description	:	transfer actuals data log from buffer table
	LastChange	:
*/

CREATE PROCEDURE [import].[GetActualsLog]
AS
	set nocount on

	declare @errorNumber		int = 16,
			@errorMessage		nvarchar(max)
	declare @actualsLog table(
							ID bigint NOT NULL PRIMARY KEY, 
							[Date] datetime,
							ShiftType char(1),
							Machine varchar(50),
							MachineAlternative tinyint,
							IsOK bit,
							[TimeStamp] datetime,
							WorkbenchID int,
							ShiftLogID int,
							ShiftLogType char(1),
							ActionType char(1) NOT NULL)

	begin try
		-- delete errors for deleted records, errors are saved by trigger in ActualsDeletedErrors
		delete import.ActualsLogErrors
		where ActualsLogID not in (select ID from import.ActualsLog)

		-- delete errors for modified records, errors are saved by trigger in ActualsDeletedErrors
		delete ale
		from import.ActualsLogErrors ale
			inner join import.ActualsLog al on ale.ActualsLogID = al.ID
		where ale.[TimeStamp] < al.[TimeStamp]

		-- add records that should be processed (new, modified, deleted)
		-- select only the new records (ID's that are not in dbo.ActualsLog) and with no error
		insert into @actualsLog(ID, [Date], ShiftType, Machine, MachineAlternative, IsOK, [TimeStamp], WorkbenchID, ShiftLogID, ShiftLogType, ActionType)
		select ial.ID, ial.[Date], ial.ShiftType, ial.Machine, ial.MachineAlternative, ial.IsOK, ial.[TimeStamp], m.WorkbenchID, sl.ID, sl.ShiftType, 'N' ActionType
		from import.ActualsLog ial
			left join import.Machine m on ial.Machine = m.Machine and ial.MachineAlternative = m.MachineAlternative
			left join dbo.vShiftLog sl on ial.[Date] >= sl.DataStart and ial.[Date] < sl.DataEnd
		where ial.ID not in (select ID from dbo.ActualsLog)
			and ial.ID not in (select ActualsLogID from import.ActualsLogErrors)
		union all
		select ial.ID, ial.[Date], ial.ShiftType, ial.Machine, ial.MachineAlternative, ial.IsOK, ial.[TimeStamp], m.WorkbenchID, sl.ID, sl.ShiftType, 'M'
		from import.ActualsLog ial
			inner join dbo.ActualsLog al on ial.ID = al.ID
			left join import.Machine m on ial.Machine = m.Machine and ial.MachineAlternative = m.MachineAlternative
			left join dbo.vShiftLog sl on ial.[Date] >= sl.DataStart and ial.[Date] < sl.DataEnd
		where ial.[TimeStamp] > al.[TimeStamp]
		union all
		select ID, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'D'
		from dbo.ActualsLog
		where ID not in (select ID from import.ActualsLog)
		if @@ROWCOUNT = 0 goto DoNothing

		-- from here changes are made in DB
		begin tran
			-- delete errors for nodified records, for deleted records errors are saved by trigger in ActualsDeletedErrors
			delete import.ActualsLogErrors
			where ActualsLogID in (select ID from @actualsLog)

			-- find errors
			-- missing machines
			insert into import.ActualsLogErrors(ActualsLogID, ErrorType, ErrorDescription, [Date])
			select ID, 1, N'Missing machine in HourlyBoard layout', [Date]
			from @actualsLog
			where ActionType <> 'D'
				and WorkbenchID is NULL

			-- missing shift log
			insert into import.ActualsLogErrors(ActualsLogID, ErrorType, ErrorDescription, [Date])
			select ID, 2, N'Missing shift log in HourlyBoard layout', [Date]
			from @actualsLog
			where ActionType <> 'D'
				and ShiftLogID is NULL

			---- different shift type
			--insert into import.ActualsLogErrors(ActualsLogID, ErrorType, ErrorDescription, [Date])
			--select ID, 3, N'Different shift type in HourlyBoard layout (' + ShiftLogType + N') than in import records', [Date]
			--from @actualLog
			--where ActionType <> 'D'
			--	and ShiftType <> ShiftLogType

			-- check for doubled records (machine + date) - new or modified records
			insert into import.ActualsLogErrors(ActualsLogID, ErrorType, ErrorDescription, [Date])
			select _al.ID, 4, N'Doubled records', _al.[Date]
			from @actualsLog _al
				inner join (
					select Machine, MachineAlternative, [Date]
					from @actualsLog
					where ActionType <> 'D'
					group by Machine, MachineAlternative, [Date]
					having COUNT(*) > 1
				) _ald on _al.Machine = _ald.Machine and _al.MachineAlternative = _ald.MachineAlternative and _al.[Date] = _ald.[Date]

			-- check for doubled records with existing records in dbo.ActualsLog
			insert into import.ActualsLogErrors(ActualsLogID, ErrorType, ErrorDescription, [Date])
			select _al.ID, 5, N'Already existing record on previous update', _al.[Date]
			from @actualsLog _al
				inner join dbo.ActualsLog al on _al.[Date] = al.Date and _al.WorkbenchID = al.WorkbenchID

			-- update ActualsLog
			-- deleted records
			delete dbo.ActualsLog
			where ID in (select ID from @actualsLog where ActionType = 'D')

			-- new records
			insert into dbo.ActualsLog(ID, WorkbenchID, ShiftLogID, IsOK, [Date], [TimeStamp])
			select ID, WorkbenchID, ShiftLogID, IsOK, [Date], [TimeStamp]
			from @actualsLog
			where ActionType = 'N'
				and ID not in (select ActualsLogID from import.ActualsLogErrors)
		
			-- modified records
			update al
			set WorkbenchID = _al.WorkbenchID,
				ShiftLogID = _al.ShiftLogID,
				IsOK = _al.IsOK,
				[Date] = _al.[Date],
				[TimeStamp] = _al.[TimeStamp]
			from dbo.ActualsLog al
				inner join @actualsLog _al on al.ID = _al.ID
			where _al.ActionType = 'M'
				and al.ID not in (select ActualsLogID from import.ActualsLogErrors)

			-- update BillboardLog
			declare @shiftLogID int = 0
			while 1=1
			begin
				select top 1 @shiftLogID = ShiftLogID
				from @actualsLog
				where ShiftLogID > @shiftLogID
				order by ShiftLogID
				if @@ROWCOUNT = 0 break

				exec dbo.BillboardUpdateActuals @ShiftLogID = @shiftLogID
			end

		if @@TRANCOUNT > 0 commit tran

		update import.Machine set ReadyForImport = 1

DoNothing:
	end try
	begin catch
		set @errorNumber = ERROR_NUMBER()
		set @errorMessage =  ERROR_MESSAGE()
		if @@TRANCOUNT > 0 rollback tran

		update import.Machine set ReadyForImport = 0
		goto ErrorExit
	end catch
return(0)

ErrorExit:
	raiserror(@errormessage, 16, 1)
	return(@errorNumber)