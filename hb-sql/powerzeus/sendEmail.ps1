$EmailTo = "somebody@mail.com"
$EmailFrom = "anybody@mymail.com"
$Subject = "Test email" 
$Body = "This is a test" 
$SMTPServer = "smtp.mail.com" 
$SMTPMessage = New-Object System.Net.Mail.MailMessage($EmailFrom,$EmailTo,$Subject,$Body)
$SMTPClient = New-Object Net.Mail.SmtpClient($SmtpServer, 587) 
$SMTPClient.EnableSsl = $true # use for SSL and TSL
$SMTPClient.Credentials = New-Object System.Net.NetworkCredential("username", "password"); 

$SMTPClient.Send($SMTPMessage)