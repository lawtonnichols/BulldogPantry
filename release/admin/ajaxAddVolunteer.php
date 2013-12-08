<?php

require_once("startSessionOrError.php");

if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['event']))
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

$name = $mysqli->real_escape_string($_POST['name']);
$emailAddress = $mysqli->real_escape_string($_POST['email']);
$eventID = $mysqli->real_escape_string($_POST['event']);
$cancelCode = generateCancelCode(11);

$mysqli->query("insert into volunteers (email, name, event_id, cancel_code) values ('$emailAddress', '$name', $eventID, '$cancelCode')");

$result = $mysqli->query("select * from volunteers where email = '$emailAddress' and event_id = '$eventID'");

if ($row = $result->fetch_assoc())
{
	$id = $row['id'];
	$output = '{"success": "true", "id": "'. $id . '"}';
	print $output;
}
else
{
	print '{"success": "false"}';
}

?>