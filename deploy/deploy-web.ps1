param (
    [Parameter(Mandatory=$true)] [string] $version,
    [Parameter(Mandatory=$false)] [switch] $simulate = $false,
    [ValidateSet('assy', 'airbag')]
    [Parameter(Mandatory=$true)] [string] $instance
)

Import-Module WebAdministration

. ".\DeployUtilities.ps1"

$zipFolder = "C:\developer\hb\transfer"

# The zip file containing the web site
$zipPath = "$zipFolder\hb-web-$version.zip"

# Where to deploy the web (IIS based)
$destPath = "C:\inetpub\wwwroot\hb\$instance"

# Where to extract the zip file
$tempPath = "C:\temp\hb_web_temp"

# Where to backup the existing web site
$backupPath = "C:\temp\hb_web_backup"

# Name of the website as it appears in IIS
$website = "Default Web Site"

$settingsSourcePath1 = "C:\developer\hb\live\settings\local.config.php.$instance"
$settingsDestPath1 = "$destPath\settings\local.config.php"

$lastAction = "None"
$currentAction = ""
try {

    $currentAction = "1. Temporary folder exists. Removing"
    if ($simulate -eq $false) {
        CleanupFolder $tempPath $currentAction
    }
    $lastAction = $currentAction

    $currentAction = "2. Unzipping $zipPath to $tempPath"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Unzip $zipPath $tempPath -Verbose
    }
    $lastAction = $currentAction

    $currentAction = "3. Removing existing backup folder."
    if ($simulate -eq $false) {
        CleanupFolder $backupPath $currentAction
        New-Item -ItemType directory -Path $backupPath
    }
    $lastAction = $currentAction

    $currentAction = "4. Stopping website $website"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Stop-Website -Name $website
    }
    $lastAction = $currentAction

    $currentAction = "5. Backing up $destPath to $backupPath"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Invoke-Expression "robocopy $destPath $backupPath /MIR"
    }
    $lastAction = $currentAction

    $currentAction = "6. Deploying new version from $tempPath to $destPath"
    Write-Output $currentAction
    # start copying a mirror using /zb switch to allow restarting in case access is denied
    if ($simulate -eq $false) {
        Invoke-Expression "robocopy $tempPath $destPath /MIR /zb"
    }
    $lastAction = $currentAction

    $currentAction = "7. Applying customized settings: local.config.php"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Copy-Item -Path $settingsSourcePath1 -Destination $settingsDestPath1 -Force
    }
    $lastAction = $currentAction

    $currentAction = "8. Start website $website"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Start-Website -Name $website
    }
    $lastAction = $currentAction
}
catch [Exception]
{
    Write-Error $_.Exception|format-list -force
    Write-Warning "Deployment failed. Last successful action was $lastAction"
}
