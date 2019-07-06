
function LoadGeneralSettings([string] $settingsFilePath, [bool] $loadBackupRestoreInformation = $true)
{
    $localSettingsFullPath = Resolve-Path $settingsFilePath;

    [xml]$ConfigFile = Get-Content $localSettingsFullPath

    $gitBaseFolder = $ConfigFile.Settings.GitBaseFolder;

    if ($loadBackupRestoreInformation)
    {
		$BackupFolder = $ConfigFile.Settings.BackupFolder;
		$MapNetwork = $ConfigFile.Settings.MapNetwork;
    }
    else {
        $BackupFolder = ""
    }

    $generalSettings = @{
        LocalSettingsFullPath = $localSettingsFullPath
        GitBaseFolder = $gitBaseFolder
		MapNetwork = $MapNetwork
        BackupFolder = $BackupFolder

        SubjectBefore = $ConfigFile.Settings.Email.SubjectBefore
        SubjectAfter = $ConfigFile.Settings.Email.SubjectAfter
        SubjectOnError = $ConfigFile.Settings.Email.SubjectOnError
        DetailsBefore = $ConfigFile.Settings.Email.DetailsBefore
        DetailsAfter = $ConfigFile.Settings.Email.DetailsAfter
        CallToActionBefore = $ConfigFile.Settings.Email.CallToActionBefore
        CallToActionAfter = $ConfigFile.Settings.Email.CallToActionAfter
        DeadlinesBefore = $ConfigFile.Settings.Email.DeadlinesBefore
        DeadlinesAfter = $ConfigFile.Settings.Email.DeadlinesAfter

    }

    return $generalSettings;
}

function LoadTargetSettings([string] $settingsFilePath, [string] $target, [bool] $loadBackupRestoreInformation = $true)
{

    $localSettingsFullPath = Resolve-Path $settingsFilePath;

    [xml]$ConfigFile = Get-Content $localSettingsFullPath
    $targetSettings = $ConfigFile.SelectNodes("/Settings/Target") | Where-Object { $_.Name -eq $target } | Select-Object -First 1
    if ($null -eq $targetSettings)
    {
            Write-Error "You have no settings for target $target. Check LocalSettings.xml"
            exit(-1)
    }

    $htmlEmailTemplateFullPath = Resolve-Path "$($ConfigFile.Settings.GitBaseFolder)\$($targetSettings.Email.HtmlTemplate)";

    if ($loadBackupRestoreInformation)
    {
		$BackupFileFullPath = Resolve-Path "$($ConfigFile.Settings.BackupFolder)\$($targetSettings.BackupFile)";
        $RestoreLocation = Resolve-Path $targetSettings.RestoreLocation;
        $DbFileName =  $targetSettings.DbFileName;
    }
    else
    {
        $BackupFileFullPath = "";
        $RestoreLocation = "";
        $DbFileName = "";
    }

    $settings = @{
        Name = $target
		BackupFileFullPath = $BackupFileFullPath;
		BackupFolder = $ConfigFile.Settings.BackupFolder;
		BackupFile = $targetSettings.BackupFile;
        Map = $Map;
        RestoreLocation = $RestoreLocation;
        DbFileName = $DbFileName;

        HtmlEmailTemplateFullPath = $htmlEmailTemplateFullPath
        EmailTemplate = Get-Content $htmlEmailTemplateFullPath -Raw

        EmailSend = $targetSettings.Email.Send
        EmailFrom = $targetSettings.Email.From
        EmailTo = [array]$targetSettings.Email.To.Address
        EmailAudienceText = $targetSettings.Email.AudienceText
        EmailServer = $targetSettings.Email.Server

        DatabaseServer = $targetSettings.DatabaseServer
        DatabaseName = $targetSettings.DatabaseName
    }

    return $settings;
}

