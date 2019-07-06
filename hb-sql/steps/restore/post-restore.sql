print 'check if user hboard-impex exists (password: 12345678)'
GO

USE [master]
GO

/****** Object:  Login [hboard-impex]    Script Date: 1/14/2019 11:20:54 AM ******/
IF  EXISTS (SELECT * FROM sys.server_principals WHERE name = N'hboard-impex')
DROP LOGIN [hboard-impex]
GO

/* For security reasons the login is created disabled and with a random password. */
/****** Object:  Login [hboard-impex]    Script Date: 1/14/2019 11:20:54 AM ******/
IF NOT EXISTS (SELECT * FROM sys.server_principals WHERE name = N'hboard-impex')
CREATE LOGIN [hboard-impex] WITH PASSWORD=N'GAE+/aRSInH4XP32/OXftU2deMoIjaZCPvV5X6kgB5E=',
DEFAULT_DATABASE=$(databaseName), DEFAULT_LANGUAGE=[us_english], CHECK_EXPIRATION=OFF, CHECK_POLICY=OFF
GO

USE $(databaseName)
GO
ALTER USER [hboard-impex] WITH LOGIN = [hboard-impex]
go


print 'check if user hboard-assy exists (password: 12345678)'
GO

/****** Object:  Login [hboard-assy]    Script Date: 1/14/2019 11:21:02 AM ******/
IF  EXISTS (SELECT * FROM sys.server_principals WHERE name = N'hboard-assy')
DROP LOGIN [hboard-assy]
GO

/* For security reasons the login is created disabled and with a random password. */
/****** Object:  Login [hboard-assy]    Script Date: 1/14/2019 11:21:02 AM ******/
/* password is 12345678 */
IF NOT EXISTS (SELECT * FROM sys.server_principals WHERE name = N'hboard-assy')
CREATE LOGIN [hboard-assy] WITH PASSWORD=N'zlh9jUuTDJBa/ZMytSNas+iDAkTDoe6plYiJbWqu480=',
DEFAULT_DATABASE=$(databaseName), DEFAULT_LANGUAGE=[us_english], CHECK_EXPIRATION=OFF, CHECK_POLICY=OFF
GO

USE $(databaseName)
GO
ALTER USER [hboard-assy] WITH LOGIN = [hboard-assy]
go