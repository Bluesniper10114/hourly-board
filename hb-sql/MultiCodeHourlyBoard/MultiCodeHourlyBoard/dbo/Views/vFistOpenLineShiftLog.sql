
CREATE VIEW [dbo].[vFistOpenLineShiftLog]
AS
	select sl.LocationID, slso.LineID, MIN(slso.ShiftLogID) ShiftLogID
	from dbo.ShiftLogSignOff slso
		inner join dbo.ShiftLog sl on slso.ShiftLogID = sl.ID
	where slso.SignedOffOperatorID is NULL
	group by sl.LocationID, slso.LineID