<?php
session_start();

require('../restorationmap_config.php');

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, 'land_audit', 'fq1fq1', true);
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db('audubon_land_audit', $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
  
$icon_color = "AAF0FF14";

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="land_audit_2001_doc"><name>Cook County Land Audit 2001</name>';	

// query to get all locations within the entire visible bounding box
$query = "SELECT * FROM 2001_data ORDER BY id";

// perform query
$result = mysql_query($query, $connection);

// check if we got a result
if ($result) {
	
	// Start a while loop to go through each plot
	while ($row = mysql_fetch_assoc($result)) {

		$quality = trim($row["quality"]);
		if ($quality == "Poor")
			$icon_color = "AA1400FF";
		else if ($quality == "Fair")
			$icon_color = "AA1478FF";
		else if ($quality == "Good")
			$icon_color = "AA14F0FF";
		else if ($quality == "Excellent")
			$icon_color = "AA14F000";

		// if it is a valid datapoint make a placemark
		if ($quality !== "0") {
			$kml[] = "<Placemark><description>";
			$kml[] = "<![CDATA[";
			$kml[] = "<b>2001 Land Audit</b><br><br>";
			$kml[] = "<b>" . $row["site_name"] . "</b><br><br>";
			$kml[] = "<b>Site Number:</b><br>" . $row["site_number"] . "<br>";
			$kml[] = "<b>Floristic Quality:</b><br>" . $quality . "<br>";
			$kml[] = "<b>Native FQI:</b><br>" . $row["native_quad_fqi"] . "<br>";
			$kml[] = "<b>Number of native species<br>per square 1/4 meter:</b><br>" . $row["quadnnativespp"] . "<br>";
			$kml[] = "<b>Percent cover by invasives:</b><br>" . $row["cover_by_invasives"] . "<br>";	
			$kml[] = "]]>";
			$kml[] = "</description>";
			$kml[] = "<Point><altitudeMode>clampToGround</altitudeMode><coordinates>";
			$kml[] = "-" . $row["longitude"] . "," . $row["latitude"] . ",0";
			$kml[] = "</coordinates></Point>";
			$kml[] = "<Style><IconStyle><color>".$icon_color."</color><colorMode>normal</colorMode><scale>";
			$kml[] = round( pow( ( $row["native_quad_fqi"] / 10 ) , ( 1 / 2 ) ) , 2);
	  		$kml[] = "</scale><Icon><href>http://www.habitatproject.org/restorationmap/kml/images/circle.png</href></Icon>";
	 		$kml[] = '<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/></IconStyle></Style></Placemark>';
		}
	}
	// Close the recordset
	mysql_free_result($result);
	// finish the kml document, set headers and echo it
	$kml[] = '</Document>';
	$kml[] = '</kml>';
	$kmlOutput = join("\n", $kml);
	header('Content-type: application/vnd.google-earth.kml+xml');
	echo $kmlOutput;
	mysql_close($connection);
}
?>