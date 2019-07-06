param (
    [Parameter(Mandatory=$true)] [string] $version,
    [Parameter(Mandatory=$false)] [switch] $simulate = $false,
)

. ".\DeployUtilities.ps1"

# Source zip file location
$zipPath = "C:\Developer\hb\transfer\hb-sql-$version.zip"

# Base folder where the deployment will be done
$destPath = "C:\Developer\hb\live\sql"

# Temporary unzip folder
$tempPath = "C:\temp\hb_sql_temp"

# Backup folder
$backupPath = "C:\temp\hb_sql_backup"

# Local settings location (specific to the live server, will be copied into the live location)
$settingsSourcePath = "C:\Developer\hb\settings\LocalSettings.xml"
$settingsDestPath = "$destPath\LocalSettings.xml"

$lastAction = "None"
$currentAction = ""
try {
    $currentAction = "1. Temp folder exists, cleaning up $tempPath"
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

    $currentAction = "3. Removing old backup $backupPath"
    if ($simulate -eq $false) {
        CleanupFolder $backupPath $currentAction
    }
    $lastAction = $currentAction

    $currentAction = "4. Backing up to $backupPath"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Invoke-Expression "robocopy $destPath $backupPath /MIR"
    }
    $lastAction = $currentAction

    $currentAction = "5. Deploying new version to $destPath"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Invoke-Expression "robocopy $tempPath $destPath /MIR"
    }
    $lastAction = $currentAction

    $currentAction = "6. Applying customized settings $settingsSourcePath => $settingsDestPath"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Copy-Item -Path $settingsSourcePath -Destination $settingsDestPath -Force
    }
    $lastAction = $currentAction

    Write-Output "Run .\simulate.ps1 in $destPath\deploy to test the sql script"
    Write-Output "Run .\release.ps1 in $destPath\deploy to deploy the sql script"

}
catch [Exception]
{
    Write-Error $_.Exception|format-list -force
    Write-Warning "Deployment failed. Last successful action was: $lastAction"
}


