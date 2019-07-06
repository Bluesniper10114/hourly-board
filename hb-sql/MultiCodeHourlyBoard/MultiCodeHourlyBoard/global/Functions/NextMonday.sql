CREATE FUNCTION [global].[NextMonday](@Date datetime)
RETURNS datetime
AS
BEGIN
	
	declare @rez datetime,
			@dateFirst int = @@DATEFIRST,
			@datePart int = DATEPART(DW, @Date)

	if @dateFirst = 7
		set @rez = DATEADD(day, DATEDIFF(day, 0, @Date), case @datePart when 1 then 1 else 9 - @datePart end)
	else if @dateFirst = 1
		set @rez = DATEADD(day, DATEDIFF(day, 0, @Date), 8 - @datePart)

	return @rez
END