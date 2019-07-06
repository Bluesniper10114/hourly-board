CREATE VIEW [import].[vActualsLogErrors]
AS
	select al.[Date], al.ShiftType, al.Machine, al.MachineAlternative, al.IsOK, ale.ErrorType, ale.ErrorDescription, ale.ActualsLogID
	from [import].[ActualsLogErrors] ale
		inner join [import].[ActualsLog] al on ale.ActualsLogID = al.ID