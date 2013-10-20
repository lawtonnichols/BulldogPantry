<?php

if (!isset($_GET['EventID']) || !isset($_GET['EmailAddress']) || !isset($_GET['Name']))
{
	header("Location: /error.html");
	return;
}

// from http://derek.io/blog/2009/php-a-better-random-string-function/
function generateCancelCode($length)
{
	return substr(MD5(microtime()), 0, $length);
}

$mysqli = new mysqli("localhost", "root", "root");
$mysqli->select_db("BulldogPantry");

$eventID = $mysqli->real_escape_string($_GET['EventID']);
$emailAddress = $mysqli->real_escape_string($_GET['EmailAddress']);
$name = $mysqli->real_escape_string($_GET['Name']);
$cancelCode = generateCancelCode(11);

// see if this person has already signed up for this event
$result = $mysqli->query("select * from volunteers where email = '$emailAddress' and event_id = $eventID");
if ($result->num_rows > 0)
{
	// if they have, don't let them sign up twice
	header("Location: /alreadySignedUp.html");
	return;
}

$mysqli->query("insert into volunteers (email, name, event_id, cancel_code) values ('$emailAddress', '$name', $eventID, '$cancelCode')");

$eventTitle = "";
$eventDescription = "";
$dateString = "";
$result = $mysqli->query("select * from events where id = $eventID");
if ($row = $result->fetch_assoc())
{
	$eventTitle = $row['event_title'];
	$eventLocation = $row['event_location'];
	$eventStart = getdate(strtotime($row['event_start']));
	$eventEnd = getdate(strtotime($row['event_end']));
	$eventDescription = $row['event_description'];
	$dateString = $eventStart['weekday'].',&nbsp;'.$eventStart['month'].'&nbsp;'.$eventStart['mday'].',&nbsp;'.$eventStart['year'].
				  ' '.timeString($eventStart).' to '.timeString($eventEnd);
}

// send an email to the volunteer
require_once "../Swift-5.0.1/lib/swift_required.php";
require_once "../private/passwords.php";
sendConfirmationEmail();

function timeString($timeArray)
{
	$hours = $timeArray['hours'];
	$minutes = $timeArray['minutes'];
	
	if ($hours == 0)
	{
		$hours = 12;
		$ampm = "a.m.";
	}
	else if ($hours >= 12)
	{
		if ($hours > 12)
			$hours = $hours - 12;
		$ampm = "p.m.";
	}
	else
	{
		$ampm = "a.m.";
	}
	
	if ($minutes < 10)
		$minutes = '0'.$minutes;
	
	return $hours.':'.$minutes.' '.$ampm;
}

function sendConfirmationEmail()
{
	global $eventTitle, $eventLocation, $eventDescription, $dateString, $cancelCode, $eventID, $emailUsername, $emailPassword;
	$email = $_GET['EmailAddress']; // we don't want this escaped
	
	$cancelLink = "http://localhost/cancel.php?EventID=$eventID&EmailAddress=$email&CancelCode=$cancelCode";
	
	$to = $email;
	$subject = "Bulldog Pantry Signup Confirmation—$eventTitle";
	$body = "You have successfully signed up for this event on ".
			"$dateString at $eventLocation. If you should wish ".
			"to cancel, please <a href='$cancelLink'>click here".
			"</a>. If you have any questions, please reply to ". 
			"this email. See you soon!";
	$from = $emailUsername;
	
	$toArray = explode(";", $to);
	$toArrayValidated = array();
	
	foreach ($toArray as $email)
	{
	    if (filter_var($email, FILTER_VALIDATE_EMAIL))
	        $toArrayValidated[] = $email;
	}
	
	// taken from http://stackoverflow.com/questions/712392/send-email-using-gmail-smtp-server-from-php-page
	
	$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
	  ->setUsername($emailUsername)
	  ->setPassword($emailPassword);
	
	$mailer = Swift_Mailer::newInstance($transport);
	
	$message = Swift_Message::newInstance($subject)
	  ->setFrom(array($emailUsername))
	  ->setTo($toArrayValidated)
	  ->setBody($body)
	  ->setContentType("text/html");
	
	$result = $mailer->send($message);
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<title>Sign-Up Confirmation</title>

<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<link href="http://code.jquery.com/ui/jquery-ui-git.css" rel="stylesheet">
<script src="http://code.jquery.com/ui/jquery-ui-git.js"></script>

<script>

$(document).ready(function () {
	$("#CancelButton").click(function () {
		window.location = "/cancel.php?EventID=<?php print $eventID; ?>&EmailAddress=<?php print $_GET['EmailAddress']; ?>&CancelCode=<?php print $cancelCode; ?>";
	});
	
	$("#DoneButton").click(function () {
		window.location = "/";
	});
});

</script>

<style>
body {background-color: #f5f5f5; max-width: 44em; margin: 0 auto; padding-top: 3em;}
p {text-indent: 8%; text-align: justify;}
button {width: 5.5em;}
</style>
</head>
<body>
<h1 class="text-center">Sign-Up Confirmation</h1>

<h3 class="">You have signed up for:</h3>
<h4 class="text-info"><?php print $eventTitle; ?>, <?php print $dateString; ?></h4>
<h3 class="">Using this Email Address:</h3>
<h4 class="text-info"><?php print $emailAddress; ?></h4>

<p class="lead">Please make sure that the above information is correct. An email will also be sent to you containing a cancellation link, just in case. If you entered your email incorrectly, signed up for the wrong volunteer event, or wish to cancel for any other reason, please click the Cancel button below now and start again.</p>

<p class="text-center">
	<button class="btn btn-large btn-primary" id="CancelButton">Cancel</button>
	<button class="btn btn-large btn-success" id="DoneButton">Done</button>
</p>

</body>
</html>
