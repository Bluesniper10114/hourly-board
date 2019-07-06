CREATE VIEW [report].[vActualsVsTargetAndNotOK]
AS
	select LineID, ShiftData, DAY(ShiftData) [Day], ShiftType,
		MAX(CumulativeTarget) [Target],
		MAX(CumulativeAchieved) Achieved,
		SUM(Defects) NOK
	from dbo.vBillboardLog
	group by LineID, ShiftData, ShiftType
