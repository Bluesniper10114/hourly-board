# Run non-verbosely and ask for confirmations
# .\restore_deploy.ps1
# .\restore_deploy.ps1 -Verbose:$false

# Run verbosely and ask for confirmations (for debugging)
# .\restore_deploy.ps1 -Verbose:$true

# Run verbosely and do not ask for confirmation
# .\restore_deploy.ps1 -Verbose:$true -automaticConfirmation:y

# Run non-verbosely and do not ask for confirmation (fastest and cleanest)
# .\restore_deploy.ps1 -Verbose:$false -automaticConfirmation:y

param (
    [Parameter(Mandatory=$false)] [int]$skipToStep = 1,
    [Parameter(Mandatory=$false)] [switch]$slow = $false,
    [ValidateSet('y', 'n', '?')]
    [Parameter(Mandatory=$false)] [string]$automaticConfirmation = '?'
)

$ScriptDirectory = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
. "$ScriptDirectory\powerzeus\Utils\ConsoleUtilities.ps1"
. "$ScriptDirectory\powerzeus\Utils\ChuckNorris.ps1"

$fast = -not $slow

if ($slow -eq $true)
{
    $joke = ChuckNorrisQuote
    "`n$joke`n"
}

$step = 1;

if ($skipToStep -le $step)
{
    ContinueOnYes "$step : I'll restore from backup target 'DEBUG'. This could take some time. Continue?" $automaticConfirmation
    .\powerzeus\commands\restore.ps1 -target: debug -verbose:$verbose -automaticConfirmation:$automaticConfirmation
}
$step++;

if ($skipToStep -le $step)
{
    ContinueOnYes "$step : Restore done. Continue?" $automaticConfirmation
    .\powerzeus\commands\deploy.ps1 -target: debug -apply:$true -skipVersion:$false -fast: $fast -verbose:$verbose -automaticConfirmation:$automaticConfirmation
}

$step++;
if ($skipToStep -le $step)
{
    ContinueOnYes "$step : Deployment done. Continue?" $automaticConfirmation
    .\powerzeus\commands\post_restore.ps1 -target: debug -automaticConfirmation:$automaticConfirmation
}