# https://gist.github.com/campbellja/e5d735f048cab76adb16957fe4a7ad75

<#param(
    [Parameter(Mandatory=$true)] [string] $VersionXmlFilePath                   # path to the version file of the specific OS Info.plist for iOS and AndroidManifest.xml for Android
    [Parameter(Mandatory=$true)] [string] $OS                                   # operating system ("iOS" or "Android")
    [Parameter(Mandatory=$true)] [string] $NewVersion            # desired major.minor version (e.g. "4.3")    
)#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$true, ValueFromPipelineByPropertyName=$true)] [string] $VersionXmlFilePath,
    [Parameter(Mandatory=$true, ValueFromPipelineByPropertyName=$true)] [string] $OS,
	[Parameter(Mandatory=$true, ValueFromPipelineByPropertyName=$true)] [string] $NewVersion,
	[Parameter(Mandatory=$true, ValueFromPipelineByPropertyName=$true)] [bool] $Silent = $false,
	[Parameter(Mandatory=$false, ValueFromPipelineByPropertyName=$true)] [bool] $KeepBuildNumber = $true
)

# count the number of commits and use it as a build number
$commitCount = & git rev-list --count HEAD
if (($Silent -ne $true) -and ($KeepBuildNumber -eq $false))
{
	Write-Host "git rev-list --count HEAD = $commitCount  (Number of commits in branch, used as build number)";
}

$params = @{	
	FilePath = $VersionXmlFilePath;
	OS = $OS;
	NewVersion = $NewVersion;
	BuildCounter = $commitCount;
}

if(![System.IO.File]::Exists($params.FilePath)){
	Write-Error "Path <$($params.FilePath)> does not exist"
	exit
}

$version = @{
	PackageVersion = $NewVersion
}

if ($Silent -ne $true)
{
	Write-Host "##BAWhiteLabel[buildNumber '$($version.PackageVersion)']"
}
$fileXml = [xml] (Get-Content $params.FilePath )
	
$versionNumber = $version.PackageVersion
$buildCounter = $params.BuildCounter
$currentVersion = "unknown"

if($params.OS -eq "iOS"){
	if ($Silent -ne $true)
	{
		Write-Output "Setting CFBundleVersion to $versionNumber"	
	}
	$currentVersion = Select-Xml -xml $fileXml -XPath "//dict/key[. = 'CFBundleShortVersionString']/following-sibling::string[1]";
	$currentBuildNumber = Select-Xml -xml $fileXml -XPath "//dict/key[. = 'CFBundleVersion']/following-sibling::string[1]";
	$currentVersion = "$currentVersion ($currentBuildNumber)"

	Select-Xml -xml $fileXml -XPath "//dict/key[. = 'CFBundleShortVersionString']/following-sibling::string[1]" |
	ForEach-Object{ 	
		$_.Node.InnerXml = $versionNumber
	}
							
	if ($KeepBuildNumber -eq $false)
	{
		Select-Xml -xml $fileXml -XPath "//dict/key[. = 'CFBundleVersion']/following-sibling::string[1]" |
		ForEach-Object { 	
			$_.Node.InnerXml = $buildCounter
		}
	}
}
elseif($params.OS -eq "Android")
{
	$currentBuildNumber = Select-Xml -xml $fileXml -XPath "/manifest/@android:versionCode" -namespace @{android="http://schemas.android.com/apk/res/android"};
	$currentVersion = Select-Xml -xml $fileXml -XPath "/manifest/@android:versionName" -namespace @{android="http://schemas.android.com/apk/res/android"};
	$currentVersion = "$currentVersion ($currentBuildNumber)"

	if ($KeepBuildNumber -eq $true)
	{
		$buildCounter = $currentBuildNumber;
	}
	$xpath = "//manifest"
	if ($Silent -ne $true)
	{
		Write-Host "Setting manifest.android:versionCode to $versionNumber"	
	}
	Select-Xml -xml $fileXml -XPath $xpath |
	ForEach-Object{ 				
		$_.Node.SetAttribute("android:versionName", "$versionNumber.$buildCounter")
		$_.Node.SetAttribute("android:versionCode", $buildCounter)
	}
}
else{
	Write-Error "Unrecognised OS argument: $params.OS"
	exit 
}

$fileXml.Save($params.FilePath) 
if ($Silent -ne $true)
{
	Write-Host "Saved Version $versionNumber - $buildCounter in $($params.FilePath)"	
}

return $currentVersion;