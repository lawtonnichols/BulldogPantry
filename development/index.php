<?php

$numberOfResultsPerPage = 5;

if (!isset($_GET['page']))
{
	$page = 1;
}
else
{
	$page = $_GET['page'];
}

$mysqli = new mysqli("localhost", "root", "root");
$mysqli->select_db("BulldogPantry");

$numberOfRecords = $mysqli->query("select count(*) from events");
$numberOfRecords = $numberOfRecords->fetch_assoc()['count(*)'];

$offset = ($page - 1) * 5;
$result = $mysqli->query("select * from events order by event_start desc limit $offset, $numberOfResultsPerPage");
if ($result == false || ($result->num_rows == 0 && $page != 1))
{
	header("Location: /index.php");
	return;
}

$html = array();
while ($row = $result->fetch_assoc())
{
	$eventTitle = $row['event_title'];
	$eventStart = getdate(strtotime($row['event_start']));
	$eventEnd = getdate(strtotime($row['event_end']));
	$eventDescription = $row['event_description'];
	$numberOfSpots = $row['number_of_spots'];
	$eventID = $row['id'];
	
	$volunteers = $mysqli->query("select count(*) from volunteers where event_id = $eventID");
	$volunteersRow = $volunteers->fetch_array();
	$numberOfPeopleSignedUp = $volunteersRow[0];
	$numberOfSpotsLeft = $numberOfSpots - $numberOfPeopleSignedUp;
	
	$dateString = $eventStart['weekday'].',&nbsp;'.$eventStart['month'].'&nbsp;'.$eventStart['mday'].',&nbsp;'.$eventStart['year'].
				  '<br />'.timeString($eventStart).' to '.timeString($eventEnd);
	
	$html[] = "".
		"<tr id=\"$eventID\">".
			"<td><h4>$eventTitle</h4></td>".
			"<td>$dateString</td>".
			"<td>$eventDescription</td>".
			"<td class=\"SpotsLeft\">$numberOfSpotsLeft</td>".
		"</tr>";
}

function timeString($timeArray)
{
	$hours = $timeArray['hours'];
	$minutes = $timeArray['minutes'];
	
	if ($hours == 0)
	{
		$hours = 12;
		$ampm = "a.m.";
	}
	else if ($hours >= 12)
	{
		if ($hours > 12)
			$hours = $hours - 12;
		$ampm = "p.m.";
	}
	else
	{
		$ampm = "a.m.";
	}
	
	if ($minutes < 10)
		$minutes = '0'.$minutes;
	
	return $hours.':'.$minutes.' '.$ampm;
}

function canGoBack()
{
	global $page;
	
	if ($page == 1)
		print ' disabled="disabled"';
	
}

function canGoForward()
{
	global $page;
	global $numberOfRecords;
	global $numberOfResultsPerPage;
	
	if ($numberOfRecords - 1 < $page * $numberOfResultsPerPage)
	{
		print ' disabled="disabled"';
	}
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<title>Events — The Bulldog Pantry</title>

<link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>

<style>
body {background-color: #f5f5f5;}
table#EventTable {width: 65em; margin: 0 auto;}
table#EventTable tbody tr:hover {background-color: #e1f3ff !important;}
table#EventTable tbody tr:nth-child(odd) {background-color: rgb(249, 249, 249);}
th#TitleHeader {min-width: 10em;}
th#DateHeader {min-width: 5em;}
th#SpotsLeft {min-width: 5em;}
td.SpotsLeft {text-align: center; vertical-align: middle; font-size: x-large;}
p#SignUpP {padding-top: 1em; padding-bottom: .5em;}
p#ClickOnEventP {padding-top: 1em; display: none;}
p#ConfirmCancelP button {min-width: 5em;}
p#ConfirmCancelP input {font-size: large; height: 2em;}
h4 {padding-bottom: .74em;}
div#CompleteSignUp {display: none;}
.text-large {font-size: large;}
</style>

<script>
var state = "initial";
var page = 1;

$(document).ready(function () {
	$("p#SignUpP button").click(function () {
		$("p#SignUpP").hide("slow");
		$("p#ClickOnEventP").show("slow");
		$("html, body").animate({ scrollTop: 0 }, "slow");
		state = "signUpButtonClicked";
	});
	
	$("table#EventTable tbody tr").click(function () {
		if (state == "signUpButtonClicked") {
			var id = $(this).attr('id');
			var title = $(this).get(0).childNodes[0].childNodes[0].innerHTML;
			var date = $(this).get(0).childNodes[1].innerHTML;
			var spotsLeft = $(this).get(0).childNodes[3].innerHTML;
			if (parseInt(spotsLeft) == 0) {
				alert("This event already has enough volunteers.")
				return;
			}
			$("input#EmailAddress").val("");
			$("div#CompleteSignUp h4").get(0).innerHTML = title + ", " + date;
			$("div#CompleteSignUp input#EventID").val(id);
			$("p#ClickOnEventP").hide("slow");
			$("div#CompleteSignUp").show("slow");
			$("html, body").animate({ scrollTop: 0 }, "slow");
			state = "eventSelected";
		}
	});
	
	$("button.Cancel").click(function () {
		$("p#ClickOnEventP").hide("slow");
		$("div#CompleteSignUp").hide("slow");
		$("p#SignUpP").show("slow");
		state = "initial";
		$("html, body").animate({ scrollTop: 0 }, "slow");
	});
	
	$("button#Confirm").click(function () {
		var eventID = $("input#EventID").val();
		var email = $("input#EmailAddress").val();
		window.location = "/signup.php?EventID=" + eventID + "&EmailAddress=" + email;
	});
	
	$("button#PreviousPage").click(function () {
		window.location = window.location.pathname + "?page=" + (page - 1);
	});
	
	$("button#NextPage").click(function () {
		window.location = window.location.pathname + "?page=" + (page + 1);
	});
	
	
});
</script>

</head>
<body>
<h1 class="text-center">Events at the Bulldog Pantry</h1>



<p class="text-center" id="ClickOnEventP">
	<button class="btn btn-primary btn-large">Please click on the event,</button>
	<button class="btn btn-danger btn-large Cancel">or Cancel</button>
</p>

<div id="CompleteSignUp">
<h3 class="text-center">You are signing up for:</h3>
<h4 class="text-center">Lorem Ipsum Dolor, Saturday, May 5, 2013, 11:00 a.m. to 12:00 p.m.</h4>
<p class="text-center" id="ConfirmCancelP">
	<input type="text" class="input-xlarge search-query text-center" placeholder="Email Address" id="EmailAddress">
	<button class="btn btn-success btn-large" id="Confirm">Confirm</button>
	<button class="btn btn-danger btn-large Cancel">Cancel</button>
</p>
<input type="hidden" id="EventID">
</div>


<table class="table" id="EventTable">
	<thead>
		<th id="TitleHeader">Event Title</th><th id="DateHeader">Date</th><th>Event Description</th>
		<th id="SpotsLeft">Spots Left</th>
	</thead>
	<tbody>
		<?php foreach($html as $row) print $row . "\n"; ?>
	</tbody>
</table>

<p class="text-center" id="SignUpP"><button class="btn btn-primary btn-large">Sign up for one of these events</button></p>

<p class="text-center">
<button class="btn btn-large" id="PreviousPage"<?php canGoBack(); ?>><i class="icon-arrow-left text-large"></i></button>
<button class="btn btn-large" id="NextPage"<?php canGoForward(); ?>><i class="icon-arrow-right text-large"></i></button>
</p>
</body>
</html>