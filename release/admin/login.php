<?php

session_start();

if (isset($_SESSION['username']))
{
	// redirect away from the login page if the user is already authenticated
	header("Location: /admin/");
}
else
{
	session_destroy();
}

function sha512PasswordHash($password, $salt)
{
	return hash("sha512", $password . $salt);
}

// if we have been sent a form and something is set
if (isset($_POST['username']) || isset($_POST['password']))
{
	$mysqli = new mysqli("localhost", "root", "root");
	$mysqli->select_db("BulldogPantry");
	
	$username = $mysqli->real_escape_string($_POST['username']);
	$password = $_POST['password'];

	$result = $mysqli->query("select * from users where username = '$username'");
	if ($result->num_rows == 0)
	{
		$error = true;
	}
	else
	{
		$row = $result->fetch_array();
		$salt = $row['salt'];
		$hashToTest = sha512PasswordHash($password, $salt);
		
		if ($row['password_hash'] != $hashToTest)
		{
			$error = true;
		}
		else
		{
			$error = false;
		}
	}
	
	if (!$error)
	{
		// user is authenticated; let's begin a session
		session_start();
		
		$_SESSION['username'] = $username;
		
		// send the user to the admin index
		header("Location: /admin/");
	}
}

function printError()
{
	global $error;
	if (isset($error) && $error == true)
	{
		print "<strong>Username or Password is incorrect.</strong>";
	}
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<title>Login</title>
<style>
body {background-color: #f5f5f5 !important;}
#login {width: 29em; margin: 0 auto; position: relative; top: 5em;}
.form-signin input[type="text"],
.form-signin input[type="password"] {font-size: 1.5em; padding: .35em; height: auto;}
.form-signin {padding: 1em;}
</style>

<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
</head>
<body>
<div id="login">
<form method="post" action="login.php" class="form-signin">
<h2 class="form-signin-heading text-center">Bulldog Pantry Login</h2>
<label class="text-error text-center"><?php printError(); ?></label>
<input type="text" name="username" placeholder="Username" class="input-block-level text-center">
<input type="password" name="password" placeholder="Password" class="input-block-level text-center">
<p class="text-center"><input type="submit" value="Login" class="btn btn-large btn-success btn-block"></p>
</form>
</div>
</body>
</html>