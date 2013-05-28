<?php
session_start(); 
require('../restorationmap_config.php');

 // Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
	die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
	die ('Can\'t use db : ' . mysql_error());

// get parameters
$data_type = mysql_real_escape_string($_GET['data_type']);
$year = mysql_real_escape_string($_GET['year']);
$county = mysql_real_escape_string($_GET['county']);
$user_id = $_SESSION['user_id'];
$opacity = $_SESSION['opacity'];

// get all the stewardship sites with the right county
if ($county !== 'All') {
	$sites_query = "SELECT * FROM stewardship_site WHERE county='$county'";
	$sites_results = mysql_query($sites_query);
}

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';

// get kml for borders
if ($data_type == 'all') {

	 // Selects all the rows in the table 
	 $query = "SELECT * FROM border WHERE 1";
	 $result = mysql_query($query);
	 if (!$result) 
		die('Invalid query: ' . mysql_error());
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($result)) 
	{
		// check that we are in the right county
		$correct_county = false;
		if ($county !== 'All') {
			while ($site = mysql_fetch_assoc($sites_results)) {
				if ($site['id'] == $row['stewardshipsite_id']) {
					$correct_county = true;
					break;
				}
			}
			// reset county pointer	
			if (mysql_num_rows($sites_results) != 0)
				mysql_data_seek($sites_results, 0);
		}
		if ($county == 'All' || $correct_county) {
			// add the placemark
			$kml[] = '<Placemark id="border-' . $row2['id'] . 'site' . $row['id'] . '">';
			$kml[] = '<visibility>1</visibility>';   		
			$kml[] = '<Style><BalloonStyle><displayMode>hide</displayMode></BalloonStyle>';
			$kml[] = '<LineStyle><color>FFFFFFFF</color><width>2</width></LineStyle><PolyStyle><fill>0</fill></PolyStyle></Style>';
			$kml[] = '<Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
			$kml[] = $row['coordinates'];
			$kml[] = '</coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';	
		}
	} 
}

// get kml for trails
if ($data_type == 'trails' || $data_type == 'all') {

	 // Selects all the rows in the table 
	 $query = "SELECT * FROM trails WHERE 1";
	 $result = mysql_query($query);
	 if (!$result) 
		die('Invalid query: ' . mysql_error());
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($result)) 
	{
		// check that we are in the right county
		$correct_county = false;
		if ($county !== 'All') {
			while ($site = mysql_fetch_assoc($sites_results)) {
				if ($site['id'] == $row['stewardshipsite_id']) {
					$correct_county = true;
					break;
				}
			}
			// reset county pointer	
			if (mysql_num_rows($sites_results) != 0)
				mysql_data_seek($sites_results, 0);
		}
		if ($county == 'All' || $correct_county) {
			// add the placemark
			$kml[] = ' <Placemark id="trails-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
			$kml[] = ' <name>' . $row['name'] . '</name>'; 
			$km[] = '   <visibility>1</visibility>';  
			$kml[] = '   <Style><LineStyle><color>ffffff00</color><width>2</width></LineStyle></Style>';
			$kml[] = '   <LineString><tessellate>1</tessellate><coordinates>';
			$kml[] = $row['coordinates'];
			$kml[] = ' </coordinates></LineString></Placemark>';
		}
	} 
}

