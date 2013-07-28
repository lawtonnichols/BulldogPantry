<?php
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
	print $salt . "\n";
	$passwordHash = sha512PasswordHash("BulldogPantry!", $salt);
	print $passwordHash . "\n";
	$query = "insert into users (username, password_hash, salt) " .
			 "values ('BulldogPantryAdmin', '$passwordHash', '$salt')";
	print $query;
	$mysqli->query($query);
}

$mysqli = new mysqli("localhost", "root", "root");

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
	
	create table events (
		id int not null auto_increment primary key,
		event_title varchar(150) not null,
		event_start datetime not null,
		event_end datetime not null,
		event_description varchar(500),
		number_of_spots int not null,
		index(event_start)
	);
	
	create table volunteers (
		id int not null auto_increment primary key,
		email varchar(256) not null,
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
?>