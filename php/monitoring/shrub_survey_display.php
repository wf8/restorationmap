<?php
session_start();

require('../restorationmap_config.php');

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, 'habitaw0_shrub', 'h0neysuckle');
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db('habitaw0_shrub_survey', $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
  
$year = mysql_real_escape_string($_GET['year']);
$data_type = mysql_real_escape_string($_GET['data_type']);

// determine circle color
if ($data_type = '1')
	$icon_color = "AA3bff00";

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="shrubdocument"><name>Shrub Survey</name>';	
		
// number of kml placemarks shown
$placemarks = 0;		
$points = null;
$number_of_points = null;


// query to get all locations 
$query_locations = "SELECT * FROM locations ORDER BY id";
$locations = mysql_query($query_locations, $connection);

// query to get all observations for the selected year
$query_observations = "SELECT * FROM observations WHERE year=" . $year . "ORDER BY location_id";
$observations = mysql_query($query_observations, $connection);

// check if we got a result
if ($observations) {

	// loop through each location
	while ($location = mysql_fetch_assoc($locations)) {

		$row["LocationID"];
	
	 
			if ($check) {	
				// now create the placemark for this location
				
				// first we finish the last placemark
				if ($lastLocationID != $row["LocationID"] && $lastLocationID != null) {
					$kml[] = "]]>";
					$kml[] = "</description>";
					$kml[] = "<Point><altitudeMode>clampToGround</altitudeMode><coordinates>";
					$kml[] = "-" . $lastLongitude . "," . $lastLatitude . ",0";
					$kml[] = "</coordinates></Point>";
					$kml[] = "<Style><IconStyle><color>".$icon_color."</color><colorMode>normal</colorMode><scale>";
					$kml[] = round( pow( ( $frog_sightings / 10 ) , ( 1 / 3 ) ) , 2);
			  		$kml[] = "</scale><Icon><href>http://www.habitatproject.org/restorationmap/kml/images/circle.png</href></Icon>";
			 		$kml[] = '<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/></IconStyle></Style></Placemark>';
				}
				// keep track of the number of placemarks
				$placemarks++;
				// then start the new placemark
				if ($lastLocationID != $row["LocationID"]) {
					$kml[] = "<Placemark><description>";
					$kml[] = "<![CDATA[";
					$kml[] = "<b>Route:</b> ";
					$kml[] = htmlspecialchars($row["RouteID"]) . "<br>";	
					$kml[] = "<b>Location:</b> " . htmlspecialchars($row["LocationName"]) . "<br>";	
					$kml[] = "<b>Observed on:</b> " . "<br>";
					$frog_sightings = 0;
				}
				// show the date for each dataLocation at this location
				$kml[] = date('n/j/Y',strtotime($row2["RunDate"])) . "<br>";
				if ($species != "All")
					$frog_sightings++;
				if ($species == "All") {
					if ($row[Skipped] == "Y")
						$kml[] = "	- Location skipped<br>";
					else if ($row[NoFrogs] == "Y")
						$kml[] = "	- None seen nor heard<br>";
					else
						// cycle through each datasighting to get species
						while ($row3 = mysql_fetch_assoc($result3)) {
							$kml[] = "	- " . $row3["SpeciesName"] . "<br>";
							$frog_sightings++;
						}
				}
				$lastLocationID = $row["LocationID"];
				$lastLongitude = $row["Longitude"];
				$lastLatitude = $row["Latitude"];
			}
		}
	}
	// now finish the very last placemark
	if ($lastLocationID != null) {
		$kml[] = "]]>";
		$kml[] = "</description>";
		$kml[] = "<Point><altitudeMode>clampToGround</altitudeMode><coordinates>";
		$kml[] = "-" . $lastLongitude . "," . $lastLatitude . ",0";
		$kml[] = "</coordinates></Point>";
		$kml[] = "<Style><IconStyle><color>".$icon_color."</color><colorMode>normal</colorMode><scale>";
		$kml[] = round( pow( ( $frog_sightings / 100 ) , ( 1 / 4 ) ) , 2);
  		$kml[] = "</scale><Icon><href>http://www.habitatproject.org/restorationmap/kml/images/circle.png</href></Icon>";
		$kml[] = '<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/></IconStyle></Style></Placemark>';
	}
	// Close the recordset
	mysql_free_result($result);
}	

// check the number of results found
if ($placemarks == 0) {
	// if no results found show message
	$noKML = array("<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
	$noKML[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
	$noKML[] = ' <Document id="frogdocument"><name>Calling Frog Survey</name>';
	$noKML[] = "<Placemark><name>No results found.</name>";	
	$noKML[] = "<Style><LabelStyle><scale>1</scale><color>ff1400E6</color></LabelStyle> <IconStyle> <scale>0</scale> ";
	$x = ($east + $west) / 2;
	$y = ($north + $south) / 2;
	$noKML[] = "</IconStyle></Style><Point><coordinates>".$x.",".$y.",0</coordinates> </Point></Placemark></Document></kml>";
	$kmlOutput = join("\n", $noKML);
	header('Content-type: application/vnd.google-earth.kml+xml');
	echo $kmlOutput;
} else {
	// otherwise all is good so finish the kml document
	$kml[] = '</Document>';
	$kml[] = '</kml>';
	$kmlOutput = join("\n", $kml);
	header('Content-type: application/vnd.google-earth.kml+xml');
	echo $kmlOutput;
}
mysql_close($connection);
?>