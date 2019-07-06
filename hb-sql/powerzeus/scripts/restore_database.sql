USE [master]

IF EXISTS(select * from sys.databases where name='$(DatabaseName)')
BEGIN 
	ALTER DATABASE $(DatabaseName) SET SINGLE_USER WITH ROLLBACK IMMEDIATE

	DROP DATABASE  $(DatabaseName) 
END

IF ($(map) = 1)
BEGIN
	EXEC XP_CMDSHELL 'net use H: $(BackUpFolder)'

	RESTORE DATABASE $(DatabaseName) 
	FROM  DISK = N'H:\$(FileNameWithoutPath)' WITH  FILE = 1,  
	MOVE N'$(DBFileName)' TO N'$(RestoreLocation)\$(DatabaseName).mdf',  
	MOVE N'$(DBFileName)_log' TO N'$(RestoreLocation)\$(DatabaseName)_log.ldf',  NOUNLOAD,  STATS = 5

	ALTER DATABASE $(DatabaseName) SET MULTI_USER

	EXEC XP_CMDSHELL 'net use H: /delete'
END
ELSE
BEGIN
	RESTORE DATABASE $(DatabaseName) 
	FROM  DISK = N'$(FullPathFileName)' WITH  FILE = 1,  
	MOVE N'$(DBFileName)' TO N'$(RestoreLocation)\$(DatabaseName).mdf',  
	MOVE N'$(DBFileName)_log' TO N'$(RestoreLocation)\$(DatabaseName)_log.ldf',  NOUNLOAD,  STATS = 5

	ALTER DATABASE $(DatabaseName) SET MULTI_USER
END
GO


