# Preloads SQLPS to avoid warnings when running any SQL script for the first time

Clear-Host 
function Import-Module-SQLPS {
    #pushd and popd to avoid import from changing the current directory (ref: http://stackoverflow.com/questions/12915299/sql-server-2012-sqlps-module-changing-current-location-automatically)
    #3>&1 puts warning stream to standard output stream (see https://connect.microsoft.com/PowerShell/feedback/details/297055/capture-warning-verbose-debug-and-host-output-via-alternate-streams)
    #out-null blocks that output, so we don't see the annoying warnings described here: https://www.codykonior.com/2015/05/30/whats-wrong-with-sqlps/
    push-location
    import-module sqlps 3>&1 | out-null
    pop-location
}
 
"Is SQLPS Loaded?"
if(get-module sqlps){"yes"}else{"no"}
 
Import-Module-SQLPS
 
"Is SQLPS Loaded Now?"
if(get-module sqlps){"yes"}else{"no"}