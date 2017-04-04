<?php

include_once "wp-content/plugins/swift-mailer/lib/swift_required.php";

$subject = 'Hello from Mandrill, PHP!';
$from = array('test@dn.se' =>'DN.SE');
$to = array(
 'lindesvard@gmail.com'  => 'Carl-Gerhard Lindesvärd',
 'carl-gerhard.lindesvard@dn.se' => 'Carl-Gerhard Lindesvärd'
);

$html = "<em>Test <strong>Email</strong></em>";

$transport = Swift_SmtpTransport::newInstance('mail.bonniernews.se', 25);
$transport->setUsername('asiktsredaktionen@dn.se');
$transport->setPassword('5342%gydd');
$swift = Swift_Mailer::newInstance($transport);

$message = new Swift_Message($subject);
$message->setFrom($from);
$message->setBody($html, 'text/html');
$message->setTo($to);

if ($recipients = $swift->send($message, $failures))
{
echo 'Message successfully sent!';
} else {
	echo "There was an error:\n";
	print_r($failures);
}

?>