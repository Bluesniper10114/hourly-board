#usage example 1: .\restore.ps1                             => does not clean the database
#usage example 2: .\restore.ps1 -cleanDB:$true              => cleans the database, all data tables will be empty
#usage example 3: .\restore.ps1 -restoreOnly:$true          => only restores the database, exactly as on the live server

param (
    [Parameter(Mandatory=$false)] [switch] $restoreOnly = $false,
    [Parameter(Mandatory=$false)] [switch] $skipRestore = $false,
    [ValidateSet('debug')]
    [Parameter(Mandatory=$true)] [string] $target = $null,
    [ValidateSet('y', 'n', '?')]
    [Parameter(Mandatory=$false)] [string]$automaticConfirmation = '?'
)

$ScriptDirectory = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent

. "$ScriptDirectory\..\Utils\SqlUtilities.ps1"
. "$ScriptDirectory\..\Utils\ConsoleUtilities.ps1"
. "$ScriptDirectory\..\Utils\SettingsUtilities.ps1"

"##########################______RESTORE____SCRIPT_____################"
$localSettingsFile = ".\LocalSettings.xml";
$settingsFile = ".\Settings.xml";

$loadBackupRestoreInformation = $true
$generalSettings = LoadGeneralSettings $localSettingsFile $loadBackupRestoreInformation
$targetSettings = LoadTargetSettings $localSettingsFile $target $loadBackupRestoreInformation
$versionSettings = LoadVersionSettings $settingsFile $generalSettings.GitBaseFolder

if ($null -eq $targetSettings.DatabaseServer)
{
	'.\restore.ps1 -restoreOnly:$false'
    exit(-1)
}

$currentBuildNumber = GetBuildNumberOfSqlServer $targetSettings.DatabaseServer $targetSettings.DatabaseName

$showBackupRestoreInformation = $true
ShowSettingsForTarget $generalSettings $targetSettings $versionSettings $currentBuildNumber $showBackupRestoreInformation $verbose

ContinueOnYes "Are you sure you want to restore?" $automaticConfirmation

"Started at: $(Get-Date)"
# remember where we stared
Push-Location

$map = 0;
if ($generalSettings.MapNetwork -eq $true)
{
	$map = 1;
}

try
{
    if (-not $skipRestore)
    {
		'restoring... (this can take a minute or two)'
		$parameters =
			"FullPathFileName=$($targetSettings.BackupFileFullPath)",
			"databaseName=$($targetSettings.DatabaseName)",
			"DBFileName=$($targetSettings.DbFileName)",
			"gitBaseFolder=$($generalSettings.GitBaseFolder)",
			"Map = $map",
			"restoreLocation=$($targetSettings.RestoreLocation)",
			"FileNameWithoutPAth=$($targetSettings.BackupFile)",
			"BackupFolder=$($targetSettings.BackupFolder)";
		Invoke-Sqlcmd -ServerInstance $targetSettings.DatabaseServer -Verbose -AbortOnError -QueryTimeout 0 -InputFile $versionSettings.RestoreScriptFullPath -OutputSqlErrors $true -Variable $parameters
	}
}
catch
{
    $ErrorMessage = $_.Exception.Message;
    $FailedItem = $_.Exception.ItemName;

	Pop-Location
    Write-Error "$ErrorMessage - $FailedItem";
    exit (-1);
}

# go back to file system, in case we are in the sql server system provided by powershell
Pop-Location
"Finished at: $(Get-Date)"

exit 0;
