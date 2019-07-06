CREATE VIEW [dbo].[vShiftLog]
AS
	select sl.ID, sl.[Data], sl.DataStart, slp.DataStart DataEnd,
		N'Schimb ' + sl.ShiftType + N' [' + CONVERT(nchar(5), sl.DataStart, 108) + N' - ' + CONVERT(nchar(5), slp.DataStart, 108) + N']' ShiftName,
		sl.ShiftType, sl.LocationID, l.[Name] [LocationName]
	from dbo.ShiftLog slp
		left join dbo.ShiftLog sl on slp.PreviousShiftLogID = sl.ID
		left join layout.[Location] l on slp.LocationID = l.ID
