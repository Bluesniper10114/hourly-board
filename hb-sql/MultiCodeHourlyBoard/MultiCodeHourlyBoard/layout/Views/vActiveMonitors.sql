CREATE VIEW [layout].[vActiveMonitors]
AS
	select m.ID id, m.[Location] [location], m.[Description] [description], m.IPAddress ipAddress,
		m.LocationID locationID, loc.[Name] locationName, m.LineID lineID, l.[Name] lineName
	from [layout].[Monitor] m
		left join [layout].Line l on m.LineID = l.ID
		left join [layout].[Location] loc on m.LocationID = loc.ID
	where m.Deleted = 0
