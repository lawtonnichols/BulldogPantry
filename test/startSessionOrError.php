<?php

session_start();

if (!isset($_SESSION['username']))
{
	// redirect to the login page if the user is not authenticated
	session_destroy();
	header("Location: /login.php");
}

$username = $_SESSION['username'];

function displayUsername()
{
	global $username;
	print "<p class='text-right'>You are logged in as <strong>$username</strong>. <a href='adminIndex.php'>Home</a> | <a href='logout.php'>Logout</a></p>";
}

?>