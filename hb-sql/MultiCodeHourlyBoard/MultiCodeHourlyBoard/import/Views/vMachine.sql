CREATE VIEW [import].[vMachine]
AS
	select m.Machine, m.MachineAlternative, m.[Server], w.[Location], w.Line, w.Cell, m.LastTimeStamp, m.ReadyForImport
	from [import].[Machine] m
		left join [layout].[vWorkbench] w on m.WorkbenchID = w.ID