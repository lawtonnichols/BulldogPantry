<?php

require_once "../Swift-5.0.1/lib/swift_required.php";
require_once "../private/passwords.php";

$to = "xxxxx@mail.fresnostate.edu";
$subject = "Test";
$body = "Testing…";
$from = $emailUsername;

$toArray = explode(";", $to);
$toArrayValidated = array();

foreach ($toArray as $email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL))
        $toArrayValidated[] = $email;
    else
        print "invalid email: " . $email . "\n"; 
}

// taken from http://stackoverflow.com/questions/712392/send-email-using-gmail-smtp-server-from-php-page

$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
  ->setUsername($emailUsername)
  ->setPassword($emailPassword);

$mailer = Swift_Mailer::newInstance($transport);

$message = Swift_Message::newInstance($subject)
  ->setFrom(array($emailUsername))
  ->setTo($toArrayValidated)
  ->setBody($body);

$result = $mailer->send($message);

?>
