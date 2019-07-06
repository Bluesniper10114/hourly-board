CREATE VIEW [report].[vDowntimeReason]
AS
	select h.LineID, h.ShiftData, DAY(h.ShiftData) [Day], h.ShiftType, 
		 dd.Comment Reason, SUM(dd.Duration) Duration
	from dbo.DowntimeDetails dd
		inner join dbo.Downtime d on dd.DowntimeID = d.ID
		inner join [target].vHourly h on d.TargetHourlyID = h.ID
	group by h.LineID, h.ShiftData, h.ShiftType, dd.Comment

