<?php 

require_once("startSessionOrError.php"); 

?>
<!doctype html>
<html>
<head>
<meta charset="utf8">
<title>Locations</title>

<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
<link href="//code.jquery.com/ui/jquery-ui-git.css" rel="stylesheet">
<script src="//code.jquery.com/ui/jquery-ui-git.js"></script>
<script src="//maps.googleapis.com/maps/api/js?v=3&sensor=false&libraries=places"></script>

<script>
var lastPosition = null;

// from https://developers.google.com/maps/documentation/javascript/examples/event-simple
function initialize() {
  var markers = [];

  var mapOptions = {
    zoom: 15,
    center: new google.maps.LatLng(36.810828, -119.746223),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };

  var map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);

  var mainMarker = new google.maps.Marker({
    position: map.getCenter(),
    map: map,
    title: 'Location',
    zIndex: 10000
  });
  
  lastPosition = mainMarker.getPosition();

  var input = document.getElementById('target');
  var searchBox = new google.maps.places.SearchBox(input);

  /*google.maps.event.addListener(map, 'center_changed', function() {
    // 3 seconds after the center of the map has changed, pan back to the
    // marker.
    window.setTimeout(function() {
      map.panTo(marker.getPosition());
    }, 3000);
  });*/

  // [START region_getplaces]
  // Listen for the event fired when the user selects an item from the
  // pick list. Retrieve the matching places for that item.
  google.maps.event.addListener(searchBox, 'places_changed', function() {
    var places = searchBox.getPlaces();

    for (var i = 0, marker; marker = markers[i]; i++) {
      marker.setMap(null);
    }

    // For each place, get the icon, place name, and location.
    markers = [];
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0, place; place = places[i]; i++) {
      var image = {
        url: place.icon,
        size: new google.maps.Size(71, 71),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(25, 25)
      };

      // Create a marker for each place.
      var marker = new google.maps.Marker({
        map: map,
        icon: image,
        title: place.name,
        position: place.geometry.location
      });

      google.maps.event.addListener(marker, 'click', function() {
  		mainMarker.setPosition(this.getPosition());
  		lastPosition = this.getPosition();
  	  });

      markers.push(marker);

      bounds.extend(place.geometry.location);
    }

    map.fitBounds(bounds);
  });
  // [END region_getplaces]

  google.maps.event.addListener(map, 'click', function(event) {
  	mainMarker.setPosition(event.latLng);
  	lastPosition = event.latLng;
  });

  google.maps.event.addListener(map, 'bounds_changed', function() {
    var bounds = map.getBounds();
    searchBox.setBounds(bounds);
  });
}

google.maps.event.addDomListener(window, 'load', initialize);

$(document).ready(function () {
	$("#AddCurrentLocation").click(function () {
		if (lastPosition != null)
		{
			var name = prompt("Please enter the name you would like to give this location.");
			if (name.length > 0)
			{
				AddLocation(name, lastPosition);
			}
		}
	});
	
	$("#LocationTable").on("click", ".ChangeName", function () {
		var newName = prompt("Enter a new name.");
		if (newName.length > 0)
		{
			var childToEdit = $(this).get(0).parentNode.parentNode.childNodes[0];
			childToEdit.innerHTML = newName;
		}
	});
	
	$("#LocationTable").on("click", ".ChangeLocation", function () {
		alert("Please select a new location, and then press the 'Update Location' button.");
		$(this).html("Update Location");
		$(this).removeClass("ChangeLocation")
		$(this).addClass("UpdateLocation");
		$(this).removeClass("btn-warning");
		$(this).addClass("btn-success");
	});
	
	$("#LocationTable").on("click", ".UpdateLocation", function () {
		var tr = $(this).get(0).parentNode.parentNode;
		tr.className = "position=" + lastPosition;
		$(this).html("Change Location");
		$(this).removeClass("UpdateLocation");
		$(this).addClass("ChangeLocation");
		$(this).removeClass("btn-success");
		$(this).addClass("btn-warning");
	});
	
	$("#LocationTable").on("click", ".Remove", function () {
		var childToRemove = $(this).get(0).parentNode.parentNode;
		$("#LocationTable").get(0).removeChild(childToRemove);
	});
});

function AddLocation(name, position)
{
	var html = "<tr class='position=" + position + "'><td style='width: 100%'>" + name + "</td>" +
				"<td><button class='btn btn-warning ChangeName' style='min-width: 9em;'>Change Name</button></td>" + 
				"<td><button class='btn btn-warning ChangeLocation' style='min-width: 10em;'>Change Location</button></td>" + 
				"<td><button class='btn btn-danger Remove' style='min-width: 5em;'>Remove</button></td></tr>";
	$("#LocationTable").get(0).innerHTML += html;
}
</script>

<style>
body {background-color: #f5f5f5;}
html { height: 100% }
body { height: 100%; margin: 0; padding: 0; }
.google-maps#map-canvas { height: 100%; }
p.text-right {padding-right: 1em; padding-top: .5em;}
div#container {text-align: center; width: 50em; height: 35em; margin: 0 auto;}
div#panel {padding-bottom: 1em;}
</style>
</head>
<body>
<?php displayUsername(); ?>
<h1 class="text-center">Locations</h1>
<div id="container">
<div id="panel">
	<input id="target" type="text" placeholder="Search Box" class="input-large search-query">
	<button class="btn btn-success" id="AddCurrentLocation">Add Current Location</button>
</div>
<div id="map-canvas" class="google-maps"></div>
<table class="table table-striped">
	<thead>
		<tr><th colspan="4">Location Name</th></tr>
	</thead>
	<tbody id="LocationTable">

	</tbody>
</table>
</div>
</body>
</html>