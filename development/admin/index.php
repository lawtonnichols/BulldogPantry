<?php

require_once("startSessionOrError.php");

?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<title>Bulldog Pantry Administration</title>

<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<link href="//code.jquery.com/ui/jquery-ui-git.css" rel="stylesheet">
<script src="//code.jquery.com/ui/jquery-ui-git.js"></script>

<script>
$(document).ready(function () {
	$("a.super-dangerous").click(function () {
		if ($(this).hasClass("btn-danger")) {
			$(this).removeClass("btn-danger");
			$(this).addClass("btn-inverse");
			$(this).html("Are you sure?");
			return false;
		}
		else if ($(this).hasClass("btn-inverse")) {
			$(this).removeClass("btn-inverse");
			$(this).html("Are you <strong>really</strong> sure?");
			return false;
		}
		else {
			return true;
		}
	});
	
	
});
</script>

<style>
h1 {padding-top: .5em;}
body {background-color: #f5f5f5;}
a {min-width: 11em;}
p#First {padding-top: 1em;}
p.text-right {padding-right: 1em; padding-top: .5em;}
</style>
</head>
<body>
<?php displayUsername(); ?>
<h1 class="text-center">Bulldog Pantry Administration</h1>
<p class="text-center" id="First"><a href="viewEvents.php" class="btn btn-primary btn-large">Manage Events</a></p>
<p class="text-center"><a href="manageLocations.php" class="btn btn-primary btn-large">Manage Locations</a></p>
<p class="text-center"><a href="changePassword.php" class="btn btn-info btn-large">Change Password</a></p>
<p class="text-center"><a href="deleteOldEvents.php" class="btn btn-warning btn-large">Delete Old Events</a></p>
<p class="text-center"><a href="resetDB.php" class="btn btn-danger btn-large super-dangerous">Reset the Database</a></p>
</body>
</html>