# Passes through the queries in a XML file and executes the queries
function ExecuteQueriesInXmlFile([string] $pathToXmlFile, [string] $gitBaseFolder, [string] $databaseServer, [string] $databaseName, [int] $sleepMilliseconds)
{
    [Action[int, string, string]] $itemFound = {
        param([int] $i, [string] $type, [string] $path)

        Write-Host "  [RUN..]  $i Executing $($type): $path" -ForegroundColor "Green"
        $parameters = "SourceFolder=$gitBaseFolder", "databaseName=$databaseName"
        Invoke-Sqlcmd -ServerInstance $databaseServer -Database $databaseName -Verbose -AbortOnError -InputFile $path -OutputSqlErrors $true  -Variable $parameters -ErrorAction Stop -QueryTimeout 3600
    }

    [Action[int, string, string]] $itemSkipped = {
        param([int] $i, [string] $type, [string] $path)
        ItemSkipped $i $type $path;
    }
    [Action[int, string, string]] $itemMissing = {
        param([int] $i, [string] $type, [string] $path)
        ItemMissing $i $type $path;
    }

    $failOnMissingFiles = $true;
    ActOnXmlList $pathToXmlFile $gitBaseFolder $failOnMissingFiles $itemFound $itemSkipped $itemMissing $sleepMilliseconds
}

# Only passes through the list of queries in a CSV file, does not execute the queries
function ListQueriesInXmlFile([string] $pathToXmlFile, [string] $gitBaseFolder, [int] $sleepMilliseconds)
{
    [Action[int, string, string]] $itemFound = {
        param([int] $i, [string] $type, [string] $path)
        ItemFound $i $type $path;
    }
    [Action[int, string, string]] $itemSkipped = {
        param([int] $i, [string] $type, [string] $path)
        ItemSkipped $i $type $path;
    }
    [Action[int, string, string]] $itemMissing = {
        param([int] $i, [string] $type, [string] $path)
        ItemMissing $i $type $path;
    }
    
    $failOnMissingFiles = $false;
    ActOnXmlList $pathToXmlFile $gitBaseFolder $failOnMissingFiles $itemFound $itemSkipped $itemMissing $sleepMilliseconds
}

# Default action when an item is found: list item as found
function ItemFound
{
    param([int] $i, [string] $type, [string] $path)
    Write-Host "  [FOUND] $i Executing $($type): $path"
}

# Default action when a file is skipped: list as skipped
function ItemSkipped 
{
    param([int] $i, [string] $type, [string] $path)
    Write-Host "[SKIPPED] $i $($type): $path" -ForegroundColor "Black" -BackgroundColor "Yellow" 
}

# Default action when a file is missing: list as missing
function ItemMissing 
{
    param([int] $i, [string] $type, [string] $path)
    Write-Host "[MISSING] $i $($type): $path" -ForegroundColor "White" -BackgroundColor "Red"
}

# Determines if there are any missing files in the list of files provided
function AnyMissingFiles($allFiles)
{
    $missing = New-Object System.Collections.ArrayList;

    Foreach ($filePath in $allFiles) 
    { 
        $fileExists = Test-Path $filePath

        if ($fileExists -ne $true)
        {
            $missing.Add($filePath);
            Write-Host "Missing file: $filePath"; 
        }
    }
    return $missing;
}

function ActOnXmlList([string] $pathToXmlFile, [string] $gitBaseFolder, [bool] $failOnMissingFiles, [Action[int, string, string]] $whenFound, [Action[int, string, string]] $whenSkipped, [Action[int, string, string]] $whenMissing, [int] $sleepMilliseconds)
{
    $countScripts = 0;
    $listOfPaths = @();

    $allScripts = @();
    Select-Xml $pathToXmlFile -XPath '/Settings/Deploy/File [ not(@Applied) or @Applied = "false" ]' | 
    ForEach-Object {  
        $node = @{
            File = $_.Node.InnerText
            Path = "$gitBaseFolder\$($_.Node.InnerText)"
            Applied = $_.Node.Attributes["Applied"].Value
            Comment = $_.Node.Attributes["Comment"].Value
            Type = $_.Node.Attributes["Type"].Value        
        }  
        if ($node.Applied -eq $null)
        {
            $node.Applied = "false";
        }
        $allScripts += $node;
        $countScripts++;
        $listOfPaths += $node.Path;         
    }    

    if ($failOnMissingFiles)
    {
        $missing = AnyMissingFiles $listOfPaths;
        if ($missing.Count -gt 0) 
        {
            Write-Host "Failing early. There are missing files." -ForegroundColor "White" -BackgroundColor "Red"
            return;
        }
    }
    
    $i = 0;
    ForEach($script in $allScripts)
    {
        $i++
        if ($sleepMilliseconds -gt 0) 
        {
            Write-Progress -Activity "Deploying files" -Status "Working on file $i/$countScripts : $($script.File)" -PercentComplete ($i / $countScripts*100)
        }
        $script.Comment
        Start-Sleep -M $sleepMilliseconds

        if ($script.Applied -notlike "false")
        {
            $whenSkipped.Invoke($i, $script.Type, $script.File);
        }
        else
        {   
            $fileExists = Test-Path $script.Path;
            if ($fileExists -eq $true)
            {
                $whenFound.Invoke($i, $script.Type, $script.Path);
            }
            else
            {
                $whenMissing.Invoke($i, $script.Type, $script.Path);
            }
        }
    }
}