// get kml for burns
if ($data_type == 'burns' || $data_type == 'all') {

	if ($opacity == 0) {
		$fill = 0;
		$polygon_color = '001f00ff';
	} else
		$fill = 1;
	if ($opacity == 100) {
		$polygon_color = 'ff1f00ff';
	} else if ($opacity == 90) {
		$polygon_color = 'ee1f00ff';
	} else if ($opacity == 80) {
		$polygon_color = 'dd1f00ff';
	} else if ($opacity == 70) {
		$polygon_color = 'cc1f00ff';
	} else if ($opacity == 60) {
		$polygon_color = 'aa1f00ff';
	} else if ($opacity == 50) {
		$polygon_color = '881f00ff';
	} else if ($opacity == 40) {
		$polygon_color = '771f00ff';
	} else if ($opacity == 30) {
		$polygon_color = '551f00ff';
	} else if ($opacity == 20) {
		$polygon_color = '331f00ff';
	} else if ($opacity == 10) {
		$polygon_color = '111f00ff';
	} 
	
	
	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='burns'";
	$private_layers = mysql_query($query);
		
	// Selects all the rows in the table for the year of interest
	if ($year == 'All')
		$query = "SELECT * FROM burns WHERE 1 ORDER BY date ASC";
	else {
		$date_wildcard = $year . '-%';
		$query = "SELECT * FROM burns WHERE date LIKE '$date_wildcard' ORDER BY date ASC";
	}
	$result = mysql_query($query);
	if (!$result) 
		die('Invalid query: ' . mysql_error());
	
	// set variables for while loop
	$lastYear = '10000';
	$firstFolder = true;
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($result)) 
	{
		// check that we are in the right county
		$correct_county = false;
		if ($county !== 'All') {
			while ($site = mysql_fetch_assoc($sites_results)) {
				if ($site['id'] == $row['stewardshipsite_id']) {
					$correct_county = true;
					break;
				}
			}
			// reset county pointer	
			if (mysql_num_rows($sites_results) != 0)
				mysql_data_seek($sites_results, 0);
		}
		if ($county == 'All' || $correct_county) {
		
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
					$kml[] = ' <visibility>1</visibility>';
					
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
				$km[] = '   <visibility>1</visibility>';  
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
						$kml[] = round($this_point[1], 5) . ', ' . round($this_point[0], 5) . '<br>';
					}
				} 
				
				$kml[] = ']]></description>';
				
				// check if we are displyaing a polygon or a point
				if ($number_of_points < 3) {
					// display a point
					$kml[] = ' <Style><IconStyle><color>ff1f00ff</color>';
					$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
					$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
					$kml[] = $coordinates;
					$kml[] = ' </coordinates></Point></Placemark>';
				} else {
					// display a polygon
					$kml[] = '   <Style><LineStyle><color>ff1f00ff</color></LineStyle><PolyStyle><fill>'.$fill.'</fill><color>'.$polygon_color.'</color></PolyStyle></Style>';
					$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
					$kml[] = $coordinates;
					$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
				}
				
				$lastYear = $thisYear;
			}
		}
	} 
	if (!$firstFolder)
		$kml[] = ' </Folder>';
}

