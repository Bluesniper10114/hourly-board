CREATE VIEW [target].[vHourly]
AS
	select h.[Hour], h.[Value] HourlyTarget, h.CumulativeValue HourlyCumulativeTarget,
		d.Billboard, l.[Name] Line, t.[Name] TargetType,
		sl.[Data] ShiftData, sl.DataStart ShiftDataStart, sl.ShiftType,
		DATEADD(HOUR, h.[Hour] - 1, sl.DataStart) HourStart,
		DATEADD(HOUR, h.[Hour], sl.DataStart) HourEnd, loc.[Name] [Location],
		h.ID, h.DailyID, d.ShiftLogID, d.LineID, d.TypeID, sl.LocationID,
		CONVERT(nchar(3), DATEADD(HOUR, h.[Hour] - 1, sl.DataStart), 108) + N'00 '
			+ CONVERT(nchar(3), DATEADD(HOUR, h.[Hour], sl.DataStart), 108) + N'00' HourInterval,
		h.UpdateDate, h.UpdateUserID
	from [target].[Hourly] h
		left join [target].[Daily] d on h.DailyID = d.ID
		left join dbo.ShiftLog sl on d.ShiftLogID = sl.ID
		left join [layout].Line l on d.LineID = l.ID
		left join [target].[Type] t on d.TypeID = t.ID
		left join [layout].[Location] loc on sl.LocationID = loc.ID
