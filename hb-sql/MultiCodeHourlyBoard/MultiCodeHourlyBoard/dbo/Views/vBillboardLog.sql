CREATE VIEW [dbo].[vBillboardLog]
AS
	select bl.HourInterval, h.[Hour], h.HourStart, h.HourEnd,
		h.[HourlyTarget] [Target], h.HourlyCumulativeTarget CumulativeTarget,
		bl.ActualAchieved, bl.CumulativeAchieved,
		bl.Defects, bl.Downtime, bl.Comment, bl.Escalated,
		o.Barcode SignedOffOperatorBarcode, o.FirstName + N' ' + o.LastName SignedOffOperator,
		sl.[Data] ShiftData, sl.DataStart ShiftDataStart, l.[Name] Line, loc.[Name] [Location] ,
		bl.TargetHourlyID, h.DailyID TargetDailyID, bl.SignedOffOperatorID,
		sl.ID ShiftLogID, sl.ShiftType, d.LineID
	from dbo.BillboardLog bl
		left join [target].[vHourly] h on bl.TargetHourlyID = h.ID
		left join [target].[Daily] d on h.DailyID = d.ID
		left join [dbo].[ShiftLog] sl on d.ShiftLogID = sl.ID
		left join [layout].[Line] l on d.LineID = l.ID
		left join [layout].[Location] loc on sl.LocationID = loc.ID
		left join [users].[Operator] o on bl.SignedOffOperatorID = o.ID
