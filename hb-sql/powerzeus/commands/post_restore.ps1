param (
    [ValidateSet('debug')]
    [Parameter(Mandatory=$true)] [string]$target = $null,
    [ValidateSet('y', 'n', '?')]
    [Parameter(Mandatory=$false)] [string]$automaticConfirmation = '?'
)

$ScriptDirectory = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent

. "$ScriptDirectory\..\Utils\SqlUtilities.ps1"
. "$ScriptDirectory\..\Utils\ConsoleUtilities.ps1"
. "$ScriptDirectory\..\Utils\SettingsUtilities.ps1"

$localSettingsFile = ".\LocalSettings.xml";
$settingsFile = ".\Settings.xml";
"##########################______POST_RESTORE____SCRIPT_____################"

$generalSettings = LoadGeneralSettings $localSettingsFile
$targetSettings = LoadTargetSettings $localSettingsFile $target
$versionSettings = LoadVersionSettings $settingsFile $generalSettings.GitBaseFolder

ContinueOnYes "This script adds support functions for testing (post-restore). Are you sure you want to run the post-restore script?" $automaticConfirmation

"Started post restore at: $(Get-Date))"
# remember where we stared
Push-Location

$cleanDB = $false
'Running post-restore scripts'
$parameters =
	"fileName=$($targetSettings.BackupFileFullPath)",
	"databaseName=$($targetSettings.DatabaseName)",
	"DBFileName=$($targetSettings.DbFileName)",
	"gitBaseFolder=$($generalSettings.GitBaseFolder)",
	"restoreLocation=$($targetSettings.RestoreLocation)",
	"DefaultDataPath=$($targetSettings.RestoreLocation)",
	"DefaultLogPath=$($targetSettings.RestoreLocation)",
	"cleanDB=$cleanDB"
Invoke-Sqlcmd -ServerInstance $targetSettings.DatabaseServer -Database $targetSettings.DatabaseName -Verbose -AbortOnError -InputFile $versionSettings.PostRestoreScriptFullPath -OutputSqlErrors $true -Variable $parameters

# go back to file system, in case we are in the sql server system provided by powershell
Pop-Location
"Finished at: $(Get-Date)"

