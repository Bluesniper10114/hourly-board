CREATE VIEW [dbo].[vActualsLog]
AS
	select al.[Date], al.IsOK, w.EOL, h.[Hour],
		al.WorkbenchID, w.LineID, al.ShiftLogID, d.ID DailyID, h.ID HourlyID, al.ID
	from dbo.ActualsLog al
		left join layout.vWorkbench w on al.WorkbenchID = w.ID
		left join [target].[Daily] d on w.LineID = d.LineID and al.ShiftLogID = d.ShiftLogID and d.Billboard = 1
		left join [target].[vHourly] h on d.ID = h.DailyID and al.[Date] >= h.HourStart and al.[Date] < h.HourEnd
