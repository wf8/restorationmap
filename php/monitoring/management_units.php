<?php
session_start();

require('../restorationmap_config.php');

set_time_limit(0);

 // Opens a connection to a MySQL server.
$connection = mysql_connect($db_server, "habitaw0_natcomm", "n6tur6l");
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db("habitaw0_fpdccnaturalcommunities", $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="fpdcc_management_units"><name>FPDCC Management Units</name>';

$kml[] = "<Style id='outline_style2'><PolyStyle><fill>0</fill></PolyStyle><LineStyle><color>AAFFFFFF</color><width>2</width></LineStyle>";
$kml[] = '<LabelStyle><scale>0.8</scale><color>AAFFFFFF</color></LabelStyle><IconStyle><scale>0</scale></IconStyle></Style>';

// build query 
$query = "SELECT * FROM natural_communities";
			
// number of kml placemarks shown
$placemarks = 0;

// Perform Query
$result = mysql_query($query, $connection);
// Check result
if ($result) {
	
	// Start a while loop to fetch each record in the result set
	while ($row = mysql_fetch_assoc($result)) {
		
		// get the coordinates so we can place the label
		$shape_coordinates = trim($row['coordinates']);
		$points = explode(' ', $shape_coordinates);
		// find the sum of all points
		$length = count($points) - 1;
		$counter = 0;
		$sum_lat = 0;
		$sum_lon = 0;
		$number_of_points = 0;
		while ($counter < $length) {
			if (trim($points[$counter]) != '') {
				$thisCoordinate = explode(',', $points[$counter]);
				$sum_lat = $sum_lat + $thisCoordinate[0];
				$sum_lon = $sum_lon + $thisCoordinate[1];
				$number_of_points++;
			}
			$counter++;
		}
		// now find the average point in between these
		if ($number_of_points == 0)
			$number_of_points = 1;
		$lat = $sum_lat / ($number_of_points);
		$lon = $sum_lon / ($number_of_points);
		
		
		$kml[] = '<Placemark><name>'.$row["CLSMGDPRTN"]."</name>";
		$kml[] = "<description><![CDATA[";
		$kml[] = "<b>Natural Community Now:</b><br>".$row["NATCOMNOW"]."<br>";
		$kml[] = "<b>Natural Community Future:</b><br>".$row["NATCOMFUTU"]."<br>";
		$kml[] = "<b>Current Condition:</b><br>".$row["CONDNOW"]."<br>";
		$kml[] = "<b>Acreage:</b><br>".$row["ACREAGE"]."<br>";
		$kml[] = "]]></description>";
		$kml[] = "<styleUrl>#outline_style2</styleUrl>";
		$kml[] = '<MultiGeometry><Point><coordinates>';
		$kml[] = $lat . ',' . $lon;
		$kml[] = '</coordinates></Point><Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
		$kml[] = $shape_coordinates;
		$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></MultiGeometry></Placemark>';
		$placemarks++;		
			
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

// finish kml document
$kml[] = '</Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>