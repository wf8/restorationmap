<?php
session_start();

require('../restorationmap_config.php');
require('spring_creek_2011_bird_blitz_boundaries.php');

// connect to MySQL server.
$connection = mysql_connect ($db_server, $sc2011_db_user, $sc2011_db_password, true);
if (!$connection) 
  die('Not connected : ' . mysql_error());

// set active MySQL database.
$db_selected = mysql_select_db($sc2011_db_database, $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
 
$boundaries = mysql_real_escape_string($_GET[boundaries]);
$species = mysql_real_escape_string($_GET[species]);
$habitat = mysql_real_escape_string($_GET[habitat]);
$status = mysql_real_escape_string($_GET[status]);

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="sc2011document"><name>Spring Creek 2011 Bird Blitz</name>';

$kml[] = "<LookAt><longitude>-88.212580664947</longitude><latitude>42.11615626209326</latitude><altitude>0</altitude>";
$kml[] = "<heading>-3.33309209613983e-10</heading><tilt>0</tilt><range>11949.96973006292</range><altitudeMode>relativeToGround</altitudeMode>";
$kml[] = "<gx:altitudeMode>relativeToSeaFloor</gx:altitudeMode></LookAt>";

// fix strings
if ($habitat == 'Woodland & Savanna')
	$habitat = 'woodland';	
if ($habitat == 'Urban & Introduced')
	$habitat = 'urban';	
$habitat = strtolower($habitat);	
$species = htmlspecialchars(strtolower($species));	

// build query 
if ($species == 'all')
	$query = "SELECT observations.Common_name, observations.Count, observations.Latitude, observations.Longitude, observations.Location, observations.Checklist_Comments, observations.Species_Comments, observations.Date, observations.Time FROM observations ORDER BY observations.Location, observations.Common_name";
else
	$query = 'SELECT observations.Common_name, observations.Count, observations.Latitude, observations.Longitude, observations.Location, observations.Checklist_Comments, observations.Species_Comments, observations.Date, observations.Time FROM observations WHERE LCASE(observations.Common_name) LIKE "%'.$species.'%" ORDER BY observations.Location';
			
// number of kml placemarks shown
$placemarks = 0;

// Perform Query
$result = mysql_query($query, $connection);
// Check result
if ($result) {
	
	$lastLocation = null;	
	// Start a while loop to fetch each record in the result set
	while ($row = mysql_fetch_assoc($result)) {
		
		$check = true;
		
		if ( ($status != 'All birds') || ($habitat != 'all') ) {
			// get the status of this observations
			$query2 = 'SELECT * FROM birds WHERE birds.name LIKE "%' . trim(strtolower($row["Common_name"])) . '%"';
			$result2 = mysql_query($query2, $connection);
			if ($result2)
				$row2 = mysql_fetch_assoc($result2);
			else
				$check = false;			
		}	
		// check conservation status
		if ($status != 'All birds') {
				
			if ($status == 'Birds of Concern') {
				if ( (trim($row2["chicago_status"]) == "") && (trim($row2["illinois_status"]) == "") )
					$check = false;
			}
			if ($status == 'CW Priority 1') {
				if (trim($row2["chicago_status"]) != "CW-PR1")
					$check = false;
			}
			if ($status == 'CW Priority 2') {
				if (trim($row2["chicago_status"]) != "CW-PR2")
					$check = false;
			}
			if ($status == 'CW Priority 3') {
				if (trim($row2["chicago_status"]) != "CW-PR3")
					$check = false;
			}
			if ($status == 'Endangered in Illinois') {
				if (trim($row2["illinois_status"]) != "IL-ENDGR.")
					$check = false;
			}
			if ($status == 'Threatened in Illinois') {
				if (trim($row2["illinois_status"]) != "IL-THRT.")
					$check = false;
			}
			
		}
		// check habitat designation		
		if ($check && ($habitat != 'all') ) {
		//echo $row["Common_name"] . "==" . $row2["habitat"] ."==". trim($habitat) . ">>" . strpos($row2["habitat"], trim($habitat)) . "|||||||";
			$pos = strpos($row2["habitat"], trim($habitat));
			if ($pos === false) 
 				$check = false;
		}
			
		if ($check) {
			// status is good so we can make the placemark
				
			// first we finish the last placemark
			if ($lastLocation != $row["Location"] && $lastLocation != null) {
				$kml[] = " - Count: " . $highest_count . "<br>";
				$total_site_count = $total_site_count + $highest_count;
				$kml[] = "]]></description>";
				$kml[] = "<Point><altitudeMode>clampToGround</altitudeMode><coordinates>";
				$kml[] = $lastLongitude . "," . $lastLatitude . ",0";
				$kml[] = "</coordinates></Point>";
				$kml[] = "<Style><IconStyle><color>CC14F0FF</color><colorMode>normal</colorMode><scale>";
				$kml[] = round( pow( ( $total_site_count / 100 ) , ( 1 / 4 ) ) , 2);
  				$kml[] = "</scale><Icon><href>http://www.habitatproject.org/restorationmap/kml/images/circle.png</href></Icon>";
 				$kml[] = '<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/></IconStyle></Style></Placemark>';
			}
			// keep track of the number of placemarks
			$placemarks++;
			// then start the new placemark
			if ($lastLocation != $row["Location"]) {
				$total_site_count = 0;
				$old_bird_name = null;
				$kml[] = "<Placemark>";
				$kml[] = "<description><![CDATA[";
				$kml[] = "<b>Location:</b> " . htmlspecialchars($row["Location"]) . "<br>";	
				$kml[] = "<b>Birds observed:</b> " . "<br>";
			}
			// we only want to show the highest count for each bird species
			// remember name of this bird observation
			$this_bird_name = $row["Common_name"];
			// if a new bird
			if ($this_bird_name != $old_bird_name) {
				// if we are still at the same location, we need to display count of old bird
				if ($old_bird_name != null) {
					$kml[] = " - Count: " . $highest_count . "<br>";
					$total_site_count = $total_site_count + $highest_count;
				}
				// display name of new bird
				$kml[] = $this_bird_name . "<br>";
				$old_bird_name = $this_bird_name;
				$highest_count = 0;
			}
			// get the count of this observation
			$this_count = $row["Count"];
			// remeber the highest count for this species
			if ($this_count > $highest_count)
				$highest_count = $this_count;
			
			// $kml[] = " - " . $row["Date"] . " " . $row["Time"] . "<br>";	
			//if ($row["Species_Comments"] != null)
			//	$kml[] = " - Species Comments: " . $row["Species_Comments"] . "<br>";
			//if ($row["Checklist_Comments"] != null)
			//	$kml[] = " - Checklist Comments: " . $row["Checklist_Comments"] . "<br>";	
			$lastLocation = $row["Location"];
			$lastLongitude = $row["Longitude"];
			$lastLatitude = $row["Latitude"];
		
		}
	}
	// now finish the very last placemark
	if ($lastLocation != null) {
		$kml[] = " - Count: " . $highest_count . "<br>";
		$total_site_count = $total_site_count + $highest_count;
		$kml[] = "]]></description>";
		$kml[] = "<Point><altitudeMode>clampToGround</altitudeMode><coordinates>";
		$kml[] = $lastLongitude . "," . $lastLatitude . ",0";
		$kml[] = "</coordinates></Point>";
		$kml[] = "<Style><IconStyle><color>CC14F0FF</color><colorMode>normal</colorMode><scale>";
		$kml[] = round( pow( ( $total_site_count / 100 ) , ( 1 / 4 ) ) , 2);
  		$kml[] = "</scale><Icon><href>http://www.habitatproject.org/restorationmap/kml/images/circle.png</href></Icon>";
 		$kml[] = '<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/></IconStyle></Style></Placemark>';
	}
	// Close the recordset
	mysql_free_result($result);
}	


// check the number of results found
if ($placemarks == 0) {
	// if no results found show message
	$kml[] = "<Placemark><name>No results found.</name>";	
	$kml[] = "<Style><LabelStyle><scale>1</scale><color>ff1f00ff</color></LabelStyle> <IconStyle> <scale>0</scale> ";
	$kml[] = "</IconStyle></Style><Point><coordinates>-88.216696,42.118758,0</coordinates> </Point></Placemark>";
}

// add boundaries
if ($boundaries == 'y') {
 	$kml[] = $sc_subunit_boundaries;
}

// finish kml document
$kml[] = '</Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
mysql_close($connection);
?>