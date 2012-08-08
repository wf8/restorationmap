<?php
require('restorationmap_config.php');

 // Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
	die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
	die ('Can\'t use db : ' . mysql_error());

$site_id = mysql_real_escape_string($_GET[id]);

 // Selects all the rows in the table for this site
 $query = "SELECT * FROM trails WHERE stewardshipsite_id='$site_id'";
 $result = mysql_query($query);
 if (!$result) 
 	die('Invalid query: ' . mysql_error());

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';

// Iterates through the rows, printing a node for each row.
while ($row = @mysql_fetch_assoc($result)) 
{	
	// add the placemark
	$kml[] = ' <Placemark id="trails-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
	$kml[] = ' <name>' . $row['name'] . '</name>'; 
	$km[] = '   <visibility>0</visibility>';  
	$kml[] = '   <Style><LineStyle><color>ffffff00</color><width>2</width></LineStyle></Style>';
	$kml[] = '   <LineString><tessellate>1</tessellate><coordinates>';
	$kml[] = $row['coordinates'];
	$kml[] = ' </coordinates></LineString></Placemark>';
} 

// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>