<?php

require_once("startSessionOrError.php");

if (!isset($_POST['id']))
{
	header("Location: /error.html");
	return;
}

$mysqli = new mysqli("localhost", "root", "root");
$mysqli->select_db("BulldogPantry");

$id = $mysqli->real_escape_string($_POST['id']);

$mysqli->query("delete from volunteers where id = '$id'");

if ($mysqli->affected_rows > 0)
{
	$output = '{"success": "true"}';
	print $output;
}
else
{
	print '{"success": "false"}';
}

?>