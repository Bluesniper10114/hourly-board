param (
    [Parameter(Mandatory=$false)] [switch] $simulate = $false
)
. ".\DeployUtilities.ps1"

# Web site location (git folder)
$sourcePath = "C:\developer\hb-docker\hb-web"
$version = GetXmlAttribute "$sourcePath\settings\version.xml" "/Version/Number";

# Temporary folder where to copy the zip source
$tempPath = "C:\temp\hb_web_temp"

# Zip file location
$zipPath = "C:\transfer\hb-web-$version.zip"

$lastAction = "None"
$currentAction = ""

if (($null -eq $version) -or ($version.length -eq 0)) {
    Write-Error "Cannot determine web version"
    exit -1;
}

try {
    $currentAction = "1. Removing temp folder $tempPath"
    if ($simulate -eq $false) {
        CleanupFolder $tempPath $currentAction
        New-Item -ItemType directory -Path $tempPath
    }
    $lastAction = $currentAction

    $currentAction = "2. Removing old zip $zipPath"
    if ($simulate -eq $false) {
        CleanupFolder $zipPath $currentAction
    }
    $lastAction = $currentAction

    $currentAction = "3. Selectively copying $sourcePath to $tempPath"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Copy-Selectively $sourcePath $tempPath @(".gitattributes", ".gitignore", ".gitmodules") @(".git")
    }
    $lastAction = $currentAction

    $currentAction = "4. Removing $tempPath\settings\local.config.php"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        CleanupFolder "$tempPath\settings\local.config.php"
    }
    $lastAction = $currentAction

    $currentAction = "5. Zipping to $zipPath"
    Write-Output $currentAction
    if ($simulate -eq $false) {
        Zip $tempPath $zipPath
    }
    $lastAction = $currentAction
}
catch [Exception]
{
    Write-Error $_.Exception|format-list -force
    Write-Warning "Preparation failed. Last successful action was: $lastAction"
}


