<?php
header("Content-Type: text/plain");

$error = false;

function getPostVariable($name, $errorIfUnset = true)
{
	global $error;
	if (isset($_POST[$name]))
	{
		return $_POST[$name];
	}
	else
	{
		if ($errorIfUnset)
			$error = true;
		return '';
	}
}

// from http://derek.io/blog/2009/php-a-better-random-string-function/
function generateCancelCode($length)
{
	return substr(MD5(microtime()), 0, $length);
}

$eventID = getPostVariable('EventID');
$eventTitle = getPostVariable('EventTitle');
$date = getPostVariable('Date');
$startTimeHours = getPostVariable('StartTimeHours');
$startTimeMinutes = getPostVariable('StartTimeMinutes');
$startTimeAmPm = getPostVariable('StartTimeAmPm');
$endTimeHours = getPostVariable('EndTimeHours');
$endTimeMinutes = getPostVariable('EndTimeMinutes');
$endTimeAmPm = getPostVariable('EndTimeAmPm');
$eventDescription = getPostVariable('EventDescription', false);
$numberOfSpots = getPostVariable('NumberOfSpots');
$emailAddresses = getPostVariable('EmailAddresses', false);

if ($error)
{
	print "There was an error in your input. Please press back and try entering it again.";
	return;
}

// otherwise, add the new information to the database
$date = date_create_from_format('m/d/Y', $date);
$startTime = clone $date;
$startTime->setTime($startTimeHours, $startTimeMinutes);
if ($startTimeAmPm == "pm")
	$startTime->modify("+12 hours");
$endTime = clone $date;
$endTime->setTime($endTimeHours, $endTimeMinutes);
if ($endTimeAmPm == "pm")
	$endTime->modify("+12 hours");
	
$startTimeString = $startTime->format("Y-m-d H:i:s");
print $startTimeString . "\n";
$endTimeString = $endTime->format("Y-m-d H:i:s");

$mysqli = new mysqli("localhost", "root", "root");
$mysqli->select_db("BulldogPantry");

$result = $mysqli->query("select email from volunteers where event_id = $eventID");
while ($row = $result->fetch_object())
{
	$emailAddressesArrayOld[] = $row['email'];
}
$result->free();
$emailAddressArrayNew = preg_split("/[\s]+/", $emailAddresses);

$emailsToAdd = array_diff($emailAddressesArrayNew, $emailAddressArrayOld);
$emailsToRemove = array_diff($emailAddressesArrayOld, $emailAddressesArrayNew);

$query = "update events set (event_title, event_start, event_end, event_description) = " .
		 "('$eventTitle', '$startTimeString', '$endTimeString', '$eventDescription') ".
		 "where event_id = $eventID";
$mysqli->query($query);

foreach ($emailsToRemove as $email)
{
	$query = "delete from volunteers where event_id = $eventID and email = '$email'";
	$mysqli->query($query);
}

foreach ($emailsToAdd as $email)
{
	$cancelCode = generateCancelCode(11);
	$query = "insert into volunteers (email, event_id, cancel_code) values " .
			 "('$email', $eventID, $cancelCode)";
	$mysqli->query($query);
	// send an email to the new volunteer
	
}

print "Success";

?>