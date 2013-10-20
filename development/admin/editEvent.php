<?php
require_once("startSessionOrError.php");

if (!isset($_GET['id']))
{
	header("Location: /error.html");
	return;
}

$eventID = $_GET['id'];

$mysqli = new mysqli("localhost", "root", "root");
$mysqli->select_db("BulldogPantry");

$eventID = $mysqli->real_escape_string($eventID);
$result = $mysqli->query("select * from events where id = " . $eventID);
if ($result->num_rows == 0)
{
	header("Location: /error.html");
	return;
}

$row = $result->fetch_assoc();
$eventTitle = $row['event_title'];
$eventStart = date_parse($row['event_start']);
$eventEnd = date_parse($row['event_end']);
$eventLocation = $row['event_location'];
$eventDescription = $row['event_description'];
$numberOfSpots = $row['number_of_spots'];
$volunteerEmails = array();
$volunteerNames = array();
$volunteerIDs = array();
$emailAllVolunteersString = "";

$result = $mysqli->query("select * from volunteers where event_id = " . $eventID);
while ($row = $result->fetch_assoc())
{
	$volunteerEmails[] = $row['email'];
	$volunteerNames[] = $row['name'];
	$volunteerIDs[] = $row['id'];
	if (strlen($emailAllVolunteersString) > 0)
		$emailAllVolunteersString .= ";\n";
	$emailAllVolunteersString .= $row['email'];
}

function selectStartTimeHour($hour)
{
	global $eventStart;
	if ($eventStart['hour'] == $hour || $eventStart['hour'] - 12 == $hour || $eventStart['hour'] + 12 == $hour)
	{
		print ' selected="selected"';
	}
}

function selectStartTimeMinutes($minutes)
{
	global $eventStart;
	if ($eventStart['minute'] == $minutes)
	{
		print ' selected="selected"';
	}
}

function selectStartTimeAmPm($amOrPm)
{
	global $eventStart;
	switch ($amOrPm)
	{
		case "am":
			if ($eventStart['hour'] < 12)
			{
				print ' selected="selected"';
			}
			break;
		case "pm":
			if ($eventStart['hour'] >= 12)
			{
				print ' selected="selected"';
			}
			break;
	}
}

function selectEndTimeHour($hour)
{
	global $eventEnd;
	if ($eventEnd['hour'] == $hour || $eventEnd['hour'] - 12 == $hour || $eventEnd['hour'] + 12 == $hour)
	{
		print ' selected="selected"';
	}
}

function selectEndTimeMinutes($minutes)
{
	global $eventEnd;
	if ($eventEnd['minute'] == $minutes)
	{
		print ' selected="selected"';
	}
}

function selectEndTimeAmPm($amOrPm)
{
	global $eventEnd;
	switch ($amOrPm)
	{
		case "am":
			if ($eventEnd['hour'] < 12)
			{
				print ' selected="selected"';
			}
			break;
		case "pm":
			if ($eventEnd['hour'] >= 12)
			{
				print ' selected="selected"';
			}
			break;
	}
}

