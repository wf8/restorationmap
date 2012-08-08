<?php
require('../restorationmap_config.php');

 // Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
	die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
	die ('Can\'t use db : ' . mysql_error());
	

$startDate = mysql_real_escape_string($_GET[startDate]);
$endDate = mysql_real_escape_string($_GET[endDate]);
$weed = mysql_real_escape_string($_GET[weed]);
$abundance = mysql_real_escape_string($_GET[abundance]);

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="wsKmlDocument"><name>Weed Scouts</name>';

if ($startDate == 'all') {
	if ($weed == 'All' && $abundance == 'All')
		$query = "SELECT * FROM weed_scouts ORDER BY date ASC";
	if ($weed != 'All' && $abundance == 'All')
		$query = "SELECT * FROM weed_scouts WHERE LCASE(weed)=LCASE('$weed') ORDER BY date ASC";
	if ($weed == 'All' && $abundance != 'All')
		$query = "SELECT * FROM weed_scouts WHERE LCASE(abundance)=LCASE('$abundance') ORDER BY date ASC";
	if ($weed != 'All' && $abundance != 'All')
		$query = "SELECT * FROM weed_scouts WHERE LCASE(abundance)=LCASE('$abundance') AND LCASE(weed)=LCASE('$weed') ORDER BY date ASC";
} else {
	if ($weed == 'All' && $abundance == 'All')
		$query = "SELECT * FROM weed_scouts WHERE date >= '$startDate' AND date <= '$endDate' ORDER BY date ASC";
	if ($weed != 'All' && $abundance == 'All')
		$query = "SELECT * FROM weed_scouts WHERE date >= '$startDate' AND date <= '$endDate' AND LCASE(weed)=LCASE('$weed') ORDER BY date ASC";
	if ($weed == 'All' && $abundance != 'All')
		$query = "SELECT * FROM weed_scouts WHERE date >= '$startDate' AND date <= '$endDate' AND LCASE(abundance)=LCASE('$abundance') ORDER BY date ASC";
	if ($weed != 'All' && $abundance != 'All')
		$query = "SELECT * FROM weed_scouts WHERE date >= '$startDate' AND date <= '$endDate' AND LCASE(abundance)=LCASE('$abundance') AND LCASE(weed)=LCASE('$weed') ORDER BY date ASC";
}

$result = mysql_query($query);
if (!$result) 
 	die('Invalid query: ' . mysql_error());	
				
// cycle through each weed sighting and make placemark
while ($sighting = mysql_fetch_assoc($result)) {
	$kml[] = "<Placemark>";
	$kml[] = "<description><![CDATA[";
	$kml[] = "<b>date:</b> " . htmlspecialchars($sighting["date"]) . "<br>";	
	$kml[] = "<b>weed:</b> " . htmlspecialchars($sighting["weed"]) . "<br>";
	$kml[] = "<b>abundance:</b> " . htmlspecialchars($sighting["abundance"]) . "<br>";
	$kml[] = "<b>longitude:</b> " . htmlspecialchars($sighting["longitude"]) . "<br>";
	$kml[] = "<b>latitude:</b> " . htmlspecialchars($sighting["latitude"]) . "<br>";	
	$kml[] = "<b>note:</b> " . htmlspecialchars($sighting["note"]) . "<br>";
	$kml[] = "<b>name:</b> " . htmlspecialchars($sighting["name"]) . "<br>";	
	$kml[] = "]]></description>";
	$kml[] = "<Point><altitudeMode>clampToGround</altitudeMode><coordinates>";
	$kml[] = $sighting["longitude"] . "," . $sighting["latitude"] . ",0";
	$kml[] = "</coordinates></Point>";
	$kml[] = "<Style><IconStyle><scale>0.6</scale><Icon><href>http://www.habitatproject.org/restorationmap/images/mm_20_yellow.png</href></Icon>";
	$kml[] = '</IconStyle></Style></Placemark>';
}

// Close the recordset
mysql_free_result($result);
	
// finish kml document
$kml[] = '</Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
mysql_close($connection);
?>