<?php

if (isset($_GET['id']))
{
	$eventID = $_GET['id'];
	
	$mysqli = new mysqli("localhost", "root", "root");
	$mysqli->select_db("BulldogPantry");
	$mysqli->query("delete from events where id = $eventID");
}

if (isset($_SERVER['HTTP_REFERER']))
{
	$referrer = $_SERVER['HTTP_REFERER'];
	header("Location: $referrer");
}
else
{
	header("Location: /viewEvents.php");
}




?>