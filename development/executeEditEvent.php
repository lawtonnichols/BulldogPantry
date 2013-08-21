<?php

require_once("startSessionOrError.php");

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
$numberOfSpots = intval(getPostVariable('NumberOfSpots'));
if ($numberOfSpots < 0)
	$numberOfSpots = 0;
$emailAddresses = getPostVariable('EmailAddresses', false);

if ($error)
{
	header("Location: /error.html");
	return;
}

// otherwise, add the new information to the database
$date = date_create_from_format('m/d/Y', $date);
$startTime = clone $date;
if ($startTimeHours == 12 && $startTimeAmPm == "am")
	$startTimeHours = 0;
$startTime->setTime($startTimeHours, $startTimeMinutes);
if ($startTimeAmPm == "pm" && $startTimeHours != 12)
	$startTime->modify("+12 hours");
$endTime = clone $date;
if ($endTimeHours == 12 && $endTimeAmPm == "am")
	$endTimeHours = 0;
$endTime->setTime($endTimeHours, $endTimeMinutes);
if ($endTimeAmPm == "pm" && $endTimeHours != 12)
	$endTime->modify("+12 hours");
	
$startTimeString = $startTime->format("Y-m-d H:i:s");
$endTimeString = $endTime->format("Y-m-d H:i:s");

$mysqli = new mysqli("localhost", "root", "root");
$mysqli->select_db("BulldogPantry");

$result = $mysqli->query("select email from volunteers where event_id = $eventID");
$emailAddressesArrayNew = array();
$emailAddressesArrayOld = array();
while ($row = $result->fetch_assoc())
{
	$emailAddressesArrayOld[] = $row['email'];
}
$result->free();
if (isset($emailAddresses))
	$emailAddressesArrayNew = preg_split("/[\s]+/", $emailAddresses);

$emailsToAdd = array_diff($emailAddressesArrayNew, $emailAddressesArrayOld);
$emailsToRemove = array_diff($emailAddressesArrayOld, $emailAddressesArrayNew);

$eventTitle = $mysqli->real_escape_string($eventTitle);
$startTimeString = $mysqli->real_escape_string($startTimeString);
$endTimeString = $mysqli->real_escape_string($endTimeString);
$eventDescription = $mysqli->real_escape_string($eventDescription);
$numberOfSpots = $mysqli->real_escape_string($numberOfSpots);

$query = "update events set event_title = '$eventTitle', event_start = '$startTimeString', event_end = '$endTimeString', ".
		 "event_description = '$eventDescription', number_of_spots = '$numberOfSpots' ".
		 "where id = $eventID";
$mysqli->query($query);

foreach ($emailsToRemove as $email)
{
	if (strlen($email) > 0)
	{
		$query = "delete from volunteers where event_id = $eventID and email = '$email'";
		$mysqli->query($query);
	}
}

foreach ($emailsToAdd as $email)
{
	if (strlen($email) > 0)
	{
		$cancelCode = generateCancelCode(11);
		$query = "insert into volunteers (email, event_id, cancel_code) values " .
				 "('$email', $eventID, $cancelCode)";
		$mysqli->query($query);
	}
}

header("Location: /viewEvents.php");

?>