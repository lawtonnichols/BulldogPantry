<?php

require_once("startSessionOrError.php");

$error = false;	

if (isset($_POST['EventTitle']))
	$eventTitle = $_POST['EventTitle'];
else
	$error = true;
if (isset($_POST['Date']))
	$date = $_POST['Date'];
else
	$error = true;	
$date = date_create_from_format('m/d/Y', $date);
if ($date == false)
	$error = true;
if (isset($_POST['StartTimeHours']))
	$startTimeHours = $_POST['StartTimeHours'];
else
	$error = true;
if (isset($_POST['StartTimeMinutes']))
	$startTimeMinutes = $_POST['StartTimeMinutes'];
else
	$error = true;
if (isset($_POST['StartTimeAmPm']))
	$startTimeAmPm = $_POST['StartTimeAmPm'];
else
	$error = true;
if (isset($_POST['EndTimeHours']))
	$endTimeHours = $_POST['EndTimeHours'];
else
	$error = true;
if (isset($_POST['EndTimeMinutes']))
	$endTimeMinutes = $_POST['EndTimeMinutes'];
else
	$error = true;
if (isset($_POST['EndTimeAmPm']))
	$endTimeAmPm = $_POST['EndTimeAmPm'];
else
	$error = true;
if (isset($_POST['EventLocation']))
	$eventLocation = $_POST['EventLocation'];
else
	$error = true;
if (isset($_POST['EventDescription']))
	$eventDescription = $_POST['EventDescription'];
else
	$eventDescription = "";
if (isset($_POST['NumberOfSpots']))
	$numberOfSpots = intval($_POST['NumberOfSpots']);
else
	$error = true;
if ($numberOfSpots < 0)
	$numberOfSpots = 0;

if ($error)
{
	header("Location: /error.html");
	return;
}

// otherwise (if there wasn't an error), add the new information to the database
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

$eventTitle = $mysqli->real_escape_string($eventTitle);
$startTimeString = $mysqli->real_escape_string($startTimeString);
$endTimeString = $mysqli->real_escape_string($endTimeString);
$eventDescription = $mysqli->real_escape_string($eventDescription);
$numberOfSpots = $mysqli->real_escape_string($numberOfSpots);
$eventLocation = $mysqli->real_escape_string($eventLocation);

$query = "insert into events (event_title, event_start, event_end, event_description, event_location_id, number_of_spots) values " .
		 "('$eventTitle', '$startTimeString', '$endTimeString', '$eventDescription', '$eventLocation', $numberOfSpots)";

$mysqli->query($query);
header("Location: /admin/viewEvents.php");

?>