CREATE VIEW [layout].[vActivePartNumbers]
AS
	select PartNumber, [Description], Routing
	from [layout].PartNumber
	where Deleted = 0
