-- ASSY initializations
USE MultiCodeBoard
GO

print 'ASSY dictionary'
GO
-- Billboard comments
insert into dbo.CommentsDictionary([Text]) values
	(N'Lipsa materiale/componente'),
	(N'Operatori invoiti'),
	(N'Operatori absenti'),
	(N'Operatori  in Rump-up'),
	(N'Oprire linie - probleme calitate'),
	(N'Oprire linie - scolarizare'),
	(N'Oprire linie - aspecte SSM'),
	(N'Oprire linie - lipsa energie'),
	(N'Oprire linie - sedinte/informari HR'),
	(N'Productie incetinita - probl. calitate materie prima'),
	(N'Intarziere transport operatori'),
	(N'Statie de lucru defecta'),
	(N'Teste'),
	(N'Schimbare rola imprimanta'),
	(N'Piking'),
	(N'Lipsa ambalaj client')
GO

-- Billboard escalated
insert into dbo.EscalatedDictionary([Text]) values
	(N'TL'),
	(N'PL'),
	(N'BUM'),
	(N'PQE'),
	(N'Logistica'),
	(N'IT'),
	(N'SQA'),
	(N'MENTENANTA'),
	(N'Q'),
	(N'ME'),
	(N'OPERATION  MANAGER')
GO

-- Billboard downtime
insert into dbo.DowntimeDictionary([Text]) values
	(N'Lipsa materiale/componente'),
	(N'Operatori invoiti'),
	(N'Operatori absenti'),
	(N'Operatori  in Rump-up'),
	(N'Oprire linie - probleme calitate'),
	(N'Oprire linie - scolarizare'),
	(N'Oprire linie - aspecte SSM'),
	(N'Oprire linie - lipsa energie'),
	(N'Oprire linie - sedinte/informari HR'),
	(N'Productie incetinita - probl. calitate materie prima'),
	(N'Intarziere transport operatori'),
	(N'Statie de lucru defecta'),
	(N'Teste'),
	(N'Schimbare rola imprimanta'),
	(N'Piking'),
	(N'Lipsa ambalaj client')
GO

-- layout
-- ASSY
print 'Line ASSY'
GO
-- line
insert into layout.Line([Name], LocationID) values
	(N'AUDI', 'TM'),
	(N'BMW', 'TM'),
	(N'MQB 1', 'TM'),
	(N'MQB 2', 'TM'),
	(N'DACIA MO', 'TM'),
	(N'FIAT', 'TM'),
	(N'EDISON', 'TM'),
	(N'BENTLEY', 'TM'),
	(N'LAMBORGHINI', 'TM'),
	(N'ROLLS', 'TM'),
	(N'GM', 'TM'),
	(N'PSA', 'TM'),
	(N'PORSCHE', 'TM'),
	(N'DAIMLER', 'TM'),
	(N'RENAULT', 'TM'),
	(N'LAND ROVER', 'TM')
GO

insert into layout.LineTag(LineID, Tag)
select ID, [Name]
from layout.Line
where ID not in (select LineID from layout.LineTag)
GO

insert into layout.LineTag(LineID, Tag)
select ID, N'MQB'
from layout.Line
where [Name] like 'MQB%'
GO


print 'Cell ASSY'
GO
-- cell
insert into layout.Cell([Name], LineID)
select N'AQ', ID from layout.Line where [Name] = N'AUDI'

insert into layout.Cell([Name], LineID)
select N'B9', ID from layout.Line where [Name] = N'AUDI'

insert into layout.Cell([Name], LineID)
select N'Q8', ID from layout.Line where [Name] = N'AUDI'

insert into layout.Cell([Name], LineID)
select N'G2X', ID from layout.Line where [Name] = N'BMW'

insert into layout.Cell([Name], LineID)
select N'BMW PL7', ID from layout.Line where [Name] = N'BMW'

insert into layout.Cell([Name], LineID)
select N'MQB 1', ID from layout.Line where [Name] = N'MQB 1'

insert into layout.Cell([Name], LineID)
select N'MQB 2', ID from layout.Line where [Name] = N'MQB 2'

insert into layout.Cell([Name], LineID)
select N'M0 (NOUL RENAULT)', ID from layout.Line where [Name] = N'DACIA MO'

insert into layout.Cell([Name], LineID)
select N'Fiat X250', ID from layout.Line where [Name] = N'FIAT'

insert into layout.Cell([Name], LineID)
select N'Fiat MCA', ID from layout.Line where [Name] = N'FIAT'

insert into layout.Cell([Name], LineID)
select N'Twingo', ID from layout.Line where [Name] = N'EDISON'

