### WARNING: This script is corrupt. It needs to be adapted to the changes in the SQL 
<#
    .SYNOPSIS
        Utility to manage version in the .\Settings.xml file and update its content

    .EXAMPLE
        verSQL.ps1 -Version 2.3                         => only increases the version number, keeps the build number unchanged
        verSQL.ps1 -Action build -version 2.4           => increases the version number and the build number
        verSQL.ps1 -Action diff                         => only writes the different files since the last build increase
        
    It is used in a SQL project usually to manage the version 
#>

param (
    [ValidateSet('build', 'diff', 'version', 'force')]
    [Parameter(Mandatory=$false)] [string] $Action,
    [Parameter(Mandatory=$false)] [string] $Version,
    [Parameter(Mandatory=$true)] [string] $PathToSettingsFile,
    [Parameter(Mandatory=$false)] [bool] $Silent=$false
    )

# functions
. "$PSScriptRoot\..\..\Utils\VersionUtilities.ps1"
. "$PSScriptRoot\..\..\Utils\GitUtilities.ps1"
. "$PSScriptRoot\..\..\Utils\StringUtilities.ps1"
. "$PSScriptRoot\..\..\Utils\SettingsUtilities.ps1"
. "$PSScriptRoot\..\..\Utils\ConsoleUtilities.ps1"

function Log([string] $message)
{
    if ($Silent -eq $false)
    {
        Write-Host $message
    }    
}

# increments the build number only, keeps the version unchanged
function IncreaseBuild([xml] $configFile, [string] $majorMinorVersion)
{
    # get node <BuildNumber>
    $buildNumberAttribute = $configFile.SelectSingleNode("//BuildNumber");
    $buildNumberAttribute.InnerText = VersionIncreaseBuild $buildNumberAttribute.InnerText;
    Log "New build number is: $($buildNumberAttribute.InnerText)";    
}

# increments the version in the settings file, considering the new major.minor version given
function ProcessSettingsAndIncrementVersion([xml] $configFile, [string] $majorMinorVersion, [bool] $force = $false)
{    
    # get node <FullVersion>
    $fullVersionAttribute = $configFile.SelectSingleNode("//FullVersion");
    $fullVersion = $fullVersionAttribute.InnerText;

    if ($force)
    {
        $fullVersion = $majorMinorVersion;        
    }
    else
    {
        $fullVersion = VersionIncrease $fullVersion $majorMinorVersion;        
    }
    $fullVersionAttribute.InnerText = $fullVersion;
    
    # get nodes:
    #   <git><Branch>
    #   <git><Commit>
    $branchAttribute = $configFile.SelectSingleNode("//git/Branch");
    $commitAttribute = $configFile.SelectSingleNode("//git/Commit");

    # current branch
    $branchAttribute.InnerText = GitGetCurrentBranch;
    
    # latest commit
    $commitAttribute.InnerText = GitGetLatestCommitHash;

    Log "Increased version to: $fullVersion";
}

function ResetReleaseNotes([xml] $configFile)
{
    Log "Resetting release notes";
    $releaseNotesAttribute = $configFile.SelectSingleNode("//ReleaseNotes");
    $releaseNotesAttribute.InnerText = "
        [TBD]
    ";
}

function ResetFileList([xml] $configFile, [string] $queryListFilePath)
{
    Log "Resetting differential file list";

    # clear list of files changed since version change
    $filesChangedAttribute = $configFile.SelectSingleNode("//git/FilesChanged");
    $filesChangedAttribute.InnerText = "";

    $header = "id,file,type,subtype,run"
    $header | Out-File $queryListFilePath;
}