// get kml for brush
if ($data_type == 'brush' || $data_type == 'all') {

	if ($opacity == 0) {
		$fill = 0;
		$polygon_color = '0014F000';
	} else
		$fill = 1;
	if ($opacity == 100) {
		$polygon_color = 'FF14F000';
	} else if ($opacity == 90) {
		$polygon_color = 'ee14F000';
	} else if ($opacity == 80) {
		$polygon_color = 'dd14F000';
	} else if ($opacity == 70) {
		$polygon_color = 'cc14F000';
	} else if ($opacity == 60) {
		$polygon_color = 'aa14F000';
	} else if ($opacity == 50) {
		$polygon_color = '8814F000';
	} else if ($opacity == 40) {
		$polygon_color = '7714F000';
	} else if ($opacity == 30) {
		$polygon_color = '5514F000';
	} else if ($opacity == 20) {
		$polygon_color = '3314F000';
	} else if ($opacity == 10) {
		$polygon_color = '1114F000';
	} 
	
	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='brush'";
	$private_layers = mysql_query($query);
			
	// Selects all the rows in the table for the year of interest
	if ($year == 'All')
		$query = "SELECT * FROM brush WHERE 1 ORDER BY date ASC";
	else {
		$date_wildcard = $year . '-%';
		$query = "SELECT * FROM brush WHERE date LIKE '$date_wildcard' ORDER BY date ASC";
	}	
	$result = mysql_query($query);	
	
	// set variables for while loop
	$lastYear = '10000';
	$firstFolder = true;
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($result)) 
	{	
		// check that we are in the right county
		$correct_county = false;
		if ($county !== 'All') {
			while ($site = mysql_fetch_assoc($sites_results)) {
				if ($site['id'] == $row['stewardshipsite_id']) {
					$correct_county = true;
					break;
				}
			}
			// reset county pointer	
			if (mysql_num_rows($sites_results) != 0)
				mysql_data_seek($sites_results, 0);
		}
		if ($county == 'All' || $correct_county) {
			$layer_is_private = false;
			$user_is_authorized = false;
			while (!$user_is_authorized && $private = @mysql_fetch_assoc($private_layers)) 
			{
				if ($row['id'] == $private['layer_id']) {
					// layer is private, so see if this user is authorized
					$layer_is_private = true;
					if ($user_id == $private['user_id']) {			
						// user is authorized
						$user_is_authorized = true;
					}
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
					$kml[] = ' <visibility>1</visibility>';
					
				}		
				
				// remove 00/00 from dates
				$betterDate = $thisYear;
				if ( $thisDay == '00' ) {
					if ( $thisMonth != '00' ) 
						$betterDate = $thisMonth . '/' . $betterDate;
				} else
					$betterDate = $thisMonth . '/' . $thisDay . '/' . $betterDate;
				
				// add the placemark
				$kml[] = ' <Placemark id="brush-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
				$kml[] = ' <name>' . $betterDate . ' ' . $row['title'] . '</name>'; 
				$km[] = '   <visibility>1</visibility>';  
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
						$kml[] = round($this_point[1], 5) . ', ' . round($this_point[0], 5) . '<br>';
					}
				} 
				
				$kml[] = ']]></description>';
				
				// check if we are displyaing a polygon or a point
				if ($number_of_points < 3) {
					// display a point
					$kml[] = ' <Style><IconStyle><color>FF14F000</color>';
					$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
					$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
					$kml[] = $coordinates;
					$kml[] = ' </coordinates></Point></Placemark>';
				} else {
					// display a polygon
					$kml[] = '   <Style><LineStyle><color>FF14F000</color></LineStyle><PolyStyle><fill>'.$fill.'</fill><color>'.$polygon_color.'</color></PolyStyle></Style>';
					$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
					$kml[] = $coordinates;
					$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
				}
				$lastYear = $thisYear;
			}
		}
	} 
	if (!$firstFolder)
		$kml[] = ' </Folder>';
}

// get kml for landmarks
if ($data_type == 'landmark' || $data_type == 'all') {
	if ($opacity == 0) {
		$fill = 0;
		$polygon_color = '00';
	} else
		$fill = 1;
	if ($opacity == 100) {
		$polygon_color = 'ff';
	} else if ($opacity == 90) {
		$polygon_color = 'ee';
	} else if ($opacity == 80) {
		$polygon_color = 'dd';
	} else if ($opacity == 70) {
		$polygon_color = 'cc';
	} else if ($opacity == 60) {
		$polygon_color = 'aa';
	} else if ($opacity == 50) {
		$polygon_color = '88';
	} else if ($opacity == 40) {
		$polygon_color = '77';
	} else if ($opacity == 30) {
		$polygon_color = '55';
	} else if ($opacity == 20) {
		$polygon_color = '33';
	} else if ($opacity == 10) {
		$polygon_color = '11';
	} 
	
	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='landmark'";
	$private_layers = mysql_query($query);
		
	// Selects all the rows in the table (landmarks don't have years)
	$query = "SELECT * FROM landmark WHERE 1";
	$result = mysql_query($query);
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($result)) 
	{	
		// check that we are in the right county
		$correct_county = false;
		if ($county !== 'All') {
			while ($site = mysql_fetch_assoc($sites_results)) {
				if ($site['id'] == $row['stewardshipsite_id']) {
					$correct_county = true;
					break;
				}
			}
			// reset county pointer	
			if (mysql_num_rows($sites_results) != 0)
				mysql_data_seek($sites_results, 0);
		}
		if ($county == 'All' || $correct_county) {
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
				$km[] = '   <visibility>1</visibility>';  
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
						$kml[] = round($this_point[1], 5) . ', ' . round($this_point[0], 5) . '<br>';
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
					$kml[] = '<PolyStyle><fill>'.$fill.'</fill><color>'.$polygon_color.substr($row['color'],2).'</color></PolyStyle><LabelStyle><scale>0.8</scale><color>' . $row['color'] . '</color>';
					$kml[] = '</LabelStyle><IconStyle><scale>0</scale></IconStyle></Style><MultiGeometry><Point><coordinates>';
					$kml[] = $lat . ',' . $lon;
					$kml[] = '</coordinates></Point><Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
					$kml[] = $shape_coordinates;
					$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></MultiGeometry></Placemark>';
				}	
			}	
		}
	} 
}

