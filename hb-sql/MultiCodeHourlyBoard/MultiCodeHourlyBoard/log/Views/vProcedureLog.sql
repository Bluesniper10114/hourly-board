CREATE VIEW [log].[vProcedureLog]
AS 
	SELECT pl.[TimeStamp], p.[Name], pl.[ProfileID], pl.CustomParams, pl.XMLParam,
		pl.[Message], pl.[ErrorID], pl.[DboError], pl.[DevError], pl.[ID]
	FROM [log].ProcedureLog pl
		inner join [log].[Procedure] p on pl.ProcedureID = p.ID