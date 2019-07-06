
CREATE VIEW [layout].[vWorkbench]
AS
	select w.[Name], w.[Description], lo.[Name] [Location], l.[Name] Line, c.[Name] Cell, w.ExternalReference,
		w.HourCapacity, w.Routing , ISNULL(w.[TimeOut], ISNULL(c.[TimeOut], l.[TimeOut])) [TimeOut], w.EOL,
		c.LineID, w.CellID, w.ID
	from [layout].[Workbench] w
		inner join [layout].[Cell] c on w.CellID = c.ID
		inner join [layout].[Line] l on c.LineID = l.ID
		inner join [layout].[Location] lo on l.LocationID = lo.ID