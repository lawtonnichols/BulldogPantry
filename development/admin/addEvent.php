<?php 

require_once("startSessionOrError.php"); 

function PrintLocationOptions()
{
	$mysqli = new mysqli("localhost", "root", "root");
	$mysqli->select_db("BulldogPantry");
	
	$result = $mysqli->query("select * from locations order by name");
	while ($row = $result->fetch_assoc())
	{
		$html = "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
		print $html;
	}
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<title>Add New Event</title>

<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<link href="//code.jquery.com/ui/jquery-ui-git.css" rel="stylesheet">
<script src="//code.jquery.com/ui/jquery-ui-git.js"></script>

<script>
function validate()
{
	var ret = true;

	// clear all warnings
	$("#EventTitleError").html("");
	$("#DateError").html("");
	$("#StartTimeError").html("");
	$("#NumberOfSpotsError").html("");
	$("#EventLocationError").html("");

	var f = document.AddEventForm;

	var eventTitle = f.EventTitle.value;
	var date = f.Date.value;
	var numberOfSpots = f.NumberOfSpots.value;
	//var eventLocation = f.EventLocation.value;
	
	if (eventTitle.length == 0)
	{
		$("#EventTitleError").html("<strong>Please enter an event title.</strong>");
		ret = false;
	}
	if (numberOfSpots.length == 0)
	{
		$("#NumberOfSpotsError").html("<strong>Please enter the number of spots.</strong>");
		ret = false;
	}
	/*if (eventLocation.length == 0)
	{
		$("#EventLocationError").html("<strong>Please enter the location of the event.</strong>");
		ret = false;
	}*/
	if (date.length == 0)
	{
		$("#DateError").html("<strong>Please enter a date.</strong>");
		return false; // we can't continue and compare times if we don't have a date
	}
	
	// we have a date to go off of, so we can check the times
	var startTimeHours = f.StartTimeHours.options[f.StartTimeHours.selectedIndex].value;
	var startTimeMinutes = f.StartTimeMinutes.options[f.StartTimeMinutes.selectedIndex].value;
	var startTimeAmPm = f.StartTimeAmPm.options[f.StartTimeAmPm.selectedIndex].value;
	var endTimeHours = f.EndTimeHours.options[f.EndTimeHours.selectedIndex].value;
	var endTimeMinutes = f.EndTimeMinutes.options[f.EndTimeMinutes.selectedIndex].value;
	var endTimeAmPm = f.EndTimeAmPm.options[f.EndTimeAmPm.selectedIndex].value;
	
	var startTime = new Date(date + " " + startTimeHours + ":" + startTimeMinutes + " " + startTimeAmPm);
	var endTime = new Date(date + " " + endTimeHours + ":" + endTimeMinutes + " " + endTimeAmPm);
	
	if (startTime.getTime() >= endTime.getTime())
	{
		$("#StartTimeError").html("<strong>The start time must be earlier than the end time.</strong>");
		ret = false;
	}
	
	return ret;
}

$(document).ready(function () {
	$("#datepicker").datepicker();
	
	$("#AddEventButton").click(function () {
		if (validate())
			document.AddEventForm.submit();
	});
	
	$("#CancelButton").click(function() {
		window.history.go(-1);
	});
});
</script>

<style>
body {background-color: #f5f5f5;}
textarea {width: 30em; height: 7em;}
#container {margin: 0 auto; width: 30em; padding-top: 2em;}
select {display: inline; width: auto;}
button.Add {width: 5em;}
p.text-right {padding-right: 1em; padding-top: .5em;}
</style>
</head>
<body>
<?php displayUsername(); ?>
<h1 class="text-center">Add New Event</h1>
<div id="container">
<form name="AddEventForm" method="post" action="executeAddEvent.php">
	<p>
		<label>Event Title:</label>
		<label id="EventTitleError" class="text-error">
		</label><input type="text" name="EventTitle">
	</p>
	<p>
		<label>Date:</label>
		<label id="DateError" class="text-error"></label> 
		<input type="text" id="datepicker" name="Date">
	</p>
	<p>
		<label>Location:</label>
		<select name="EventLocation">
			<?php PrintLocationOptions(); ?>
		</select>
	</p>
	<p>
		<label>Start Time:</label>
		<label id="StartTimeError" class="text-error"></label>
		<select name="StartTimeHours">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8" selected="selected">8</option>
			<option value="9">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12">12</option>
		</select> :
		<select name="StartTimeMinutes">
			<option value="00" selected="selected">00</option>
			<option value="05">05</option>
			<option value="10">10</option>
			<option value="15">15</option>
			<option value="20">20</option>
			<option value="25">25</option>
			<option value="30">30</option>
			<option value="35">35</option>
			<option value="40">40</option>
			<option value="45">45</option>
			<option value="50">50</option>
			<option value="55">55</option>
		</select>
		<select name="StartTimeAmPm">
			<option value="am">a.m.</option>
			<option value="pm">p.m.</option>
		</select>
	</p>
	<p><label>End Time:</label>
		<select name="EndTimeHours">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8" selected="selected">8</option>
			<option value="9">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12">12</option>
		</select> :
		<select name="EndTimeMinutes">
			<option value="00" selected="selected">00</option>
			<option value="05">05</option>
			<option value="10">10</option>
			<option value="15">15</option>
			<option value="20">20</option>
			<option value="25">25</option>
			<option value="30">30</option>
			<option value="35">35</option>
			<option value="40">40</option>
			<option value="45">45</option>
			<option value="50">50</option>
			<option value="55">55</option>
		</select>
		<select name="EndTimeAmPm">
			<option value="am">a.m.</option>
			<option value="pm">p.m.</option>
		</select>
	</p>
	<p><label>Event Description:</label> <textarea name="EventDescription"></textarea></p>
	<p>
		<label>Number of Spots:</label>
		<label id="NumberOfSpotsError" class="text-error"></label> 
		<input type="text" name="NumberOfSpots">
	</p>
	<p class="text-center">
		<button class="btn btn-large btn-success Add" id="AddEventButton" onclick="return false;">Add</button>
		<button class="btn btn-large btn-danger Cancel" id="CancelButton" onclick="return false;">Cancel</button>
	</p>
</form>
</div>
</body>
</html>