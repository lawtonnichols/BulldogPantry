<?php

$mysqli = new mysqli("localhost", "root", "root");

$shouldLogOut = false;

if (doesTheDatabaseExist($mysqli))
{
	require_once("startSessionOrError.php");
	$shouldLogOut = true;
}
// if the database doesn't exist, then we should be allowed to reset it
// in order to create it for the first time

error_reporting(E_ALL);

// from http://stackoverflow.com/questions/6023363/checking-if-a-database-exists-mysql-php
function doesTheDatabaseExist($mysqli)
{
	// statement to execute
	$sql = 'SELECT COUNT(*) AS `exists` FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME="BulldogPantry"';
	
	// execute the statement
	$query = $mysqli->query($sql);
	if ($query === false) {
	    throw new Exception($mysqli->error, $mysqli->errno);
	}
	
	// extract the value
	$row = $query->fetch_object();
	$dbExists = (bool) $row->exists;
	return $dbExists;
}

// from http://derek.io/blog/2009/php-a-better-random-string-function/
function generateSalt($length)
{
	return substr(MD5(microtime()), 0, $length);
}

function sha512PasswordHash($password, $salt)
{
	return hash("sha512", $password . $salt);
}

function createAdminUser($mysqli)
{
	$salt = generateSalt(11);
	$passwordHash = sha512PasswordHash("BulldogPantry!", $salt);
	$query = "insert into users (username, password_hash, salt) " .
			 "values ('BulldogPantryAdmin', '$passwordHash', '$salt')";
	$mysqli->query($query);
}

if (doesTheDatabaseExist($mysqli))
{
	$mysqli->query("drop database BulldogPantry");
}

$mysqli->query("create database BulldogPantry");
$mysqli->select_db("BulldogPantry");

$query = <<<END
	create table users (
		id int not null auto_increment primary key,
		username varchar(100) not null,
		password_hash varchar(512) not null,
		salt varchar(25) not null,
		index(username)
	);
	
	create table locations (
		id int not null auto_increment primary key,
		name varchar(256) not null,
		position varchar(512) not null,
		index(name), index(position)
	);
	
	create table events (
		id int not null auto_increment primary key,
		event_title varchar(150) not null,
		event_start datetime not null,
		event_end datetime not null,
		event_description varchar(500),
		event_location_id int not null,
		number_of_spots int not null,
		foreign key(event_location_id) references locations(id) on delete cascade,
		index(event_start)
	);
	
	create table volunteers (
		id int not null auto_increment primary key,
		email varchar(256) not null,
		name varchar(100) not null,
		event_id int not null,
		cancel_code varchar(11) not null,
		foreign key(event_id) references events(id) on delete cascade,
		index(email), index(event_id)
	);
END;

$result = $mysqli->multi_query($query); // create the database and all the tables

// clear the results of the multi_query, from http://php.net/mysqli_multi_query
while ($mysqli->more_results() && $mysqli->next_result());

createAdminUser($mysqli);

if ($shouldLogOut)
{
	unset($_SESSION['username']);
	session_destroy();
}

header("Location: /admin/databaseReset.html");
?>