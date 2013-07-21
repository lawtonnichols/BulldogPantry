<?php

$error = false;	

if (isset($_POST['EventTitle']))
	$eventTitle = $_POST['EventTitle'];
else
	$error = true;
if (isset($_POST['Date']))
	$date = $_POST['Date'];
else
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
if (isset($_POST['EventDescription']))
	$eventDescription = $_POST['EventDescription'];
else
	$eventDescription = "";
if (isset($_POST['NumberOfSpots']))
	$numberOfSpots = $_POST['NumberOfSpots'];
else
	$error = true;

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
	
$query = "insert into events (event_title, event_start, event_end, event_description) values " .
		 "('$eventTitle', '$startTimeString', '$endTimeString', '$eventDescription')";

$mysqli = new mysqli("localhost", "root", "root");
$mysqli->select_db("BulldogPantry");
$mysqli->query($query);

print "Success";

?>