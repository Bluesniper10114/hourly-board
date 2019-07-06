CREATE FUNCTION [log].[GetProcedureID]
(
	@name nvarchar(max)
)
RETURNS tinyint
AS
BEGIN
	declare @id tinyint;

	select @id = ID from [log].[Procedure] p
	where p.Name = @name

	return @id
END