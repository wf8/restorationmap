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

// get user id
$user_id = mysql_real_escape_string($_GET[user_id]);

// select all the private map layers for this shape type
$query = "SELECT * FROM authorized_users WHERE layer_type='burns'";
$private_layers = mysql_query($query);
	
 // Selects all the rows in the table for this site
 $query = "SELECT * FROM burns WHERE stewardshipsite_id='$site_id' ORDER BY date ASC";
 $result = mysql_query($query);
 if (!$result) 
 	die('Invalid query: ' . mysql_error());

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';

// set variables for while loop
$lastYear = '10000';
$firstFolder = true;

// Iterates through the rows, printing a node for each row.
while ($row = @mysql_fetch_assoc($result)) 
{
	$layer_is_private = false;
	$user_is_authorized = false;
	while (!$user_is_authorized && $private = @mysql_fetch_assoc($private_layers)) 
	{
		if ($row['id'] == $private['layer_id']) {
			// layer is private, so see if this user is authorized
			$layer_is_private = true;
			if ($user_id == $private['user_id'])
				// user is authorized
				$user_is_authorized = true;
		}
	}		
	// reset pointer	
	if (mysql_num_rows($private_layers) != 0)
		mysql_data_seek($private_layers, 0);
	if (!$layer_is_private || ($layer_is_private && $user_is_authorized)) {		
		// get the year month date out of the sql date
		$theDate = $row['date'];
		list($thisYear, $thisMonth, $thisDay) = split('-', $theDate);
		// check to see if we need to create a new year folder
		if ($thisYear != $lastYear) 
		{
			// if this is not the first folder we need to close the last folder
			if ($firstFolder)
				$firstFolder = false;
			else
				$kml[] = ' </Folder>';
			// create the new folder
			$kml[] = ' <Folder>';
			$kml[] = ' <name>' . $thisYear . '</name>'; 
			$kml[] = ' <visibility>0</visibility>';
			
		}		
		
		// remove 00/00 from dates
		$betterDate = $thisYear;
		if ( $thisDay == '00' ) {
			if ( $thisMonth != '00' ) 
				$betterDate = $thisMonth . '/' . $betterDate;
		} else
			$betterDate = $thisMonth . '/' . $thisDay . '/' . $betterDate;
		
		// add the placemark
		$kml[] = ' <Placemark id="burns-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
		$kml[] = ' <name>' . $betterDate . ' ' . $row['title'] . '</name>'; 
		$km[] = '   <visibility>0</visibility>';  
		$kml[] = '   <description>' . $row['description'] . '</description>'; 
		
		// check if we are displyaing a polygon or a point
		$coordinates = trim($row['coordinates']);
		$points = explode(' ', $coordinates);
		$number_of_points = count($points);
  		
		if ($number_of_points < 3) {
			// display a point
			$kml[] = ' <Style><IconStyle><color>ff1f00ff</color>';
			$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
			$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
			$kml[] = $coordinates;
			$kml[] = ' </coordinates></Point></Placemark>';
		} else {
			// display a polygon
			$kml[] = '   <Style><LineStyle><color>ff1f00ff</color></LineStyle><PolyStyle><fill>0</fill></PolyStyle></Style>';
			$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
			$kml[] = $coordinates;
			$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
		}
		
		$lastYear = $thisYear;
	}
} 

// End XML file
if (!$firstFolder)
	$kml[] = ' </Folder>';
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>