// get kml for seed
if ($data_type == 'seed' || $data_type == 'all') {
	if ($opacity == 0) {
		$fill = 0;
		$polygon_color = '0014F0FF';
	} else
		$fill = 1;
	if ($opacity == 100) {
		$polygon_color = 'FF14F0FF';
	} else if ($opacity == 90) {
		$polygon_color = 'ee14F0FF';
	} else if ($opacity == 80) {
		$polygon_color = 'dd14F0FF';
	} else if ($opacity == 70) {
		$polygon_color = 'cc14F0FF';
	} else if ($opacity == 60) {
		$polygon_color = 'aa14F0FF';
	} else if ($opacity == 50) {
		$polygon_color = '8814F0FF';
	} else if ($opacity == 40) {
		$polygon_color = '7714F0FF';
	} else if ($opacity == 30) {
		$polygon_color = '5514F0FF';
	} else if ($opacity == 20) {
		$polygon_color = '3314F0FF';
	} else if ($opacity == 10) {
		$polygon_color = '1114F0FF';
	} 
	
	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='seed'";
	$private_layers = mysql_query($query);
		
	// Selects all the rows in the table for the year of interest
	if ($year == 'All')
		$query = "SELECT * FROM seed WHERE 1 ORDER BY date ASC";
	else {
		$date_wildcard = $year . '-%';
		$query = "SELECT * FROM seed WHERE date LIKE '$date_wildcard' ORDER BY date ASC";
	}
	$result = mysql_query($query);
	
	// set variables for while loop
	$lastYear = '10000';
	$firstFolder = true;
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($result)) 
	{
		// check that we are in the right county
		$correct_county = false;
		if ($county !== 'All') {
			while ($site = mysql_fetch_assoc($sites_results)) {
				if ($site['id'] == $row['stewardshipsite_id']) {
					$correct_county = true;
					break;
				}
			}
			// reset county pointer	
			if (mysql_num_rows($sites_results) != 0)
				mysql_data_seek($sites_results, 0);
		}
		if ($county == 'All' || $correct_county) {
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
					$kml[] = ' <visibility>1</visibility>';
					
				}		
				
				// remove 00/00 from dates
				$betterDate = $thisYear;
				if ( $thisDay == '00' ) {
					if ( $thisMonth != '00' ) 
						$betterDate = $thisMonth . '/' . $betterDate;
				} else
					$betterDate = $thisMonth . '/' . $thisDay . '/' . $betterDate;
				
				// add the placemark
				$kml[] = ' <Placemark id="seed-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
				$kml[] = ' <name>' . $betterDate . ' ' . $row['title'] . '</name>'; 
				$km[] = '   <visibility>1</visibility>';  
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
						$kml[] = round($this_point[1], 5) . ', ' . round($this_point[0], 5) . '<br>';
					}
				} 
				
				$kml[] = ']]></description>';
				
				// check if we are displyaing a polygon or a point
				if ($number_of_points < 3) {
					// display a point
					$kml[] = ' <Style><IconStyle><color>FF14F0FF</color>';
					$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
					$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
					$kml[] = $coordinates;
					$kml[] = ' </coordinates></Point></Placemark>';
				} else {
					// display a polygon
					$kml[] = '   <Style><LineStyle><color>FF14F0FF</color></LineStyle><PolyStyle><fill>'.$fill.'</fill><color>'.$polygon_color.'</color></PolyStyle></Style>';
					$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
					$kml[] = $coordinates;
					$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
				}
				
				$lastYear = $thisYear;
			}
		}
	} 
	if (!$firstFolder)
		$kml[] = ' </Folder>';
}

