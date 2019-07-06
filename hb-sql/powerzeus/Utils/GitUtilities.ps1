function GitGetCurrentBranch()
{
    $branch = git rev-parse --abbrev-ref HEAD
    return $branch;
}

function GitGetLatestCommitHash()
{
    $latestCommit = git log -n 1 --pretty=format:"%H"
    return $latestCommit;
}

function GitDiffVsCommit ([string] $commit)
{
    $diff = git diff --name-only HEAD
    return $diff;
}

function GitCheckBranch([string] $filePath, [string] $expectedBranch)
{
    $onExpectedBranch = $false;
    Push-Location
    try 
    {
        $checkBranchInFolder = Split-Path -Path $filePath -Parent;
        cd $checkBranchInFolder;
        $currentBranch = GitGetCurrentBranch;
        $onExpectedBranch = $currentBranch -eq $expectedBranch;
    }
    catch 
    {
        
    }
    
    Pop-Location
    if (-not $onExpectedBranch)
    {
        throw "File $filePath is not on the $expectedBranch branch";
    }
}


