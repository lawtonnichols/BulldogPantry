<?php

require_once("startSessionOrError.php");

if (isset($_GET['id']))
{
	$eventID = $_GET['id'];
	
	$mysqli = new mysqli("localhost", "root", "root");
	$mysqli->select_db("BulldogPantry");
	$eventID = $mysqli->real_escape_string($eventID);
	$mysqli->query("delete from events where id = $eventID");
}

if (isset($_SERVER['HTTP_REFERER']))
{
	$referrer = $_SERVER['HTTP_REFERER'];
	header("Location: $referrer");
}
else
{
	header("Location: /admin/viewEvents.php");
}




?>