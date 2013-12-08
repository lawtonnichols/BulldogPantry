<?php

require_once("startSessionOrError.php");

// process our post request if there was one
if (isset($_POST['Date']) && strlen($_POST['Date']) > 0)
{
	$date = $_POST['Date'];
	
	$mysqli = new mysqli("localhost", "root", "root");
	$mysqli->select_db("BulldogPantry");
	
	$dateArray = preg_split("/\//", $date);
	
	$month = $dateArray[0];
	$day = $dateArray[1];
	$year = $dateArray[2];
	
	if ($month < 10)
		$month = "0" . $month;
	if ($day < 10)
		$day = "0" . $day;
	
	$formattedDate = $year.'-'.$month.'-'.$day.' 23:59:59';

	$result = $mysqli->query("delete from events where event_start <= '$formattedDate'");
	
	header("Location: /admin/");
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<title>Delete Old Events</title>

<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<link href="//code.jquery.com/ui/jquery-ui-git.css" rel="stylesheet">
<script src="//code.jquery.com/ui/jquery-ui-git.js"></script>

<script>
$(document).ready(function () {

	$("input#Date").datepicker();
	
	$("input:submit.dangerous").click(function () {
		if ($(this).hasClass("btn-danger")) {
			$(this).removeClass("btn-danger");
			$(this).addClass("btn-warning");
			$(this).val("Are you sure?");
			return false;
		}
		else {
			if ($("input#Date").val().length == 0)
			{
				$("#ErrorText").html("<strong>Please enter a date.</strong>");
				$(this).val("Delete");
				$(this).removeClass("btn-warning");
				$(this).addClass("btn-danger");
				return false;
			}
			else
			{
				return true;
			}
		}
	});
	
});
</script>

<style>
body {background-color: #f5f5f5;}
p.text-right {padding-right: 1em; padding-top: .5em;}
label.instructions, input[type="text"] {font-size: 1.5em;}
input[type="text"] {height: auto; padding: .25em 0;}
h1 {padding-bottom: .25em; padding-top: .5em;}
</style>
</head>
<body>
<?php displayUsername(); ?>
<h1 class="text-center">Delete Old Events</h1>

<form method="post" action="deleteOldEvents.php">
<label class="text-center instructions">Delete all events that occur on or before:</label>
<label class="text-center text-error" id="ErrorText"></label>
<p class="text-center"><input type="text" id="Date" name="Date" placeholder="M/D/YYYY" class="input-xlarge text-center"></p>
<p class="text-center"><input type="submit" id="SubmitButton" class="btn btn-large btn-danger dangerous" value="Delete"></p>
</form>
</body>
</html>