insert into layout.Cell([Name], LineID)
select N'Smart', ID from layout.Line where [Name] = N'EDISON'

insert into layout.Cell([Name], LineID)
select N'BENTLEY', ID from layout.Line where [Name] = N'BENTLEY'

insert into layout.Cell([Name], LineID)
select N'Lamborghini', ID from layout.Line where [Name] = N'LAMBORGHINI'

insert into layout.Cell([Name], LineID)
select N'Rolls Royce', ID from layout.Line where [Name] = N'ROLLS'

insert into layout.Cell([Name], LineID)
select N'GM', ID from layout.Line where [Name] = N'GM'

insert into layout.Cell([Name], LineID)
select N'PSA (CITROEN)', ID from layout.Line where [Name] = N'PSA'

insert into layout.Cell([Name], LineID)
select N'Lamborghini Urus', ID from layout.Line where [Name] = N'LAMBORGHINI'

insert into layout.Cell([Name], LineID)
select N'PORSCHE', ID from layout.Line where [Name] = N'PORSCHE'

insert into layout.Cell([Name], LineID)
select N'Daimler V4', ID from layout.Line where [Name] = N'DAIMLER'

insert into layout.Cell([Name], LineID)
select N'BJA', ID from layout.Line where [Name] = N'RENAULT'

insert into layout.Cell([Name], LineID)
select N'LAND ROVER', ID from layout.Line where [Name] = N'LAND ROVER'
GO

