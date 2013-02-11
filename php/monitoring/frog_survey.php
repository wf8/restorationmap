<?php
session_start();

require('../restorationmap_config.php');

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $frog_db_username, $frog_db_password, true);
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($frog_db_database, $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
  
 
$north = mysql_real_escape_string($_GET['north']);
$south = mysql_real_escape_string($_GET['south']);
$west = mysql_real_escape_string($_GET['west']);
$east = mysql_real_escape_string($_GET['east']);
$species = mysql_real_escape_string($_GET['species']);
$year_begin = mysql_real_escape_string($_GET['year_begin']);
$year_end = mysql_real_escape_string($_GET['year_end']);

$icon_color = "AA3bff00";

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="frogdocument"><name>Calling Frog Survey</name>';	
		
// number of kml placemarks shown
$placemarks = 0;		
$points = null;
$number_of_points = null;


// query to get all locations within the entire visible bounding box
$query = "SELECT DISTINCT Location.LocationID, Location.LocationName, Location.RouteID, Location.CountyState, Location.Longitude, Location.Latitude, DataLocation.Observation_Seq, DataLocation.NoFrogs, DataLocation.Skipped FROM Location JOIN DataLocation ON Location.LocationID=DataLocation.LocationID WHERE ((CAST(Location.Latitude AS DECIMAL(30,20)) > '$south') && (CAST(Location.Latitude AS DECIMAL(30,20)) < '$north') && (CAST(Location.Longitude AS DECIMAL(30,20))*-1 > '$west') && (CAST(Location.Longitude AS DECIMAL(30,20))*-1 < '$east')) ORDER BY Location.LocationID";

// perform query
$result = mysql_query($query, $connection);

// check if we got a result
if ($result) {
	$lastLocationID = null;	
	// Start a while loop to fetch each record in the result set
	while ($row = mysql_fetch_assoc($result)) {

		// get the rundate of this datalocation
		$query2 = "SELECT DataRoute.RunDate FROM DataRoute WHERE DataRoute.Observation_Seq=" . $row["Observation_Seq"];
		$result2 = mysql_query($query2, $connection);
		$row2 = mysql_fetch_assoc($result2);
		$runDateYear = substr($row2["RunDate"], 0, 4);
		// check to see if the observation is in the right year
		if ($year_begin == "All" || $year_end == "All")
			$check = true;
		else if ($year_begin <= $runDateYear && $runDateYear <= $year_end)
			$check = true;
		else
			$check = false;

		if ($check) {
			// the year is correct so we go on:
			// next get all dataSightings from this dataLocation
			$query3 = "SELECT DataSighting.SpeciesName FROM DataSighting WHERE DataSighting.Observation_Seq=" . $row["Observation_Seq"];
			$query3 = $query3 . " AND DataSighting.LocationID=" . $row["LocationID"];
			$result3 = mysql_query($query3, $connection);
			// check if its the right species
			if ($species != "All") {
				$check = false;
				// cycle through each datasighting to check species
				while ($row3 = mysql_fetch_assoc($result3)) {
					if ($species == $row3["SpeciesName"])
						$check = true;
				}
			}
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