function printVolunteerEmails()
{
	global $volunteerEmails;
	global $volunteerNames;
	global $volunteerIDs;
	$output = "";
	for ($i = 0; $i < sizeof($volunteerEmails); $i++)
	{
		$name = $volunteerNames[$i];
		$email = $volunteerEmails[$i];
		$id = "v" . $volunteerIDs[$i];
		$output .= "<tr id='$id'>";
		$output .= "<td>$name</td><td>$email</td>";
		$output .= "</tr>";
	}
	print $output;
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<title>Edit Event</title>

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

	var f = document.EditEventForm;

	var eventTitle = f.EventTitle.value;
	var date = f.Date.value;
	var numberOfSpots = f.NumberOfSpots.value;
	var eventLocation = f.EventLocation.value;
	
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
	if (eventLocation.length == 0)
	{
		$("#EventLocationError").html("<strong>Please enter the location of the event.</strong>");
		ret = false;
	}
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

// state = editing, removing, or null
var state = null;

$(document).ready(function () {
	$("#datepicker").datepicker();
	
	$("#SaveButton").click(function () {
		if (validate())
			document.EditEventForm.submit();
	});
	
	$("#CancelButton").click(function() {
		window.history.go(-1);
	});
	
	$("#SendEmailButton").click(function() {
		$("#SendEmailPopup").show("slow");
	});
	
	$("#CloseSendEmailPopup").click(function () {
		$("#SendEmailPopup").hide("slow");
		return false;
	});
	
	$("#AddVolunteerButton").click(function () {
		$("#AddVolunteerPopup").show("slow");
	});
	
	$("#CloseAddVolunteerPopup").click(function () {
		$("#AddVolunteerPopup").hide("slow");
		return false;
	});
	
	$("#CloseEditVolunteerPopup").click(function () {
		$("#EditVolunteerPopup").hide("slow");
		return false;
	});
	
	$("#EditVolunteerButton").click(function () {
		state = "editing";
		$("#SelectVolunteerButton").html("Click on the Volunteer to Edit");
		$("#SelectVolunteerButton").show("fast");
		$("#CancelSelectVolunteerButton").show("fast");
	});
	
	$("#EditVolunteerSubmitButton").click(function () {
		var id = document.forms.EditVolunteer.id.value;
		var name = document.forms.EditVolunteer.Name.value;
		var email = document.forms.EditVolunteer.Email.value;
		var d = {"id": id, "name": name, "email": email};
		var jqxhr = $.ajax("ajaxEditVolunteer.php",
		  		{
			    	data: d,
					type: "POST",
					dataType: "json"
				}
				);
				
		jqxhr.done(function (data, textStatus, jqXHR) {
			// add to table of volunteers
			if (data.success == false)
			{
				alert("There was an error editing the volunteer; please try again.");
			}
			else
			{
				$("#v" + id).remove();
				document.getElementById("VolunteerTableBody").innerHTML += "<tr id='v" + id + "'><td>" + name + "</td><td>" + email + "</td></tr>";
			}
		});
		
		jqxhr.fail(function () {
			alert("There was an error editing the volunteer; please try again.");
		});
		$("#EditVolunteerPopup").hide("slow");
		return false;
	});
	
	$("#RemoveVolunteerButton").click(function () {
		state = "removing";
		$("#SelectVolunteerButton").html("Click on the Volunteer to Remove");
		$("#SelectVolunteerButton").show("fast");
		$("#CancelSelectVolunteerButton").show("fast");
	});
	
	$("#CancelSelectVolunteerButton").click(function () {
		state = null;
		$("#SelectVolunteerButton").hide("fast");
		$("#CancelSelectVolunteerButton").hide("fast");
	});
	
	$("#VolunteerTable").on("click", "tr", function() {
		//alert($(this).get(0).id);
		//alert(state);
		if (state == "removing")
		{
			var id = $(this).get(0).id.substr(1);
			var d = {"id": id};
			var jqxhr = $.ajax("ajaxRemoveVolunteer.php",
			  		{
				    	data: d,
						type: "POST",
						dataType: "json"
					}
					);
					
			jqxhr.done(function (data, textStatus, jqXHR) {
				// add to table of volunteers
				if (data.success == false)
				{
					alert("There was an error removing the volunteer; please try again.");
				}
				else
				{
					$("#v" + id).remove();
				}
			});
		
			jqxhr.fail(function () {
				alert("There was an error removing the volunteer; please try again.");
			});
			
			state = null;
			$("#SelectVolunteerButton").hide("fast");
			$("#CancelSelectVolunteerButton").hide("fast");
		}
		else if (state == "editing")
		{
			var id = $(this).get(0).id.substr(1);
			var name = $(this).get(0).childNodes[0].innerHTML;
			var email = $(this).get(0).childNodes[1].innerHTML;
			document.forms.EditVolunteer.id.value = id;
			document.forms.EditVolunteer.Name.value = name;
			document.forms.EditVolunteer.Email.value = email;
			$("#EditVolunteerPopup").show("slow");
			state = null;
			$("#SelectVolunteerButton").hide("fast");
			$("#CancelSelectVolunteerButton").hide("fast");
		}
	});
	
	$("#AddVolunteerSubmitButton").click(function () {
		var name = document.forms.AddVolunteer.Name.value;
		var email = document.forms.AddVolunteer.Email.value;
		var d = {"name": name, "email": email, "event": "<?php print $eventID; ?>"};
		var jqxhr = $.ajax("ajaxAddVolunteer.php",
			  		{
				    	data: d,
						type: "POST",
						dataType: "json"
					}
					);
					
		jqxhr.done(function (data, textStatus, jqXHR) {
			// add to table of volunteers
			if (data.success == false)
			{
				alert("There was an error adding the volunteer; please try again.");
			}
			else
			{
				var id = data.id;
				document.getElementById("VolunteerTableBody").innerHTML += "<tr id='v" + id + "'><td>" + name + "</td><td>" + email + "</td></tr>";
			}
			$("#AddVolunteerPopup").hide("slow");
		});
		
		jqxhr.fail(function () {
			alert("There was an error adding the volunteer; please try again.");
			$("#AddVolunteerPopup").hide("slow");
		});
	});
});
</script>

<style>
body {background-color: #f5f5f5;}
textarea {width: 30em; height: 7em;}
#container {margin: 0 auto; width: 30em; padding-top: 2em;}
select {display: inline; width: auto;}
button {min-width: 5.5em;}
p#EditCancelButtons {padding-top: 1em;}
td#icontd {padding-right: .5em;}
textarea#EmailAddresses {font-family: monospace; font-size: larger; width: 26em;}
p.text-right {padding-right: 1em; padding-top: .5em;}
#SendEmailPopup {position: fixed; width: 100%; top: 10%; display: none;}
#SendEmailPopupContainer {position: relative; margin: 0 auto; width: 30em; background-color: #fafafa; padding: 3em; border: thin solid #cccccc; border-radius: .5em;}
a#CloseSendEmailPopup {position: absolute; top: 0; right: 0; padding: .5em; font-size: 2em; color: black;}

#AddVolunteerPopup, #EditVolunteerPopup {position: fixed; width: 100%; top: 10%; display: none;}
#AddVolunteerPopupContainer, #EditVolunteerPopupContainer {position: relative; margin: 0 auto; width: 30em; background-color: #fafafa; padding: 3em; border: thin solid #cccccc; border-radius: .5em;}
a#CloseAddVolunteerPopup, a#CloseEditVolunteerPopup {position: absolute; top: 0; right: 0; padding: .5em; font-size: 2em; color: black;}
#AddVolunteerPopup label, #EditVolunteerPopup label {font-size: 1.5em;}
table#VolunteerTable tbody tr:nth-child(odd) {background-color: rgb(249, 249, 249);}
table#VolunteerTable tbody tr:hover {background: #e1f3ff;}
#SelectVolunteerButton {margin-right: .02em;}
#SelectVolunteerButton, #CancelSelectVolunteerButton {display: none;}
</style>
</head>
<body>
<?php displayUsername(); ?>
<h1 class="text-center">Edit Event</h1>
<div id="container">
<form name="EditEventForm" method="post" action="executeEditEvent.php">
	<input type="hidden" name="EventID" value="<?php print $eventID; ?>">
	<p>
		<label>Event Title:</label>
		<label id="EventTitleError" class="text-error"></label>
		<input type="text" name="EventTitle" value="<?php print $eventTitle; ?>">
	</p>
	<p>
		<label>Date:</label> 
		<label id="DateError" class="text-error"></label>
		<input type="text" id="datepicker" name="Date" value="<?php print $eventStart['month'].'/'.$eventStart['day'].'/'.$eventStart['year']; ?>">
	</p>
	<p>
		<label>Location:</label>
		<label id="EventLocationError" class="text-error"></label>
		<input type="text" name="EventLocation" value="<?php print $eventLocation; ?>">
	</p>
	<p>
		<label>Start Time:</label>
		<label id="StartTimeError" class="text-error"></label>
		<select name="StartTimeHours">
			<option  value="1"<?php selectStartTimeHour(1);  ?>>1</option>
			<option  value="2"<?php selectStartTimeHour(2);  ?>>2</option>
			<option  value="3"<?php selectStartTimeHour(3);  ?>>3</option>
			<option  value="4"<?php selectStartTimeHour(4);  ?>>4</option>
			<option  value="5"<?php selectStartTimeHour(5);  ?>>5</option>
			<option  value="6"<?php selectStartTimeHour(6);  ?>>6</option>
			<option  value="7"<?php selectStartTimeHour(7);  ?>>7</option>
			<option  value="8"<?php selectStartTimeHour(8);  ?>>8</option>
			<option  value="9"<?php selectStartTimeHour(9);  ?>>9</option>
			<option value="10"<?php selectStartTimeHour(10); ?>>10</option>
			<option value="11"<?php selectStartTimeHour(11); ?>>11</option>
			<option value="12"<?php selectStartTimeHour(12); ?>>12</option>
		</select> :
		<select name="StartTimeMinutes">
			<option value="00"<?php selectStartTimeMinutes(0);  ?>>00</option>
			<option value="05"<?php selectStartTimeMinutes(5);  ?>>05</option>
			<option value="10"<?php selectStartTimeMinutes(10); ?>>10</option>
			<option value="15"<?php selectStartTimeMinutes(15); ?>>15</option>
			<option value="20"<?php selectStartTimeMinutes(20); ?>>20</option>
			<option value="25"<?php selectStartTimeMinutes(25); ?>>25</option>
			<option value="30"<?php selectStartTimeMinutes(30); ?>>30</option>
			<option value="35"<?php selectStartTimeMinutes(35); ?>>35</option>
			<option value="40"<?php selectStartTimeMinutes(40); ?>>40</option>
			<option value="45"<?php selectStartTimeMinutes(45); ?>>45</option>
			<option value="50"<?php selectStartTimeMinutes(50); ?>>50</option>
			<option value="55"<?php selectStartTimeMinutes(55); ?>>55</option>
		</select>
		<select name="StartTimeAmPm">
			<option value="am"<?php selectStartTimeAmPm("am"); ?>>a.m.</option>
			<option value="pm"<?php selectStartTimeAmPm("pm"); ?>>p.m.</option>
		</select>
	</p>
	<p><label>End Time:</label>
		<select name="EndTimeHours">
			<option  value="1"<?php selectEndTimeHour(1);  ?>>1</option>
			<option  value="2"<?php selectEndTimeHour(2);  ?>>2</option>
			<option  value="3"<?php selectEndTimeHour(3);  ?>>3</option>
			<option  value="4"<?php selectEndTimeHour(4);  ?>>4</option>
			<option  value="5"<?php selectEndTimeHour(5);  ?>>5</option>
			<option  value="6"<?php selectEndTimeHour(6);  ?>>6</option>
			<option  value="7"<?php selectEndTimeHour(7);  ?>>7</option>
			<option  value="8"<?php selectEndTimeHour(8);  ?>>8</option>
			<option  value="9"<?php selectEndTimeHour(9);  ?>>9</option>
			<option value="10"<?php selectEndTimeHour(10); ?>>10</option>
			<option value="11"<?php selectEndTimeHour(11); ?>>11</option>
			<option value="12"<?php selectEndTimeHour(12); ?>>12</option>
		</select> :
		<select name="EndTimeMinutes">
			<option value="00"<?php selectEndTimeMinutes(0);  ?>>00</option>
			<option value="05"<?php selectEndTimeMinutes(5);  ?>>05</option>
			<option value="10"<?php selectEndTimeMinutes(10); ?>>10</option>
			<option value="15"<?php selectEndTimeMinutes(15); ?>>15</option>
			<option value="20"<?php selectEndTimeMinutes(20); ?>>20</option>
			<option value="25"<?php selectEndTimeMinutes(25); ?>>25</option>
			<option value="30"<?php selectEndTimeMinutes(30); ?>>30</option>
			<option value="35"<?php selectEndTimeMinutes(35); ?>>35</option>
			<option value="40"<?php selectEndTimeMinutes(40); ?>>40</option>
			<option value="45"<?php selectEndTimeMinutes(45); ?>>45</option>
			<option value="50"<?php selectEndTimeMinutes(50); ?>>50</option>
			<option value="55"<?php selectEndTimeMinutes(55); ?>>55</option>
		</select>
		<select name="EndTimeAmPm">
			<option value="am"<?php selectEndTimeAmPm("am"); ?>>a.m.</option>
			<option value="pm"<?php selectEndTimeAmPm("pm"); ?>>p.m.</option>
		</select>
	</p>
	<p><label>Event Description:</label> 
	   <textarea name="EventDescription"><?php print $eventDescription; ?></textarea>
	</p>
	<p>
		<label>Number of Spots:</label> 
		<label id="NumberOfSpotsError" class="text-error"></label>
		<input type="text" name="NumberOfSpots" value="<?php print $numberOfSpots; ?>">
	</p>
	<p>Spots Remaining: <?php print $numberOfSpots - count($volunteerEmails); ?></p>
	<p>Names and Email Addresses of Volunteers for this Event:</p>
		<p class="text-center">
			<button class="btn btn-primary" onclick="return false;" id="SelectVolunteerButton">Select the Volunteer to Edit</button>
			<button class="btn btn-danger" onclick="return false;" id="CancelSelectVolunteerButton">or Cancel</button>
		</p>
		<table id="VolunteerTable" class="table">
			<thead><tr><th>Name</th><th>Email</th></tr></thead>
			<tbody id="VolunteerTableBody"><?php printVolunteerEmails(); ?><tbody>
		</table>
		<p class="text-center">
			<button id="AddVolunteerButton" class="btn btn-success" onclick="return false;">Add a Volunteer</button>
			<button id="EditVolunteerButton" class="btn btn-warning" onclick="return false;">Edit a Volunteer</button>
			<button id="RemoveVolunteerButton" class="btn btn-danger" onclick="return false;">Remove a Volunteer</button>
		</p>
	<!--<table><tr valign="top"><td id="icontd"><i class="icon-star"></i></td>
		<td><p class="muted">To manually add or remove a volunteer, add an email to a new line, or remove an email from an existing one.</p></td>
	</tr></table>-->
	<p class="text-center">
		<button class="btn btn-info" id="SendEmailButton" onclick="return false;">
			<i class="icon-envelope icon-white"></i> Send an Email to All Volunteers
		</button>
	</p>
	<p class="text-center" id="EditCancelButtons">
		<button class="btn btn-large btn-primary Save" id="SaveButton" onclick="return false;">Save</button>
		<button class="btn btn-large btn-danger Cancel" id="CancelButton" onclick="return false;">Cancel</button>
	</p>
</form>
</div>
<div id="SendEmailPopup">
	<div id="SendEmailPopupContainer">
		<a id="CloseSendEmailPopup" href="#">×</a>
		<p>Below are the addresses of the volunteers in a format suitable for any email application. Please copy the entire text below and paste it all into the "To" field of a new email message.</p>
		<textarea id="EmailAddressesToCopy"><?php print $emailAllVolunteersString; ?></textarea>
	</div>
</div>
<div id="AddVolunteerPopup">
	<div id="AddVolunteerPopupContainer">
		<a id="CloseAddVolunteerPopup" href="#">×</a>
		<form name="AddVolunteer">
			<p>
				<label>Name:</label>
				<input type="text" name="Name"/>
			</p>
			<p>
				<label>Email:</label>
				<input type="text" name="Email"/>
			</p>
			<p class="text-center">
				<button id="AddVolunteerSubmitButton" class="btn btn-large btn-success" onclick="return false;">Add</button>
			</p>
		</form>
	</div>
</div>
<div id="EditVolunteerPopup">
	<div id="EditVolunteerPopupContainer">
		<a id="CloseEditVolunteerPopup" href="#">×</a>
		<form name="EditVolunteer">
			<input type="hidden" name="id" value="" />
			<p>
				<label>Name:</label>
				<input type="text" name="Name"/>
			</p>
			<p>
				<label>Email:</label>
				<input type="text" name="Email"/>
			</p>
			<p class="text-center">
				<button id="EditVolunteerSubmitButton" class="btn btn-large btn-success" onclick="return false;">Edit</button>
			</p>
		</form>
	</div>
</div>
</body>
</html>