print 'Workbench ASSY'
GO
-- workbench
declare @id int

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0101', N'Bucsare', N'ER1_0101', ID, NULL, 0, 1 from layout.Cell where [Name] = N'AQ'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0102', N'EOL1', N'ER1_0102', ID, @id, 1, 1 from layout.Cell where [Name] = N'AQ'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0103', N'EOL2', N'ER1_0103', ID, @id, 1, 1 from layout.Cell where [Name] = N'AQ'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0104', N'EOL3', N'ER1_0104', ID, @id, 1, 1 from layout.Cell where [Name] = N'AQ'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0105', N'EOL4', N'ER1_0105', ID, @id, 1, 1 from layout.Cell where [Name] = N'AQ'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0106', N'EOL5', N'ER1_0106', ID, @id, 1, 1 from layout.Cell where [Name] = N'AQ'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0201', N'Bucsare', N'ER1_0201', ID, NULL, 0, 1 from layout.Cell where [Name] = N'B9'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0202', N'EOL1', N'ER1_0202', ID, @id, 1, 1 from layout.Cell where [Name] = N'B9'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0203', N'EOL2', N'ER1_0203', ID, @id, 1, 1 from layout.Cell where [Name] = N'B9'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0204', N'EOL3', N'ER1_0204', ID, @id, 1, 1 from layout.Cell where [Name] = N'B9'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0205', N'EOL4', N'ER1_0205', ID, @id, 1, 1 from layout.Cell where [Name] = N'B9'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0206', N'EOL5', N'ER1_0206', ID, @id, 1, 1 from layout.Cell where [Name] = N'B9'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0301', N'Bucsare', N'ER1_0301', ID, NULL, 0, 1 from layout.Cell where [Name] = N'Q8'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0302', N'EOL', N'ER1_0302', ID, @id, 1, 1 from layout.Cell where [Name] = N'Q8'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0401', N'Bucsare', N'ER1_0401', ID, NULL, 0, 1 from layout.Cell where [Name] = N'G2X'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0402', N'PreAssy1', N'ER1_0402', ID, @id, 0, 1 from layout.Cell where [Name] = N'G2X'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0403', N'Assy1', N'ER1_0403', ID, @id, 0, 1 from layout.Cell where [Name] = N'G2X'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0404', N'Assy2', N'ER1_0404', ID, @id, 0, 1 from layout.Cell where [Name] = N'G2X'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0405', N'EOL', N'ER1_0405', ID, @id, 0, 1 from layout.Cell where [Name] = N'G2X'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0501', N'Assy1', N'ER1_0501', ID, NULL, 0, 1 from layout.Cell where [Name] = N'BMW PL7'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0502', N'Assy2', N'ER1_0502', ID, @id, 0, 1 from layout.Cell where [Name] = N'BMW PL7'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0503', N'Assy3', N'ER1_0503', ID, @id, 0, 1 from layout.Cell where [Name] = N'BMW PL7'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0504', N'ASE', N'ER1_0504', ID, @id, 0, 1 from layout.Cell where [Name] = N'BMW PL7'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0505', N'PreAssy1', N'ER1_0505', ID, @id, 0, 1 from layout.Cell where [Name] = N'BMW PL7'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0506', N'PreAssy2', N'ER1_0506', ID, @id, 0, 1 from layout.Cell where [Name] = N'BMW PL7'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0507', N'EOL1', N'ER1_0507', ID, @id, 1, 1 from layout.Cell where [Name] = N'BMW PL7'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0508', N'EOL2', N'ER1_0508', ID, @id, 1, 1 from layout.Cell where [Name] = N'BMW PL7'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0601', N'PreAssy1', N'ER1_0601', ID, NULL, 0, 1 from layout.Cell where [Name] = N'MQB 1'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0602', N'Adaptori1', N'ER1_0602', ID, @id, 0, 1 from layout.Cell where [Name] = N'MQB 1'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0603', N'Milling1', N'ER1_0603', ID, @id, 0, 1 from layout.Cell where [Name] = N'MQB 1'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0604', N'5A', N'ER1_0604', ID, @id, 0, 1 from layout.Cell where [Name] = N'MQB 1'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0605', N'5B', N'ER1_0605', ID, @id, 0, 1 from layout.Cell where [Name] = N'MQB 1'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0606', N'EOL', N'ER1_0606', ID, @id, 1, 1 from layout.Cell where [Name] = N'MQB 1'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0701', N'PreAssy1', N'ER1_0701', ID, NULL, 0, 1 from layout.Cell where [Name] = N'MQB 2'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0702', N'Adaptori1', N'ER1_0702', ID, @id, 0, 1 from layout.Cell where [Name] = N'MQB 2'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0703', N'Milling1', N'ER1_0703', ID, @id, 0, 1 from layout.Cell where [Name] = N'MQB 2'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0704', N'5A', N'ER1_0704', ID, @id, 0, 1 from layout.Cell where [Name] = N'MQB 2'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0705', N'5B', N'ER1_0705', ID, @id, 0, 1 from layout.Cell where [Name] = N'MQB 2'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0706', N'EOL', N'ER1_0706', ID, @id, 1, 1 from layout.Cell where [Name] = N'MQB 2'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0801', N'PreAssy1', N'ER1_0801', ID, NULL, 0, 1 from layout.Cell where [Name] = N'M0 (NOUL RENAULT)'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0802', N'PreAssy2', N'ER1_0802', ID, @id, 0, 1 from layout.Cell where [Name] = N'M0 (NOUL RENAULT)'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0803', N'PreAssy3', N'ER1_0803', ID, @id, 0, 1 from layout.Cell where [Name] = N'M0 (NOUL RENAULT)'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0804', N'EOL', N'ER1_0804', ID, @id, 1, 1 from layout.Cell where [Name] = N'M0 (NOUL RENAULT)'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0805', N'EOL', N'ER1_0805', ID, @id, 1, 1 from layout.Cell where [Name] = N'M0 (NOUL RENAULT)'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0806', N'EOL', N'ER1_0806', ID, @id, 1, 1 from layout.Cell where [Name] = N'M0 (NOUL RENAULT)'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0901', N'AssyPur', N'ER1_0901', ID, NULL, 0, 1 from layout.Cell where [Name] = N'Fiat X250'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0902', N'PreAssy', N'ER1_0902', ID, @id, 0, 1 from layout.Cell where [Name] = N'Fiat X250'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB0903', N'EOL', N'ER1_0903', ID, @id, 1, 1 from layout.Cell where [Name] = N'Fiat X250'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1001', N'PreAssy1', N'ER1_1001', ID, NULL, 0, 1 from layout.Cell where [Name] = N'Fiat MCA'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1002', N'EOL', N'ER1_1002', ID, @id, 1, 1 from layout.Cell where [Name] = N'Fiat MCA'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1101', N'Claxon', N'ER1_1101', ID, NULL, 0, 1 from layout.Cell where [Name] = N'Twingo'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1102', N'EOL1', N'ER1_1102', ID, @id, 1, 1 from layout.Cell where [Name] = N'Twingo'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1103', N'EOL2', N'ER1_1103', ID, @id, 1, 1 from layout.Cell where [Name] = N'Twingo'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1201', N'Claxon', N'ER1_1201', ID, NULL, 0, 1 from layout.Cell where [Name] = N'Smart'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1202', N'EOL1', N'ER1_1202', ID, @id, 1, 1 from layout.Cell where [Name] = N'Smart'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1203', N'EOL2', N'ER1_1203', ID, @id, 1, 1 from layout.Cell where [Name] = N'Twingo'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1301', N'EOL', N'ER1_1301', ID, NULL, 1, 1 from layout.Cell where [Name] = N'Bentley'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1401', N'EOL', N'ER1_1401', ID, NULL, 1, 1 from layout.Cell where [Name] = N'Lamborghini'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1501', N'Assy1', N'ER1_1501', ID, NULL, 0, 1 from layout.Cell where [Name] = N'Rolls Royce'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1502', N'EOL', N'ER1_1502', ID, @id, 1, 1 from layout.Cell where [Name] = N'Rolls Royce'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1601', N'EOL', N'ER1_1601', ID, NULL, 1, 1 from layout.Cell where [Name] = N'GM'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1701', N'Assy1', N'ER1_1701', ID, NULL, 0, 1 from layout.Cell where [Name] = N'PSA (CITROEN)'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1702', N'Assy2', N'ER1_1702', ID, @id, 0, 1 from layout.Cell where [Name] = N'PSA (CITROEN)'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1703', N'EOL', N'ER1_1703', ID, @id, 1, 1 from layout.Cell where [Name] = N'PSA (CITROEN)'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1801', N'EOL', N'ER1_1801', ID, NULL, 1, 1 from layout.Cell where [Name] = N'Lamborghini Urus'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1901', N'AssyAdaptori', N'ER1_1901', ID, NULL, 0, 1 from layout.Cell where [Name] = N'PORSCHE'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1902', N'SuduraUltrasonica', N'ER1_1902', ID, @id, 0, 1 from layout.Cell where [Name] = N'PORSCHE'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1903', N'AssyBrackets', N'ER1_1903', ID, @id, 0, 1 from layout.Cell where [Name] = N'PORSCHE'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1904', N'AssyMufuSiBezel', N'ER1_1904', ID, @id, 0, 1 from layout.Cell where [Name] = N'PORSCHE'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1905', N'AssyBCover', N'ER1_1905', ID, @id, 0, 1 from layout.Cell where [Name] = N'PORSCHE'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1906', N'Milling', N'ER1_1906', ID, @id, 0, 1 from layout.Cell where [Name] = N'PORSCHE'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1907', N'AssyEbox', N'ER1_1907', ID, @id, 0, 1 from layout.Cell where [Name] = N'PORSCHE'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1908', N'AssyPadele', N'ER1_1908', ID, @id, 0, 1 from layout.Cell where [Name] = N'PORSCHE'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1909', N'AssyContactUnit', N'ER1_1909', ID, @id, 0, 1 from layout.Cell where [Name] = N'PORSCHE'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB1910', N'EOL', N'ER1_1910', ID, @id, 1, 1 from layout.Cell where [Name] = N'PORSCHE'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2001', N'PreAssy1', N'ER1_2001', ID, NULL, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2002', N'PreAssy2', N'ER1_2002', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2003', N'Assy1', N'ER1_2003', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2004', N'Assy2', N'ER1_2004', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2005', N'Assy3', N'ER1_2005', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2006', N'Assy4', N'ER1_2006', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2007', N'Assy5', N'ER1_2007', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2008', N'Assy6', N'ER1_2008', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2009', N'Assy7', N'ER1_2009', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2010', N'Assy8', N'ER1_2010', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2011', N'GAP1', N'ER1_2011', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2012', N'GAP2', N'ER1_2012', ID, @id, 0, 1 from layout.Cell where [Name] = N'Daimler V4'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2013', N'EOL1', N'ER1_2013', ID, @id, 1, 1 from layout.Cell where [Name] = N'Daimler V4'
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2014', N'EOL2', N'ER1_2011', ID, @id, 1, 1 from layout.Cell where [Name] = N'Daimler V4'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2101', N'Assy1', N'ER1_2101', ID, NULL, 0, 1 from layout.Cell where [Name] = N'BJA'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2102', N'Assy2', N'ER1_2102', ID, @id, 0, 1 from layout.Cell where [Name] = N'BJA'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2103', N'EOL', N'ER1_2103', ID, @id, 1, 1 from layout.Cell where [Name] = N'BJA'

insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2201', N'Assy1', N'ER1_2201', ID, NULL, 0, 1 from layout.Cell where [Name] = N'LAND ROVER'
select @id = SCOPE_IDENTITY()
insert into layout.Workbench([Name], [Description], ExternalReference, CellID, PreviousWorkbenchID, EOL, Routing)
select N'WB2202', N'EOL', N'ER1_2202', ID, @id, 1, 1 from layout.Cell where [Name] = N'LAND ROVER'
GO
