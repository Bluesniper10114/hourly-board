CREATE VIEW [target].[vDaily]
AS
	select sl.[Data], d.[Value], t.[Name] TargetType,
		d.Billboard,  l.[Name] Line, l.Tags,
		sl.DataStart ShiftDataStart, sl.ShiftName, sl.ShiftType,
		d.ID, d.LineID, d.ShiftLogID, d.TypeID, l.LocationID, xl.[Name] LocationName, 
		d.UpdateDate, d.UpdateUserID
	from [target].[Daily] d
		left join layout.Line l on d.LineID = l.ID
		left join dbo.vShiftLog sl on d.ShiftLogID = sl.ID
		left join [target].[Type] t on d.TypeID = t.ID
		left join layout.[Location] xl on sl.LocationID = xl.ID
