<?php

require_once("startSessionOrError.php");

// from http://derek.io/blog/2009/php-a-better-random-string-function/
function generateSalt($length)
{
	return substr(MD5(microtime()), 0, $length);
}

function sha512PasswordHash($password, $salt)
{
	return hash("sha512", $password . $salt);
}

// if we have been sent a form and something is set
if (isset($_POST['OriginalPassword']) || isset($_POST['NewPassword']) || isset($_POST['ConfirmNewPassword']))
{
	if (isset($_POST['OriginalPassword']))
		$OriginalPassword = $_POST['OriginalPassword'];
	else
		$OriginalPassword = '';
	if (isset($_POST['NewPassword']))
		$NewPassword = $_POST['NewPassword'];
	else
		$NewPassword = '';
	if (isset($_POST['ConfirmNewPassword']))
		$ConfirmNewPassword = $_POST['ConfirmNewPassword'];
	else
		$ConfirmNewPassword = '';

	$mysqli = new mysqli("localhost", "root", "root");
	$mysqli->select_db("BulldogPantry");
	
	// $username is already assigned in startSessionOrError.php

	$result = $mysqli->query("select * from users where username = '$username'");
	if ($result->num_rows == 0)
	{
		$error = true;
		$errorText = "Incorrect username."; // this should hopefully never happen
	}
	else
	{
		$row = $result->fetch_array();
		$salt = $row['salt'];
		$hashToTest = sha512PasswordHash($OriginalPassword, $salt);
		
		if ($row['password_hash'] != $hashToTest)
		{
			// the original password doesn't match
			$error = true;
			$errorText = "Incorrect password.";
		}
		else
		{
			if ($NewPassword != $ConfirmNewPassword)
			{
				// the new passwords don't match
				$error = true;
				$errorText = "New passwords don't match.";
			}
			else
			{
				$error = false;
				
				$newSalt = generateSalt(11);
				$newPasswordHash = sha512PasswordHash($NewPassword, $newSalt);
				$query = "update users set salt = '$newSalt', password_hash = '$newPasswordHash' ".
						 "where username = '$username'";
				$mysqli->query($query);
			}
		}
	}
}

function printError()
{
	global $error;
	global $errorText;
	if (isset($error) && $error == true)
	{
		if (isset($errorText))
		{
			print "<strong>" . $errorText . "</strong>";
		}
	}
}

function printSuccess()
{
	global $error;
	if (isset($error) && $error == false)
	{
		print "<strong>Password updated successfully.</strong>";
	}
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<title>Change Password</title>

<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<link href="http://code.jquery.com/ui/jquery-ui-git.css" rel="stylesheet">
<script src="http://code.jquery.com/ui/jquery-ui-git.js"></script>

<script>
$(document).ready(function () {
	$("input:submit").click(function () {
		if ($("#NewPassword").val() === $("#ConfirmNewPassword").val() && $("#NewPassword").val().length > 0
			&& $("#NewPassword").val() !== $("#OriginalPassword").val())
			return true;
		else
			return false;
	});
	
	$("input").keyup(function () {
		if ($("#NewPassword").val() === $("#ConfirmNewPassword").val() && $("#NewPassword").val().length > 0
				 && $("#NewPassword").val() === $("#OriginalPassword").val()) {
			$("#SubmitButton").val("New password same as old password");
			if ($("#SubmitButton").hasClass("btn-success")) {
				$("#SubmitButton").removeClass("btn-success");
				$("#SubmitButton").addClass("btn-primary");
			}
		}
		else if ($("#NewPassword").val() === $("#ConfirmNewPassword").val() && $("#NewPassword").val().length > 0) {
			$("#SubmitButton").val("Change Password");
			if ($("#SubmitButton").hasClass("btn-primary")) {
				$("#SubmitButton").removeClass("btn-primary");
				$("#SubmitButton").addClass("btn-success");
			}
		}
		else {
			$("#SubmitButton").val("Passwords do not match.");
			if ($("#SubmitButton").hasClass("btn-success")) {
				$("#SubmitButton").removeClass("btn-success");
				$("#SubmitButton").addClass("btn-primary");
			}
		}
	});
	
	
});
</script>

<style>
body {background-color: #f5f5f5;}
input[type="password"] {font-size: x-large; height: auto;}
h1 {padding-bottom: .5em;}
input[type="submit"] {min-width: 16.25em;}
p.text-right {padding-right: 1em; padding-top: .5em;}
</style>
</head>
<body>
<?php displayUsername(); ?>
<h1 class="text-center">Change Password</h1>

<form method="post" action="changePassword.php">
<label class="text-error text-center"><?php printError(); ?></label>
<label class="text-success text-center"><?php printSuccess(); ?></label>
<p class="text-center"><input type="password" id="OriginalPassword" name="OriginalPassword" placeholder="Current Password" class="input-xlarge text-center"></p>
<p class="text-center"><input type="password" id="NewPassword" name="NewPassword" placeholder="New Password" class="input-xlarge text-center NewPasswords"></p>
<p class="text-center"><input type="password" id="ConfirmNewPassword" name="ConfirmNewPassword" placeholder="Confirm New Password" class="input-xlarge text-center NewPasswords"></p>
<p class="text-center"><input type="submit" id="SubmitButton" class="btn btn-large btn-primary" value="Change Password"></p>
</form>
</body>
</html>