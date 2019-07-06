CREATE FUNCTION [global].[GetDate]()
RETURNS [datetime]
AS
BEGIN
	return global.getDateProxy()
END