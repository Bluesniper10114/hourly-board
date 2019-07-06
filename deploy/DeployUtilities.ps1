Add-Type -AssemblyName System.IO.Compression.FileSystem
function Unzip
{
    param([string]$zipfile, [string]$outpath)

    [System.IO.Compression.ZipFile]::ExtractToDirectory($zipfile, $outpath)
}

function Zip
{
	param([string]$sourcePath, [string]$zipPath)
	[System.IO.Compression.ZipFile]::CreateFromDirectory($sourcePath, $zipPath)
}

function Copy-Selectively
{
    param ([string] $from, [string] $to, [string[]] $excludeFiles, [string[]] $excludeFolders)

    $folders = Get-ChildItem -Path $from -Exclude $excludeFiles |
    Where-Object {
        ($excludeFolders -eq $null) -or ($excludeFolders -notcontains $_.FullName.Replace($from, ""))
    }

    foreach ($folder in $folders)
    {
        $destinations = Join-Path $to $folder.FullName.Substring($from.length)
        foreach ($destination in $destinations)
        {
            Write-Output "Copy-Item -Path $folder -Destination $destination -Recurse -Force"
            Copy-Item -Path $folder -Destination $destination -Recurse -Force
        }
    }
}

function CleanupFolder
{
	param([string] $targetPath, [string] $message)

	if ((test-path $targetPath) -eq $true) {
		Write-Output $message
		Remove-Item $targetPath -Force -Recurse
	}
}

function GetXmlAttribute([string] $pathToXml, [string] $xmlAttribute)
{
    [xml]$xmlFile = Get-Content $pathToXml;
    $filesChangedAttribute = $xmlFile.SelectSingleNode($xmlAttribute);
    return $filesChangedAttribute.InnerText;
}