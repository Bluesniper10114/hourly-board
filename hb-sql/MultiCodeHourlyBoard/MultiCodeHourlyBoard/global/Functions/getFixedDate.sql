CREATE FUNCTION [global].[GetFixedDate]()
RETURNS DateTime AS 
BEGIN
	declare @midnight datetime
	declare @millisecondsToday bigint
	declare @now datetime = getdate()

	select @midnight = DATEADD(day, DATEDIFF(day, 0, @now), 0)
	select @millisecondsToday = DATEDIFF(MILLISECOND, @midnight, @now)
	return DATEADD(MILLISECOND, @millisecondsToday, '2019-02-01')
END