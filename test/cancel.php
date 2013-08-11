<?php 

if (!isset($_GET['EventID']) || !isset($_GET['EmailAddress']) || !isset($_GET['CancelCode']))
{
	header("Location: /error.html");
}

$mysqli = new mysqli("localhost", "root", "root");
$mysqli->select_db("BulldogPantry");

$eventID = $mysqli->real_escape_string($_GET['EventID']);
$emailAddress = $mysqli->real_escape_string($_GET['EmailAddress']);
$cancelCode = $mysqli->real_escape_string($_GET['CancelCode']);

$result = $mysqli->query("select * from volunteers where event_id = $eventID and email = '$emailAddress' and cancel_code = '$cancelCode'");

if ($result->num_rows == 0)
{
	header("Location: /error.html");
}
else
{
	$mysqli->query("delete from volunteers where event_id = $eventID and email = '$emailAddress' and cancel_code = '$cancelCode'");
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<title>Sign-Up Confirmation</title>

<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<link href="http://code.jquery.com/ui/jquery-ui-git.css" rel="stylesheet">
<script src="http://code.jquery.com/ui/jquery-ui-git.js"></script>

<script>

$(document).ready(function () {
	$("#CancelButton").click(function () {
		window.location = "/cancel.php?EventID=<?php print $eventID; ?>&EmailAddress=<?php print $emailAddress; ?>&CancelCode=<?php print $cancelCode; ?>";
	});
	
	$("#DoneButton").click(function () {
		window.location = "/";
	});
});

</script>

<style>
body {background-color: #f5f5f5; max-width: 44em; margin: 0 auto; padding-top: 3em;}
p {text-indent: 8%; text-align: justify;}
button {width: 5.5em;}
</style>
</head>
<body>
<h1 class="text-center">Cancellation Confirmation</h1>

<h3 class="lead text-center">You have successfully given up your volunteer spot.</h3>
</body>
</html>