function LoadVersionSettings([string] $versionSettingsFilePath, [string] $gitBaseFolder)
{
    $versionSettingsFileFullPath = Resolve-Path $versionSettingsFilePath
    [xml]$RepoConfigFile = Get-Content $versionSettingsFileFullPath

    # replace [ ] with HTML lists
    $releaseNotes = $RepoConfigFile.Settings.ReleaseNotes;
    $releaseNotes = $releaseNotes -replace "\[", "<li>";
    $releaseNotes = $releaseNotes -replace "\]", "</li>";

    <#
    <JIRA>
      <Item>
        <Project>TRW</Project>
        <IssueBaseUrl>https://profidocs.atlassian.net/browse/</IssueBaseUrl>
      </Item>
      <Item>
        <Project>TRWS</Project>
        <IssueBaseUrl>https://profidocs.atlassian.net/projects/TRWS/queues/custom/1/</IssueBaseUrl>
      </Item>
    </JIRA>#>
    Select-Xml $versionSettingsFilePath -XPath '/Settings/JIRA/Item' |
    ForEach-Object {
        # replace occurences of TRW-123 with <a href ="http://.../TRW-123">TRW-123</a>
        $replaceValue = '<a href="' + $_.Node.IssueBaseUrl + '$1">$1</a>';
        $replaceRegex = "(" + $_.Node.Project + "-\d+)";
        $releaseNotes = $releaseNotes -replace $replaceRegex, $replaceValue;
    }
    $versionSettings = @{
        BuildNumber = $RepoConfigFile.Settings.BuildNumber
        FullVersion = $RepoConfigFile.Settings.FullVersion
        ReleaseNotes = $releaseNotes
        RestoreScriptFullPath = Resolve-Path "$gitBaseFolder\$($RepoConfigFile.Settings.RestoreScript)"
        PostRestoreScriptFullPath = Resolve-Path "$gitBaseFolder\$($RepoConfigFile.Settings.PostRestoreScript)"
        }
    return $versionSettings;
}

function ShowSettingsForTarget($generalSettings, $targetSettings, $versionSettings, $currentBuildNumber, $showBackupRestore = $false)
{
    ""
    Write-Verbose "Your computer name        : $env:COMPUTERNAME"
    Write-Verbose "Target                    : $($targetSettings.Name)"

    Write-Verbose "Local settings from       : $($generalSettings.LocalSettingsFullPath)"

    if ($showBackupRestore) {
        Write-Verbose "Backup file               : $($targetSettings.BackupFileFullPath)"
        Write-Verbose "Restore Location          : $($targetSettings.RestoreLocation)\$($targetSettings.DbFileName)[_log].[lm]df"
        Write-Verbose "Restore script            : $($versionSettings.RestoreScriptFullPath)"
        Write-Verbose "Post restore script       : $($versionSettings.PostRestoreScriptFullPath)"
    }

    Write-Verbose "Database server           : $($targetSettings.DatabaseServer)"
    Write-Verbose "Database name             : $($targetSettings.DatabaseName)"

    Write-Verbose "Database Build No is      : $currentBuildNumber"
    Write-Verbose "Database Build No will be : $($versionSettings.BuildNumber) - ($($versionSettings.FullVersion)) [$($versionSettings.ReleaseNotes)]"

    Write-Verbose "Scripts from file         : $($generalSettings.DeploymentScriptFullPath)"
    Write-Verbose "Scripts base folder       : $($generalSettings.GitBaseFolder)"

    Write-Verbose "Email will be sent        : $($targetSettings.EmailSend)"
    Write-Verbose "Sent from                 : $($targetSettings.EmailFrom)"
    Write-Verbose "Sent to                   : $($targetSettings.EmailTo)"
    Write-Verbose "Sent via server           : $($targetSettings.EmailServer)"
    Write-Verbose "Target audience           : $($targetSettings.EmailAudienceText)"
    Write-Verbose "Template e-mail file      : $($targetSettings.HtmlEmailTemplateFullPath)"
}