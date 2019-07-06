-- AIRBAG initializations
USE MultiCodeBoard
GO

print 'AIRBAG dictionary'
GO

-- Billboard comments
insert into dbo.CommentsDictionary([Text]) values
	(N'Productie incetinita erori utilaj'),
	(N'Productie incetinita operator in scolarizare/fara experienta'),
	(N'Productie incetinita probleme de calitate componente'),
	(N'Productie incetinita erori trasabilitate'),
	(N'Productie oprita-motive HSE')
GO
 
 --Billboard escalated (de pus la separarea scripturilor pe ASSY/AIRBAG)
insert into dbo.EscalatedDictionary([Text]) values
	(N'TL'),
	(N'PL'),
	(N'Mentenanta'),
	(N'BUM'),
	(N'SQA'),
	(N'Q'),
	(N'PQE'),
	(N'ME'),
	(N'Logistica'),
	(N'IT')
GO

-- Billboard downtime
insert into dbo.DowntimeDictionary([Text]) values
	(N'Utilaj defect'),
	(N'Schimbare model'),
	(N'Probleme IT'),
	(N'Probleme de calitate'),
	(N'Testare utilaj de catre ME'),
	(N'Sedinta'),
	(N'Curatenie'),
	(N'Lipsa operatori'),
	(N'Scolarizare/evaluare operatori'),
	(N'Lipsa materiale / ambalaje(organizare logistica)'),
	(N'Lipsa materiale / ambalaje(intarziere depozit)'),
	(N'Lipsa materiale / ambalaje(organizare interna)'),
	(N'Lipsa plan'),
	(N'Utilaj defect remediat de TL')
GO

-- AIRBAG
print 'Line AIRBAG'
GO
-- line
insert into layout.Line([Name], LocationID) values
	(N'Pab X52', 'TM'),
	(N'SAB HJD', 'TM'),
	(N'SAB HJD RH', 'TM'),
	(N'SAB HJD LH', 'TM'),
	(N'PAB HJD', 'TM'),
	(N'DAB M0', 'TM'),
	(N'CAB HJD A', 'TM'),
	(N'CAB HJD B', 'TM'),
	(N'Thab X52 Ph1', 'TM'),
	(N'THAB X52 PH 2 A', 'TM'),
	(N'THAB X52 PH 2 B', 'TM'),
	(N'DAIMLER DAB V4', 'TM'),
	(N'Porsche DAB 992', 'TM')
GO

insert into layout.LineTag(LineID, Tag)
select ID, [Name]
from layout.Line
where ID not in (select LineID from layout.LineTag)
GO

insert into layout.LineTag(LineID, Tag)
select ID, N'SAB'
from layout.Line
where [Name] like 'SAB%'

insert into layout.LineTag(LineID, Tag)
select ID, N'CAB'
from layout.Line
where [Name] like 'CAB%'

insert into layout.LineTag(LineID, Tag)
select ID, N'THAB'
from layout.Line
where [Name] like 'THAB%'
GO

print 'Cell AIRBAG'
GO
-- cell
insert into layout.Cell([Name], LineID)
select N'Pab X52', ID from layout.Line where [Name] = N'Pab X52'

insert into layout.Cell([Name], LineID)
select N'SAB HJD', ID from layout.Line where [Name] = N'SAB HJD'

insert into layout.Cell([Name], LineID)
select N'SAB HJD RH', ID from layout.Line where [Name] = N'SAB HJD RH'

insert into layout.Cell([Name], LineID)
select N'SAB HJD LH', ID from layout.Line where [Name] = N'SAB HJD LH'

insert into layout.Cell([Name], LineID)
select N'PAB HJD', ID from layout.Line where [Name] = N'PAB HJD'

insert into layout.Cell([Name], LineID)
select N'DAB M0', ID from layout.Line where [Name] = N'DAB M0'

insert into layout.Cell([Name], LineID)
select N'CAB HJD A', ID from layout.Line where [Name] = N'CAB HJD A'

insert into layout.Cell([Name], LineID)
select N'CAB HJD B', ID from layout.Line where [Name] = N'CAB HJD B'

insert into layout.Cell([Name], LineID)
select N'Thab X52 Ph1', ID from layout.Line where [Name] = N'Thab X52 Ph1'

insert into layout.Cell([Name], LineID)
select N'THAB X52 PH 2 A', ID from layout.Line where [Name] = N'THAB X52 PH 2 A'

insert into layout.Cell([Name], LineID)
select N'THAB X52 PH 2 B', ID from layout.Line where [Name] = N'THAB X52 PH 2 B'

insert into layout.Cell([Name], LineID)
select N'DAIMLER DAB V4', ID from layout.Line where [Name] = N'DAIMLER DAB V4'

