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
$result = $mysqli->query("select * from events limit $offset, $numberOfResultsPerPage");
if ($result->num_rows == 0 && $page != 1)
{
	header("Location: /viewEvents.php");
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
				  ' '.timeString($eventStart).' to '.timeString($eventEnd);
	
	$html[] = "".
		"<tr id=\"$eventID\">".
			"<td><h4>$eventTitle</h4></td>".
			"<td>$dateString</td>".
			"<td>$eventDescription</td>".
			"<td class=\"td-center\">$numberOfSpots</td>".
			"<td class=\"SpotsLeft td-center\">$numberOfSpotsLeft</td>".
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
th#TitleHeader {min-width: 10em;}
th#DateHeader {min-width: 5em;}
th#SpotsLeft {min-width: 5em;}
div#EditDeleteButtons button {min-width: 5em;}
div#EditDeleteButtons {display: none; position: absolute; width: 100%;}
td.td-center {text-align: center;}
table#EventTable tbody tr {height: 10em; overflow: scroll;}
table#EventTable tbody td {vertical-align: middle;}
p#BackForwardButtons {padding-top: 1em;}
</style>

<script>
var page = 1;
var timer;
var selectedID;

function removeButtons()
{
	$("#EditDeleteButtons").fadeOut("slow");
}

$(document).ready(function () {
	$("tbody tr").mousemove(function() {
		$("#EditDeleteButtons").stop(true, true).show();
		//$("#EditDeleteButtons").fadeOut("fast");
		selectedID = $(this).attr('id');
		var y = $(this).offset().top;
		var height = $(this).height();
		var pheight = $("#EditDeleteButtons button").height();
		var paddingTop = height / 2 - pheight;
		$("#EditDeleteButtons").css("top", y).css("left", 0).css("height", height + "px");
		$("#EditDeleteButtons p").css("padding-top", paddingTop + "px");
		//$("#EditDeleteButtons").stop(true, true).show("slow");
		//$("#EditDeleteButtons").fadeIn("fast");
		clearTimeout(timer);
		timer = setTimeout(removeButtons, 1500);
	});
	
	$("#EditDeleteButtons button").mouseover(function () {
		clearTimeout(timer);
		$("#EditDeleteButtons").stop(true, true).show();
	});
	
	$("#EditDeleteButtons button").mouseout(function () {
		timer = setTimeout(removeButtons, 1500);
	});
	
	$("button.Edit").click(function () {
		window.location = "/editEvent.php?id=" + selectedID;
	});
	
	$("button.Delete").click(function () {
		if (confirm("Please confirm that you would like to delete this entry.")) {
			window.location = "/delete.php?id=" + selectedID;
		}
	});
	
	$("button#PreviousPage").click(function () {
		window.location = window.location.pathname + "?page=" + (page - 1);
	});
	
	$("button#NextPage").click(function () {
		window.location = window.location.pathname + "?page=" + (page + 1);
	});
	
	$("button#AddNewEvent").click(function () {
		window.location = "/addEvent.php";
	});
	
	
	
});
</script>

</head>
<body>
<h1 class="text-center">Current Events</h1>

<p class="text-center">
	<button class="btn btn-primary" id="AddNewEvent"><i class="icon-plus icon-white"></i> Add New Event</button>
</p>

<table class="table table-striped" id="EventTable">
	<thead>
		<tr>
			<th id="TitleHeader">Event Title</th><th id="DateHeader">Date</th><th>Event Description</th>
			<th>Spots</th><th id="SpotsLeft">Spots Left</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($html as $row) print $row . "\n"; ?>
	</tbody>
</table>

<div id="EditDeleteButtons">
<p class="text-center">
	<button class="btn btn-info btn-large Edit">Edit</button>
	<button class="btn btn-danger btn-large Delete">Delete</button>
</p>
</div>

<p class="text-center" id="BackForwardButtons">
<button class="btn btn-large" id="PreviousPage"<?php canGoBack(); ?>><i class="icon-arrow-left text-large"></i></button>
<button class="btn btn-large" id="NextPage"<?php canGoForward(); ?>><i class="icon-arrow-right text-large"></i></button>
</p>
</body>
</html>