// get kml for weed
if ($data_type == 'weed' || $data_type == 'all') {
	if ($opacity == 0) {
		$fill = 0;
		$polygon_color = '007800F0';
	} else
		$fill = 1;
	if ($opacity == 100) {
		$polygon_color = 'FF7800F0';
	} else if ($opacity == 90) {
		$polygon_color = 'ee7800F0';
	} else if ($opacity == 80) {
		$polygon_color = 'dd7800F0';
	} else if ($opacity == 70) {
		$polygon_color = 'cc7800F0';
	} else if ($opacity == 60) {
		$polygon_color = 'aa7800F0';
	} else if ($opacity == 50) {
		$polygon_color = '887800F0';
	} else if ($opacity == 40) {
		$polygon_color = '777800F0';
	} else if ($opacity == 30) {
		$polygon_color = '557800F0';
	} else if ($opacity == 20) {
		$polygon_color = '337800F0';
	} else if ($opacity == 10) {
		$polygon_color = '117800F0';
	} 
	
	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='weed'";
	$private_layers = mysql_query($query);
	
	// Selects all the rows in the table for the year of interest
	if ($year == 'All')
		$query = "SELECT * FROM weed WHERE 1 ORDER BY date ASC";
	else {
		$date_wildcard = $year . '-%';
		$query = "SELECT * FROM weed WHERE date LIKE '$date_wildcard' ORDER BY date ASC";
	}
	$result = mysql_query($query);
	
	// set variables for while loop
	$lastYear = '10000';
	$firstFolder = true;
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($result)) 
	{
		// check that we are in the right county
		$correct_county = false;
		if ($county !== 'All') {
			while ($site = mysql_fetch_assoc($sites_results)) {
				if ($site['id'] == $row['stewardshipsite_id']) {
					$correct_county = true;
					break;
				}
			}
			// reset county pointer	
			if (mysql_num_rows($sites_results) != 0)
				mysql_data_seek($sites_results, 0);
		}
		if ($county == 'All' || $correct_county) {
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
					$kml[] = ' <visibility>1</visibility>';
					
				}		
				
				// remove 00/00 from dates
				$betterDate = $thisYear;
				if ( $thisDay == '00' ) {
					if ( $thisMonth != '00' ) 
						$betterDate = $thisMonth . '/' . $betterDate;
				} else
					$betterDate = $thisMonth . '/' . $thisDay . '/' . $betterDate;
				
				// add the placemark
				$kml[] = ' <Placemark id="weed-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
				$kml[] = ' <name>' . $betterDate . ' ' . $row['title'] . '</name>'; 
				$km[] = '   <visibility>1</visibility>';  
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
						$kml[] = round($this_point[1], 5) . ', ' . round($this_point[0], 5) . '<br>';
					}
				} 
				
				$kml[] = ']]></description>';
				
				// check if we are displyaing a polygon or a point
				if ($number_of_points < 3) {
					// display a point
					$kml[] = ' <Style><IconStyle><color>FF7800F0</color>';
					$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
					$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
					$kml[] = $coordinates;
					$kml[] = ' </coordinates></Point></Placemark>';
				} else {
					// display a polygon
					$kml[] = '   <Style><LineStyle><color>FF7800F0</color></LineStyle><PolyStyle><fill>'.$fill.'</fill><color>'.$polygon_color.'</color></PolyStyle></Style>';
					$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
					$kml[] = $coordinates;
					$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
				}
				
				$lastYear = $thisYear;
			}
		}
	}
	if (!$firstFolder)
		$kml[] = ' </Folder>';
}

