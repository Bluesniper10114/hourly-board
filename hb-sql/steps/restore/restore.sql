-- Script to be run during restore

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

:r $(gitBaseFolder)\powerzeus\scripts\restore_database.sql

:r $(gitBaseFolder)\steps\restore\post-restore.sql




