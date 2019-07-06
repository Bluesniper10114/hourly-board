function GetBuildNumberOfSqlServer([string] $databaseServer, [string] $databaseName)
{
    $currentVersion = [int32]::MaxValue;

    try {
        # ====== Check Current Version (BuildNumber) on Server
        $SqlConnection = New-Object System.Data.SqlClient.SqlConnection;
        $SqlConnection.ConnectionString = "Server=$databaseServer;Database=$databaseName;Integrated Security=True";
        $SqlConnection.Open();
        $SqlCmd = New-Object System.Data.SqlClient.SqlCommand;
        $SqlCmd.CommandText = "select max(id) from ver.Version";
        $SqlCmd.Connection = $SqlConnection;
        $currentVersion = $SqlCmd.ExecuteScalar();
        $SqlConnection.Close();
        
    }
    catch {
        Write-Host "Unable to acquire version from server" -ForegroundColor "White" -BackgroundColor "Red";   
    }

    return $currentVersion;
}

function OpenBuildNumberOnSqlServer($versionData, [string] $databaseServer, [string] $databaseName)
{
    $trackStartCommand = "exec ver.MarkVersion @buildNumber = $($versionData.BuildNumber), @fullVersion = '$($versionData.FullVersion)', @description = '$($versionData.ReleaseNotes)', @start = 1"
    Invoke-Sqlcmd -ServerInstance "$databaseServer" -Database "$databaseName" -Verbose -AbortOnError -Query $trackStartCommand -OutputSqlErrors $true -ErrorAction Stop
}

function CloseBuildNumberOnSqlServer($versionData, [string] $databaseServer, [string] $databaseName)
{
    $trackEndCommand = "exec ver.MarkVersion @buildNumber = $($versionData.BuildNumber), @start = 0"
    Invoke-Sqlcmd -ServerInstance $databaseServer -Database $databaseName -Verbose -AbortOnError -Query $trackEndCommand -OutputSqlErrors $true	
}