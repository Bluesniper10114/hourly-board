-- Script to be run post-deployment

/*
	Parameters passed to this script are:

	"fileName=$($targetSettings.BackupFileFullPath)",
	"databaseName=$($targetSettings.DatabaseName)",
	"DBFileName=$($targetSettings.DbFileName)",
	"gitBaseFolder=$($generalSettings.GitBaseFolder)",
	"restoreLocation=$($targetSettings.RestoreLocation)",
	"cleanDB=$cleanDB",
	"DefaultDataPath=$($targetSettings.RestoreLocation)",
	"DefaultLogPath$($targetSettings.RestoreLocation)",

	You can use these parameters in your script like so:
	$(fileName)
*/

print 'Create Schema utility'
GO

IF EXISTS (SELECT name FROM sys.schemas WHERE name = N'utility')
	BEGIN
		PRINT 'Dropping the utility schema'
		DROP SCHEMA [utility]
	END
GO
PRINT 'Creating the utility schema'
GO
CREATE SCHEMA [utility] AUTHORIZATION [dbo]
GO

print '#01: create ResetDB stored procedure for tests'
print '     running /steps/post-deployment/spUtility_ResetDB.sql'
GO
:r $(gitBaseFolder)"/steps/post-deployment/spUtility_ResetDB.sql"

print '#02: preparing the database for testing'
print '     running /steps/post-deployment/prep-for-testing.sql'
GO
:r $(gitBaseFolder)"/steps/post-deployment/prep-for-testing.sql"

print '#03: Clear live data'
print '     NOT running /MultiCodeHourlyBoard/MultiCodeHourlyBoard/reset-live-data.sql'
GO
--:r $(gitBaseFolder)"/MultiCodeHourlyBoard/MultiCodeHourlyBoard/reset-live-data.sql"

print '#04: Add dictionary tables'
print '     running /MultiCodeHourlyBoard/MultiCodeHourlyBoard/Script.PostDeployment.sql'
GO
:setvar relativeProjectPath /MultiCodeHourlyBoard/MultiCodeHourlyBoard
:r $(gitBaseFolder)"/MultiCodeHourlyBoard/MultiCodeHourlyBoard/Script.PostDeployment.sql"
