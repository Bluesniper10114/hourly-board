use $(DatabaseName)
go
--ALTER USER NewLW WITH LOGIN = NEWLW
--go

USE $(DatabaseName)
GO
EXEC dbo.sp_changedbowner @loginame = N'sa', @map = false
GO


