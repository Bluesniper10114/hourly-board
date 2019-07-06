function WriteError([string] $text)
{
    Write-Host $text  -ForegroundColor "White" -BackgroundColor "Red"
}

function WriteWarning([string] $text)
{
    Write-Host $text  -ForegroundColor "Black" -BackgroundColor "DarkYellow"
}

# possible values for $answer: 'y', 'n', '?'
# '?' => a value will be requested by the prompt
# 'y' => assuming the answer is 'yes'
# 'n' => assuming the answer is 'no'

function ContinueOnYes([string] $question, [string] $answer)
{
    Write-Output $question
    if ($answer -and ($answer -ne '?'))
    {
        Write-Output "Assumed automatic answer $answer"
        $input = $answer;
    }
    else
    {
        $input = Read-Host "Type [Yy]es if you agree then hit ENTER. Anything else if you want to cancel"
    }
    if (($input -ne "Y") -or ($input -ne "y"))
    {
        Write-Error "Cancelled by user"
        exit(-1)
    }
}