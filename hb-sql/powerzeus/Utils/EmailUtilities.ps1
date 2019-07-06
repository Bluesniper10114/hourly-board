function SendEmailFromTemplate($emailTemplate, $contentData, $emailServer)
{
    $template = FillEmailTemplate $emailTemplate $contentData

    try
    {
        Write-Output "Sending e-mail from $($contentData.FromEmail) to $($contentData.ToEmail)";
        Write-Output "Subject:  $($contentData.Subject)";

        send-mailmessage -from $contentData.FromEmail -to $contentData.ToEmail -subject $contentData.Subject -body $template -BodyAsHtml -priority Normal -dno onSuccess, onFailure -smtpServer $emailServer

    }
    catch
    {
        Write-Warning $_.Exception.Message
    }
}

function FillEmailTemplate($emailTemplate, $contentData, $fileName)
{
	$template = $emailTemplate;
    $template = $template -replace "{Joke}", $contentData.Joke;
    $template = $template -replace "{Subject}", $contentData.Title;
    $template = $template -replace "{Audience}", $contentData.Audience;
    $template = $template -replace "{Details}", $contentData.ReleaseNotes;
    $template = $template -replace "{VersionName}", $contentData.VersionName;
    $template = $template -replace "{CallToAction}", $contentData.CallToAction;
    $template = $template -replace "{Deadlines}", $contentData.Deadlines;
    $template = $template -replace "{DateTime}", $contentData.DateTime;
    return $template;
}