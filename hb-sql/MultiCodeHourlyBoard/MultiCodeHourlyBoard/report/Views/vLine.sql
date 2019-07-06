CREATE VIEW [report].[vLine]
AS
	select ID LineID, case Deleted when 1 then N'[del]' + [Name] else [Name] end LineName, Deleted
	from [layout].[Line]
	
