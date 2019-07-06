<#
    Deployment script for MultiCode

    usage: deploy.ps1 -target (debug/release) -skipVersion:$false

    -target: one of several options:
            debug           - deploys to a multicodelw debug server using the corresponding SQL server instance from the settings file
            release         - deploys to a live multicode server using settings from the settings file
#>

<#
    Usage example:
    .\deploy.ps1 -target debug
    .\deploy.ps1 -target debug -skipVersion:$true
#>

<#
    There are three configuration files:
    1. LocalSettings.xml
        * has all the settings regarding the deployment: server, database, restore information, email information
    2. Settings.xml
        * has version information for current version (build number, version, version release notes)

    * Target:
        Debug or Release, makes sure the database server acts like a live or like a test server preventing irreversible errors

    * GitBaseFolder
        The git folder where the SQL project is hosted. It must be the base folder.
        All scripts are run relative to this base.

    * DatabaseServer:
        The server to which we are deploying

    * BackupFolder:
        Folder where the database backups are stored.
        WARNING: Do not put this in git!!!

    * BackupFile:
        The name of the backup file with extension in the backup folder
#>

param (
  [ValidateSet('debug', 'release')]
  [Parameter(Mandatory=$true)] [string]$target,
  [Parameter(Mandatory=$true)] [switch]$apply = $false,
  [Parameter(Mandatory=$false)] [switch]$skipVersion = $false,
  [Parameter(Mandatory=$false)] [switch]$fast = $false,
  [ValidateSet('y', 'n', '?')]
  [Parameter(Mandatory=$false)] [string]$automaticConfirmation = '?'
  )

$ScriptDirectory = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent

. "$ScriptDirectory\..\Utils\SqlUtilities.ps1"
. "$ScriptDirectory\..\Utils\ListUtilities.ps1"
. "$ScriptDirectory\..\Utils\EmailUtilities.ps1"
. "$ScriptDirectory\..\Utils\StringUtilities.ps1"
. "$ScriptDirectory\..\Utils\ConsoleUtilities.ps1"
. "$ScriptDirectory\..\Utils\SettingsUtilities.ps1"

"##########################______DEPLOYMENT_SCRIPT_____################"
""
Push-Location
$localSettingsFile = ".\LocalSettings.xml";
$settingsFile = ".\Settings.xml";

# ==== SIMULATE
if (($apply -eq $false) -and ($skipVersion -eq $false)) {
    'This is a simulation, a version change will not be recorded in the database (even though $skipVersion was set to $false)'
    $skipVersion = $true
}

$sleepMilliseconds = 500;
if ($fast -eq $true)
{
    $sleepMilliseconds = 0;
}

# ===== LOCAL SETTINGS

# Import settings from config file
$loadBackupInformation = $false
$localSettings = LoadGeneralSettings $localSettingsFile $loadBackupInformation
$targetSettings = LoadTargetSettings $localSettingsFile $target $loadBackupInformation
$versionSettings = LoadVersionSettings $settingsFile $localSettings.GitBaseFolder

if ($apply -eq $false)
{
    $sendEmail = $false;
}
else
{
    $sendEmail = [System.Convert]::ToBoolean($targetSettings.EmailSend)
}

# ====== SETTINGS.XML

# Import version and version description settings from config file
$currentBuildNumber = GetBuildNumberOfSqlServer $targetSettings.DatabaseServer $targetSettings.DatabaseName

$showBackupRestore = $false
ShowSettingsForTarget $localSettings $targetSettings $versionSettings $currentBuildNumber $showBackupRestore $verbose

if ($sendEmail)
{
    WriteWarning "I will be sending e-mail to notify all people involved"
}

if (($versionSettings.BuildNumber -ile $currentBuildNumber))
{
    WriteError "Nothing to update. Script version is old"
    exit(-1)
}

if ($apply -eq $false)
{
    WriteWarning "This is just a SIMULATION. I'll be checking if the scripts exist"
}
else
{
    WriteWarning "Are you sure you want to deploy?"
    ContinueOnYes "If you are unsure, run again with -simulate:true to test this script" $automaticConfirmation
}

$emailData = @{
    Subject = "$($localSettings.SubjectBefore) (v$($versionSettings.FullVersion)-$($versionSettings.BuildNumber))"
    Title = $localSettings.SubjectBefore
    FromEmail = $targetSettings.EmailFrom
    ToEmail = $targetSettings.EmailTo
    Audience = $targetSettings.EmailAudienceText
    Details = $localSettings.DetailsBefore
    ReleaseNotes = $versionSettings.ReleaseNotes
    CallToAction = $localSettings.CallToActionBefore
    Deadlines = $localSettings.DeadlinesBefore
    DateTime = Get-Date
};
# ====== DO THE DEPLOYMENT
Try
{

    $startDate = Get-Date
    Write-Output ""
    Write-Output "Starting script execution... ($startDate)"
    Write-Output "Version $($versionSettings.BuildNumber) - $($versionSettings.FullVersion)"
    Write-Output $versionSettings.Description


    if ($skipVersion -eq $false)
    {
        OpenBuildNumberOnSqlServer $versionSettings $targetSettings.DatabaseServer $targetSettings.DatabaseName
    }

    $emailBody = FillEmailTemplate $targetSettings.EmailTemplate $emailData
    $emailBody > "$($targetSettings.HtmlEmailTemplateFullPath).start.html"

    if ($sendEmail)
    {
        SendEmailFromTemplate $targetSettings.EmailTemplate $emailData $targetSettings.EmailServer
    }

    if ($apply -eq $false)
    {
        ListQueriesInXmlFile $settingsFile $localSettings.GitBaseFolder $sleepMilliseconds;
    }
    else
    {
        ExecuteQueriesInXmlFile $settingsFile $localSettings.GitBaseFolder $targetSettings.DatabaseServer $targetSettings.DatabaseName $sleepMilliseconds;
    }

    '...Ending script execution: '
    if ($skipVersion -eq $false)
    {
        CloseBuildNumberOnSqlServer $versionSettings $targetSettings.DatabaseServer $targetSettings.DatabaseName
	}

    $endDate = Get-Date
    $emailData.Subject = "$($localSettings.SubjectAfter) (v$($versionSettings.FullVersion)-$($versionSettings.BuildNumber))"
    $emailData.Title = $localSettings.SubjectAfter
    $emailData.Details = $localSettings.DetailsAfter
    $emailData.CallToAction = $localSettings.CallToActionAfter
    $emailData.Deadlines = $localSettings.DeadlinesAfter
    $emailData.DateTime = Get-Date

    $emailBody = FillEmailTemplate $targetSettings.EmailTemplate $emailData
    $emailBody > "$($targetSettings.HtmlEmailTemplateFullPath).end.html"

    if ($sendEmail)
    {
        SendEmailFromTemplate $targetSettings.EmailTemplate $emailData $targetSettings.EmailServer
    }
}
Catch
{
    $ErrorMessage = $_.Exception.Message
    Write-Output "Error in script version: v$($versionSettings.FullVersion)-$($versionSettings.BuildNumber)"
    Write-Output 'Error message: ' + $ErrorMessage

    Write-Host ($emailData | Format-Table | Out-String)

    if ($sendEmail)
    {
        $emailData.Subject = $localSettings.SubjectOnError
        $emailData.Title =  $ErrorMessage
        $emailData.DateTime = Get-Date

        SendEmailFromTemplate $targetSettings.EmailTemplate $emailData $targetSettings.EmailServer
    }
    Pop-Location
    exit (-1);
}

Pop-Location
"Finished at: $endDate"

exit 0;

