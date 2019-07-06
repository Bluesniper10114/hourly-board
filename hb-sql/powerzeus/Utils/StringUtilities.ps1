function ListOfFilesNotHavingExtensions($list, $exclude)
{
    # creates a list of files changed since version change
    $finalList = "";          
    foreach ($file in $changedFiles) 
    {
        $extension = [IO.Path]::GetExtension($file);
        if ($exclude -notcontains $extension)
        {
            $finalList += "`n" + $file;                      
        }
    }
    $finalList += "`n";
    return $finalList;
}

function ChangeXmlAttribute([string] $pathToXml, [string] $xmlAttribute, [string] $newValue)
{
    [xml]$xmlFile = Get-Content $pathToXml;
    $filesChangedAttribute = $xmlFile.SelectSingleNode($xmlAttribute);
    $oldValue = $filesChangedAttribute.InnerText;
    $filesChangedAttribute.InnerText = $newValue;
    $xmlFile.Save($pathToXml);
    return $oldValue;
}

function GetXmlAttribute([string] $pathToXml, [string] $xmlAttribute)
{
    [xml]$xmlFile = Get-Content $pathToXml;
    $filesChangedAttribute = $xmlFile.SelectSingleNode($xmlAttribute);
    return $filesChangedAttribute.InnerText;
}