param (
    [Parameter(Mandatory=$false)] [switch] $simulate = $false
)
. ".\DeployUtilities.ps1"

$sourcePath = "C:\developer\hb-docker\hb-sql\"
$version = GetXmlAttribute "$sourcePath\Settings.xml" "/Settings/FullVersion";

$zipPath = "C:\transfer\hb\hb-sql-$version.zip"
$tempPath = "C:\temp\hb_sql_temp\"

$lastAction = "None"
$currentAction = ""
try {
    $currentAction = "1. Removing temp folder $tempPath"
    CleanupFolder $tempPath $currentAction
    if ($simulate -eq $false) {
        New-Item -ItemType directory -Path $tempPath
    }
    $lastAction = $currentAction

    $currentAction = "2. Selectively copying $sourcePath to $tempPath"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Copy-Selectively $sourcePath $tempPath @("LocalSettings.xml", ".gitattributes", ".gitignore", ".gitmodules") @(".git", "solution", "scenario")
    }
    $lastAction = $currentAction

    $currentAction = "3. Removing old zip $zipPath"
    if ($simulate -eq $false) {
        CleanupFolder $zipPath $currentAction
    }
    $lastAction = $currentAction

    $currentAction = "4. Zipping to $zipPath"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Zip $tempPath $zipPath
    }
    $lastAction = $currentAction
}
catch [Exception]
{
    Write-Error $_.Exception|format-list -force
    Write-Warning "Preparation failed. Last successful step was: $lastAction"
}
