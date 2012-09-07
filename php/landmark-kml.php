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
$query = "SELECT * FROM authorized_users WHERE layer_type='landmark'";
$private_layers = mysql_query($query);
	
 // Selects all the rows in the table for this site
 $query = "SELECT * FROM landmark WHERE stewardshipsite_id='$site_id'";
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
			
		// add the placemark
		$kml[] = ' <Placemark id="landmark-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
		$kml[] = ' <name>' . $row['name'] . '</name>'; 
		$km[] = '   <visibility>0</visibility>';  
		$kml[] = '   <description><![CDATA[' . $row['description'];  	
		
		$coordinates = trim($row['coordinates']);
		$points = explode(' ', $coordinates);
		$number_of_points = count($points);

  		// display coordinates for simple polygons
  		if ($number_of_points < 10) {
  			$kml[] = '</br></br>Lat/Long Coordinates:<br>';
  			if ($number_of_points == 1)
  				$number_of_points = 2;
  			for($i = 0; $i < $number_of_points - 1; ++$i) {
			    $this_point = explode(',', $points[$i]);
			    $kml[] = $this_point[1] . ', ' . $this_point[0] . '<br>';
			}
  		} 
  		
  		$kml[] = ']]></description>';
  		
  		// check if we are displyaing a polygon or a point
		if ($number_of_points < 3) {
			// display a point
			$kml[] = ' <Style><IconStyle><color>' . $row['color'] . '</color>';
			$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
			$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0.8</scale><color>' . $row['color'] . '</color></LabelStyle></Style><Point><coordinates>';
			$kml[] = $coordinates;
			$kml[] = ' </coordinates></Point></Placemark>';
		} else {
			// display a polygon
			// get the coordinates so we can place the label
			$shape_coordinates = $row['coordinates'];
			$points = explode(' ', $shape_coordinates);
			// find the sum of all points
			$number_of_points = count($points) - 1;
			$counter = 0;
			$sum_lat = 0;
			$sum_lon = 0;
			while ($counter < $number_of_points) {
				$thisCoordinate = explode(',', $points[$counter]);
				$sum_lat = $sum_lat + $thisCoordinate[0];
				$sum_lon = $sum_lon + $thisCoordinate[1];
				$counter++;
			}
			// now find the average point in between these
			if ($counter == 0)
				$counter = 1;
			$lat = $sum_lat / ($counter);
			$lon = $sum_lon / ($counter);
			// kml for the polygon
			$kml[] = '<Style><LineStyle><color>' . $row['color'] . '</color></LineStyle>';
			$kml[] = '<PolyStyle><fill>0</fill></PolyStyle><LabelStyle><scale>0.8</scale><color>' . $row['color'] . '</color>';
			$kml[] = '</LabelStyle><IconStyle><scale>0</scale></IconStyle></Style><MultiGeometry><Point><coordinates>';
			$kml[] = $lat . ',' . $lon;
			$kml[] = '</coordinates></Point><Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
			$kml[] = $shape_coordinates;
			$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></MultiGeometry></Placemark>';
		}		
	}
} 

// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>