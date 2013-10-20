<?php

require_once("startSessionOrError.php");

if (!isset($_POST['request_type']))
{
	header("Location: /error.html");
	return;
}

$mysqli = new mysqli("localhost", "root", "root");
$mysqli->select_db("BulldogPantry");

$request_type = $mysqli->real_escape_string($_POST['request_type']);
if ($request_type == "add")
{
	$name = $mysqli->real_escape_string($_POST['name']);
	$position = $mysqli->real_escape_string($_POST['position']);
	$query = "insert into locations (name, position) values ('$name', '$position')";
	$mysqli->query($query);
	
	$result = $mysqli->query("select * from locations where name = '$name' and position = '$position'");
	
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
}
else if ($request_type == "edit")
{
	$id = $mysqli->real_escape_string($_POST['id']);
	$query = "";
	if (isset($_POST['name']))
	{
		$name = $mysqli->real_escape_string($_POST['name']);
		$query = "update locations set name='$name' where id=$id";
	}
	else if (isset($_POST['position']))
	{
		$position = $mysqli->real_escape_string($_POST['position']);
		$query = "update locations set position='$position' where id=$id";
	}
	$mysqli->query($query);
	if ($mysqli->affected_rows > 0)
	{
		$output = '{"success": "true"}';
		print $output;
	}
	else
	{
		print '{"success": "false"}';
	}
}
else if ($request_type == "remove")
{
	$id = $mysqli->real_escape_string($_POST['id']);
	$query = "delete from locations where id=$id";
	$mysqli->query($query);
	if ($mysqli->affected_rows > 0)
	{
		$output = '{"success": "true"}';
		print $output;
	}
	else
	{
		print '{"success": "false"}';
	}
}

?>