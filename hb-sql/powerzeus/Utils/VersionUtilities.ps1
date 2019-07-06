<#
    .SYNOPSIS
        Utilities working with version strings in the format major.minor.patch

    .LINK
        www.semver.org
#>

function VersionGetPatchNumber([string] $versionString)
{
    <#
        .SYNOPSIS
            Gets the patch number (last number) in major.minor.patch version struncture.

        .PARAMETER versionString
            A version string in the format 1.2.3 (According to www.semver.org specs)

        .EXAMPLE
            GetPatchNumber "1.2.3"
        
        .OUTPUTS
            The patch number (int)
    #>
    $segments = $versionString.Split(".");    
    return $segments[2];
}



function VersionSplit([string] $versionString)
{
    <#
    .SYNOPSIS
        Gets an object from a string version (with major.minor.patch version struncture).

    .PARAMETER versionString
        A version string in the format 1.2.3 (According to www.semver.org specs)

    .EXAMPLE
        VersionSplit "1.2.3"

    .OUTPUTS
        An object having properties {Major, Minor, Patch}

    .LINK
        www.semver.org
    #>

    $segments = $versionString.Split(".");
    $v1 = "0";
    if ($segments[0])
    {
        $v1 = $segments[0];
    }

    $v2 = "0";
    if ($segments[1])
    {
        $v2 = $segments[1];
    }

    $v3 = "0";
    if ($segments[2])
    {
        $v3 = $segments[2];
    }

    $version = [PSCustomObject]@{
        Major = $v1
        Minor = $v2
        Patch = $v3
    }
    return $version;
}

function VersionIncrease([string] $versionString, [string] $majorMinorVersion)
{
    <#
    .SYNOPSIS
       Increases current version using a new major.minor version provided (with major.minor.patch version structure).

    .PARAMETER versionString
        A version string in the format 1.2.3 (According to www.semver.org specs)
    
    .PARAMETER majorMinorVersion
        A short version in the format 1.3

    .EXAMPLE
        VersionIncrease "1.2.3" "1.2" => returns 1.2.4
        VersionIncrease "1.2.3" "1.3" => returns 1.3.0

    .OUTPUTS
        A version object having properties {Major, Minor, Patch} with the new, incremented values. Read more at www.semver.org to understand the logic behind changing vesions.
    #>

    $newMajorMinor = VersionSplit $majorMinorVersion;
    $version = VersionSplit $versionString;
    
    $majorVersion = $version.Major;
    $minorVersion = $version.Minor;
    $patchVersion = $version.Patch;
    
    if (($newMajorMinor.Major -eq $majorVersion) -And ($newMajorMinor.Minor -eq $minorVersion))
    {
        $patchVersion = [string]([int]$patchVersion + 1);
    }
    else
    {
        $patchVersion = "0";
    }
    return "$($newMajorMinor.Major).$($newMajorMinor.Minor).$patchVersion";
}

function VersionIncreaseBuild([string] $buildNumber)
{
    return [int]$buildNumber + 1;
}

function SetAssemblyInfoVersion([string] $pathInfo, [string] $version, [bool] $anyBuildNumber = $true)
{
    if ($anyBuildNumber)
    {
        $versionPlaceHolder = "$version.*";        
    }
    else
    {
        $versionPlaceHolder = $version;
    }

    (Get-Content $pathInfo) | Foreach-Object {$_ -replace '^\s*\[assembly\:\s+AssemblyVersion\s*\(".*"\)\]' , "[assembly: AssemblyVersion (""$versionPlaceholder"")]"} | Set-Content $pathInfo        
}