insert into layout.Cell([Name], LineID)
select N'Porsche DAB 992', ID from layout.Line where [Name] = N'Porsche DAB 992'
GO

print 'Workbench AIRBAG'
GO
-- workbench
insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP 10', N'ER2_0101', ID, 1, 1, 124 from layout.Cell where [Name] = N'Pab X52'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP20', N'ER2_0102', ID, 1, 1, 124 from layout.Cell where [Name] = N'Pab X52'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP30', N'ER2_0103', ID, 1, 1, 124 from layout.Cell where [Name] = N'Pab X52'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP15', N'ER2_0201', ID, 1, 1, 120 from layout.Cell where [Name] = N'SAB HJD'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP32 A', N'ER2_0301', ID, 1, 1, 60 from layout.Cell where [Name] = N'SAB HJD RH'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP60 A', N'ER2_0302', ID, 1, 1, 60 from layout.Cell where [Name] = N'SAB HJD RH'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP120 A', N'ER2_0303', ID, 1, 1, 60 from layout.Cell where [Name] = N'SAB HJD RH'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP32 B', N'ER2_0401', ID, 1, 1, 60 from layout.Cell where [Name] = N'SAB HJD LH'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP60 B', N'ER2_0402', ID, 1, 1, 60 from layout.Cell where [Name] = N'SAB HJD LH'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'Op120 B', N'ER2_0403', ID, 1, 1, 60 from layout.Cell where [Name] = N'SAB HJD LH'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP 50', N'ER2_0501', ID, 1, 1, 74 from layout.Cell where [Name] = N'PAB HJD'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP 80', N'ER2_0502', ID, 1, 1, 74 from layout.Cell where [Name] = N'PAB HJD'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP 75', N'ER2_0503', ID, 1, 1, 74 from layout.Cell where [Name] = N'PAB HJD'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'Op 150', N'ER2_0504', ID, 1, 1, 74 from layout.Cell where [Name] = N'PAB HJD'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP10', N'ER2_0601', ID, 1, 1, 153 from layout.Cell where [Name] = N'DAB M0'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP30 A', N'ER2_0602', ID, 1, 1, 67 from layout.Cell where [Name] = N'DAB M0'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'Op30 B', N'ER2_0603', ID, 1, 1, 67 from layout.Cell where [Name] = N'DAB M0'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP70', N'ER2_0604', ID, 1, 1, 135 from layout.Cell where [Name] = N'DAB M0'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP100', N'ER2_0605', ID, 1, 1, 135 from layout.Cell where [Name] = N'DAB M0'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP150', N'ER2_0606', ID, 1, 1, 135 from layout.Cell where [Name] = N'DAB M0'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP 50 A', N'ER2_0701', ID, 1, 1, 65 from layout.Cell where [Name] = N'CAB HJD A'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP 80 A', N'ER2_0702', ID, 1, 1, 65 from layout.Cell where [Name] = N'CAB HJD A'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'Op 120 A', N'ER2_0703', ID, 1, 1, 65 from layout.Cell where [Name] = N'CAB HJD A'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP 50 B', N'ER2_0801', ID, 1, 1, 65 from layout.Cell where [Name] = N'CAB HJD B'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP 80 B', N'ER2_0802', ID, 1, 1, 65 from layout.Cell where [Name] = N'CAB HJD B'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'Op 120 B', N'ER2_0803', ID, 1, 1, 65 from layout.Cell where [Name] = N'CAB HJD B'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP 10 A', N'ER2_0901', ID, 1, 1, 205 from layout.Cell where [Name] = N'Thab X52 Ph1'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'OP 30', N'ER2_0902', ID, 1, 1, 95 from layout.Cell where [Name] = N'Thab X52 Ph1'

insert into layout.Workbench([Name], ExternalReference, CellID, EOL, Routing, HourCapacity)
select N'Op 60', N'ER2_0903', ID, 1, 1, 95 from layout.Cell where [Name] = N'Thab X52 Ph1'
GO


print 'PartNumber'
GO
insert into layout.PartNumber(ID, PartNumber, [Description], Routing) values
	(1, N'12345678', N'Test partnumber #1', 3),
	(2, N'87654321', N'Test partnumber #2', 3),
	(3, N'12121212', N'Test partnumber #3', 2),
	(4, N'34343434', N'Test partnumber #4', 2),
	(5, N'56565656', N'Test partnumber #5', 2),
	(6, N'78787878', N'Test partnumber #6', 2),
	(7, N'12341234', N'Test partnumber #7', 1),
	(8, N'34563456', N'Test partnumber #8', 1),
	(9, N'56785678', N'Test partnumber #9', 1)
GO

