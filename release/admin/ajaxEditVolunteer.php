<?php

require_once("startSessionOrError.php");

if (!isset($_POST['id']) || !isset($_POST['name']) || !isset($_POST['email']))
{
	header("Location: /error.html");
	return;
}

$mysqli = new mysqli("localhost", "root", "root");
$mysqli->select_db("BulldogPantry");

$name = $mysqli->real_escape_string($_POST['name']);
$emailAddress = $mysqli->real_escape_string($_POST['email']);
$id = $mysqli->real_escape_string($_POST['id']);

$mysqli->query("update volunteers set email='$emailAddress', name='$name' where id='$id'");

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