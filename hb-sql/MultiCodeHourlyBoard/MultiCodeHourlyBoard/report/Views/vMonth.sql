CREATE VIEW [report].[vMonth]
AS
	select CONVERT(varchar(7), h.ShiftData, 121) [Month],
		MIN(h.ShiftData) DataStart,
		MAX(h.ShiftData) DataEnd
	from dbo.BillboardLog bl
		inner join [target].vHourly h on bl.TargetHourlyID = h.ID
	group by CONVERT(varchar(7), h.ShiftData, 121)
