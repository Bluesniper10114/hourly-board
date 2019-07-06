CREATE FUNCTION [users].[TokenExpirationInSeconds]()
RETURNS INT
AS
BEGIN
	RETURN 36000; -- 10 hours	
END