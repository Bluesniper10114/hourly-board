php .\vendor\phpcheckstyle\phpcheckstyle\run.php --src .\src --config .\psr2.cfg.xml --outdir .\doc\style-report

$ScriptDirectory = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent

[System.Diagnostics.Process]::Start("$ScriptDirectory/doc/style-report/index.html")