# writes a list of changed files in the settings file
function ProcessSettingsAndGenerateListOfFiles([xml] $configFile)
{
    #   <git><Branch> matches current branch?
    $branchAttribute = $configFile.SelectSingleNode("//git/Branch");
    $currentBranch = GitGetCurrentBranch;
    if ($branchAttribute.InnerText -ne $currentBranch)
    {
        Write-Error "You are on the wrong branch ($currentBranch) to compare commits (expected branch: $($branchAttribute.InnerText). Stopping";
    }
    else 
    {
        $commitAttribute = $configFile.SelectSingleNode("//git/Commit");
        
        # list of files to exclude from the changed list:

        $excludeExtensions = @(
            ".ps1",
            ".html",
            ".xml",
            ".txt",
            ".csv"
        )

        # creates a list of files changed since version change
        $filesChangedAttribute = $configFile.SelectSingleNode("//git/FilesChanged");
        $changedFiles = GitDiffVsCommit $commitAttribute.InnerText;
        $filesChangedAttribute.InnerText = ListOfFilesNotHavingExtensions $changedFiles $excludeExtensions;                  
        Log "List of changed vs commit <$($commitAttribute.InnerText)> files successfully updated"; 
    }   
}

# Displays the version currently written in the settings file
function ShowVersionInSettingsFile([xml] $configFile)
{
    # get node <BuildNumber>
    $buildNumberAttribute = $configFile.SelectSingleNode("//BuildNumber");
    $buildNumber = $buildNumberAttribute.InnerText;
    Log "Current build number: $buildNumber";
    
    # get nodes:
    #   <git><Branch>
    #   <git><Commit>
    $branchAttribute = $configFile.SelectSingleNode("//git/Branch");
    $commitAttribute = $configFile.SelectSingleNode("//git/Commit");

    # current branch
    $currentBranch = GitGetCurrentBranch;
    
    # latest commit
    $latestCommit = GitGetLatestCommitHash;

    Log "Version start branch: $($branchAttribute.InnerText). Current branch $currentBranch";
    Log "Version start commit: $($commitAttribute.InnerText). Latest commit $latestCommit";
    return $fullVersion;
}

Write-Error "Corrupt script. This script needs to be adapted to the changes in the SQL Server project structure";
exit;
# main code

#ContinueOnYes "Settings from $($PathToSettingsFile). Continue?"

Log "Settings $($PathToSettingsFile)"
[xml]$ConfigFile = Get-Content $PathToSettingsFile;

[bool]$save = $false;

# get node <FullVersion>
$fullVersionAttribute = $configFile.SelectSingleNode("//FullVersion");
$currentVersion = $fullVersionAttribute.InnerText;
Log "Current version: $currentVersion";

try 
{
    switch ($Action) {
        'version'
        {  
            Log "I'm changing major.minor version to $Version. The differential list will be cleared";
            ShowVersionInSettingsFile $ConfigFile;
            ProcessSettingsAndIncrementVersion $ConfigFile $Version;
            ResetReleaseNotes $ConfigFile;
            ResetFileList $ConfigFile $generalSettings.DeploymentScriptFullPath; 
            $save = $true;           
        }
        'force'
        {  
            Log "I'm forcibly changing version to $Version. The differential list will be cleared";
            ShowVersionInSettingsFile $ConfigFile;
            ProcessSettingsAndIncrementVersion $ConfigFile $Version $true; # force version to a specific patch number
            ResetReleaseNotes $ConfigFile;
            ResetFileList $ConfigFile $generalSettings.DeploymentScriptFullPath; 
            $save = $true;           
        }
        'build' 
        { 
            Log "I'm increasing the build number, the version remains unchanged. Differential list remains unchanged";
            ShowVersionInSettingsFile $ConfigFile;
            IncreaseBuild $ConfigFile;
            ResetReleaseNotes $ConfigFile;
            ResetFileList $ConfigFile $generalSettings.DeploymentScriptFullPath; 
            $save = $true;
        }
        'diff' 
        {
            Log "Refreshing the differential list since last build";
            ProcessSettingsAndGenerateListOfFiles $ConfigFile;
            $save = $true;    
        }
        Default 
        {
            ShowVersionInSettingsFile $ConfigFile;
        }
    }
}
catch 
{
    $ErrorMessage = $_.Exception.Message
    $FailedItem = $_.Exception.ItemName
    Write-Error "An error occured ($ErrorMessage, $FailedItem). Cancelling save.";
    $save = $false;    
    break;
}

# save changes if no error
if ($save -eq $true) 
{
    Log "Saved Version $Version in $($PathToSettingsFile)"
    $ConfigFile.Save($PathToSettingsFile);
}

return $currentVersion;
#end of file