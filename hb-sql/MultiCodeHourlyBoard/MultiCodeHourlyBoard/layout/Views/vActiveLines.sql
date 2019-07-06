CREATE VIEW [layout].[vActiveLines]
AS
	select l.ID id, l.[Name] [name], l.[Description] [description], l.[Tags] tags, l.[TimeOut] [timeOut],
		l.LocationID locationID, loc.[Name] locationName, lc.capacity, lc.eolMachines
	from [layout].Line l
		left join [layout].[Location] loc on l.LocationID = loc.ID
		left join (
			select c.LineID,
				ROUND(SUM(w.HourCapacity) * 8 * 11 / 12, 0) capacity,
				COUNT(w.ID) eolMachines
			from layout.Cell c
				inner join layout.Workbench w on c.ID = w.CellID
			where w.EOL = 1
			group by c.LineID
		) lc on l.ID = lc.LineID
	where l.Deleted = 0