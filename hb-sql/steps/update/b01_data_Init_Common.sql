-- common initializations
USE MultiCodeBoard
GO

print 'Settings'
GO
insert into [global].Setting ([Key], [Value], [Note]) values
	('AUTH_TOKEN_EXPIRES_IN_SECONDS', '36000', '10 hours until login expires'),
	('SESSION_EXPIRES_IN_MINUTES', '60', '1 hour until session expires')

GO

print 'Procedures'
GO
insert into [log].[Procedure] ([ID], [Name]) values
	(1, N'[users].[GetRights]'),
	(2, N'[users].[SaveRights]'),
	(3, N'[dbo].[GetBreaks]'),
	(4, N'[dbo].[SaveBreaks]'),
	(5, N'[layout].[GetWorkbenches]'),
	(6, N'[layout].[SaveWorkbenches]'),
	(7, N'[target].[GetPlanningDataSets]'),
	(8, N'[layout].[AddMonitor]'),
	(9, N'[layout].[EditMonitor]'),
	(10, N'[layout].[DeleteMonitor]'),
	(11, N'[dbo].[GetBillboard]'),
	(12, N'[dbo].[BillboardSaveComment]'),
	(13, N'[dbo].[BillboardEscalated]'),
	(14, N'[dbo].[BillboardHourSignOff]'),
	(15, N'[dbo].[BillboardShiftSignOff]'),
	(16, N'[dbo].[GetDowntime]'),
	(17, N'[dbo].[SaveDowntime]'),
	(18, N'[dbo].[GetTargetByDay]'),
	(19, N'[dbo].[SaveTargetByDate]'),
	(20, N'[dbo].[GetTargetByPartNumber]'),
	(21, N'[dbo].[SaveTargetByPartNumber]'),
	(22, N'[target].[SetOnBillboard]'),
	(23, N'[target].[SetBillboardOnByIDList]'),
	(24, N'[report].[HistoricalShift]'),
	(25, N'[report].[ActualsVsTargetAndNotOK]'),
	(26, N'[report].[DowntimeReason]')
GO

-- location
print 'Location'
GO
insert into layout.Location(ID, [Name], Deleted) values('TM', N'Timisoara', 0)
GO


print 'ShiftLog'
GO
-- ShiftLog
declare @dataStart	datetime,
		@shiftType	char(1) = 'A',
		@shiftLogID	int
set @dataStart = '08/01/2018 07:00'
while DATEDIFF(year, GETDATE(), @dataStart) < 1
begin
	insert into dbo.ShiftLog(LocationID, ShiftType, Data, DataStart, PreviousShiftLogID)
	select 'TM', @shiftType, CONVERT(date,  @dataStart), @dataStart, @shiftLogID

	select @shiftLogID = SCOPE_IDENTITY(),
			@dataStart = DATEADD(HOUR, 8, @dataStart),
			@shiftType = case @shiftType when 'A' then 'B' when 'B' then 'C' when 'C' then 'A' end
end
GO

print 'ShiftLogBreak'
GO
-- ShiftLogBreak
insert into dbo.ShiftLogBreak(ShiftLogID, TimeStart, TimeEnd, UpdateDate)
select ID, DATEADD(MINUTE, 150, DataStart), DATEADD(MINUTE, 170, DataStart), [global].[GetDate]() from dbo.ShiftLog
union
select ID, DATEADD(MINUTE, 330, DataStart), DATEADD(MINUTE, 350, DataStart), [global].[GetDate]() from dbo.ShiftLog
GO

print 'Users'
GO
-- user level
insert into users.[Level](ID, Name, Help) values
	(1, N'Super User', N'This is Super User security level'),
	(2, N'IT Admin', N'This is IT Administrator security level'),
	(5, N'Airbag BUM', N'This is a Manager for the Airbags Business Unit'),
	(6, N'Assy BUM', N'This is a Manager for the Assembly Business Unit'),
	(99, N'Standard operator', N'This user has no rights')
GO

print 'AccountProvider'
GO
-- account provider
insert into users.AccountProvider(ID, Name)
values(1, N'MultiCode Hourly Board')
GO

print 'Profile'
GO
-- profile
insert into users.[Profile](FirstName, LastName, LevelID, Barcode, OperatorID) values
	(N'Super', N'User', 1, N'0000', NULL),
	(N'IT', N'Admin', 2, N'0001', 1),
	(N'Airbag', N'Demo User', 5, N'00021', NULL),
	(N'Assy', N'Demo User', 6, N'00022', NULL)
GO

print 'Account'
GO
-- account
insert into users.Account(Username, [Password], AccountProviderUniqueAppID, AccountProviderID, ProfileID) values
    (N'superuser', N'098f6bcd4621d373cade4e832627b4f6', N'george.gheorghe@trw.com', 1, 1),
    (N'admin', N'098f6bcd4621d373cade4e832627b4f6', N'ion.popescu@trw.com', 1, 2),
	(N'airbag', N'098f6bcd4621d373cade4e832627b4f6', N'd001@trw.com', 1, 3),
	(N'assy', N'098f6bcd4621d373cade4e832627b4f6', N'd002@trw.com', 1, 4)
GO

print 'Feature'
GO
-- feature
insert into users.Feature(ID, RequestorLevelID, TargetLevelID, Operation, UpdateUserID, UpdateDate) values
	(N'HOURLY-SIGN-OFF', 1, 1, N'X', 1, [global].[GetDate]()),
	(N'SHIFT-SIGN-OFF', 1, 1, N'X', 1, [global].[GetDate]()),
	(N'HOURLY-SIGN-OFF', 1,2, NULL, 1, [global].[GetDate]()),
	(N'SHIFT-SIGN-OFF', 1, 2, NULL, 1, [global].[GetDate]()),
	(N'HOURLY-SIGN-OFF', 1, 5, NULL, 1, [global].[GetDate]()),
	(N'SHIFT-SIGN-OFF', 1, 5, NULL, 1, [global].[GetDate]()),
	(N'HOURLY-SIGN-OFF', 1, 6, NULL, 1, [global].[GetDate]()),
	(N'SHIFT-SIGN-OFF', 1, 6, NULL, 1, [global].[GetDate]())
GO


print 'WorkbenchType'
GO
insert into layout.WorkbenchType(ID, [Name], [Description]) values
	(1, N'Standard', N'Standard')
GO

print 'Planning dataset type'
GO
insert into [target].[Type](ID, [Name]) values
	('DY', 'Target by day'),
	('PN', 'Target by part number'),
	('HY', 'Target by hour - manual')