// get kml for other
if ($data_type == 'other' || $data_type == 'all') {
	if ($opacity == 0) {
		$fill = 0;
		$polygon_color = '00';
	} else
		$fill = 1;
	if ($opacity == 100) {
		$polygon_color = 'ff';
	} else if ($opacity == 90) {
		$polygon_color = 'ee';
	} else if ($opacity == 80) {
		$polygon_color = 'dd';
	} else if ($opacity == 70) {
		$polygon_color = 'cc';
	} else if ($opacity == 60) {
		$polygon_color = 'aa';
	} else if ($opacity == 50) {
		$polygon_color = '88';
	} else if ($opacity == 40) {
		$polygon_color = '77';
	} else if ($opacity == 30) {
		$polygon_color = '55';
	} else if ($opacity == 20) {
		$polygon_color = '33';
	} else if ($opacity == 10) {
		$polygon_color = '11';
	} 
	
	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='other'";
	$private_layers = mysql_query($query);
		
	// Selects all the rows in the table for the year of interest
	if ($year == 'All')
		$query = "SELECT * FROM other WHERE 1 ORDER BY date ASC";
	else {
		$date_wildcard = $year . '-%';
		$query = "SELECT * FROM other WHERE date LIKE '$date_wildcard' ORDER BY date ASC";
	}
	$result = mysql_query($query);
	
	// set variables for while loop
	$lastYear = '10000';
	$firstFolder = true;
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($result)) 
	{
		// check that we are in the right county
		$correct_county = false;
		if ($county !== 'All') {
			while ($site = mysql_fetch_assoc($sites_results)) {
				if ($site['id'] == $row['stewardshipsite_id']) {
					$correct_county = true;
					break;
				}
			}
			// reset county pointer	
			if (mysql_num_rows($sites_results) != 0)
				mysql_data_seek($sites_results, 0);
		}
		if ($county == 'All' || $correct_county) {
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
					$kml[] = ' <name>' .  $thisYear . '</name>'; 
					$kml[] = ' <visibility>1</visibility>';
					
				}		
				
				// remove 00/00 from dates
				$betterDate = $thisYear;
				if ( $thisDay == '00' ) {
					if ( $thisMonth != '00' ) 
						$betterDate = $thisMonth . '/' . $betterDate;
				} else
					$betterDate = $thisMonth . '/' . $thisDay . '/' . $betterDate;
				
				// add the placemark
				$kml[] = ' <Placemark id="other-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
				$kml[] = ' <name>' . $betterDate . ' ' . $row['title'] . '</name>'; 
				$km[] = '   <visibility>1</visibility>';  
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
						$kml[] = round($this_point[1], 5) . ', ' . round($this_point[0], 5) . '<br>';
					}
				} 
				
				$kml[] = ']]></description>';
				
				// check if we are displyaing a polygon or a point
				if ($number_of_points < 3) {
					// display a point
					$kml[] = ' <Style><IconStyle><color>' . $row['color'] . '</color>';
					$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
					$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
					$kml[] = $coordinates;
					$kml[] = ' </coordinates></Point></Placemark>';
				} else {
					// display a polygon
					$kml[] = '   <Style><LineStyle><color>' . $row['color'] . '</color></LineStyle><PolyStyle><fill>'.$fill.'</fill><color>'.$polygon_color.substr($row['color'],2).'</color></PolyStyle></Style>';
					$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
					$kml[] = $coordinates;
					$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
				}
				
				$lastYear = $thisYear;
			}
		}
	} 
	if (!$firstFolder)
		$kml[] = ' </Folder>';
}
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>