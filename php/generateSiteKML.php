<?php
session_start();

require('restorationmap_config.php');

 // Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());

// get user id out of session

if(!$_SESSION['valid']) 
	$user_id = 'not_logged_in';
else
	$user_id = $_SESSION['user_id'];

$site_id = mysql_real_escape_string($_POST[site]);

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';

// get the site name
$query = "SELECT * FROM stewardship_site WHERE id='$site_id'";
$site_result = mysql_query($query);
if (!$site_result) 
	die('Invalid query: ' . mysql_error());
$site = mysql_fetch_assoc($site_result);

// add a folder for the site
$kml[] = '<Folder>'; 
$kml[] = '<name>' . $site['name'] . '</name>';
$kml[] = '<flyToView>1</flyToView>'; 
$kml[] = '<visibility>0</visibility>';	
$kml[] = '<ListStyle><listItemType>checkOffOnly</listItemType></ListStyle>';

// add border for site
$query = "SELECT id, coordinates FROM border WHERE stewardshipsite_id = '$site_id'";
$border_result = mysql_query($query);
if (!$border_result) 
	die('Invalid query: ' . mysql_error());
$border = mysql_fetch_assoc($border_result);
$kml[] = ' <Placemark id="border-' . $border['id'] . 'site' . $site['id'] . '">';
$kml[] = ' <name>Border</name>'; 
$km[] = '   <visibility>1</visibility>';   		
$kml[] = '   <Style><BalloonStyle><displayMode>hide</displayMode></BalloonStyle>';
$kml[] = '   <LineStyle><color>FFFFFFFF</color><width>2</width></LineStyle><PolyStyle><fill>0</fill></PolyStyle></Style>';
$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
$kml[] = $border['coordinates'];
$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';


// trails
$query = "SELECT * FROM trails WHERE stewardshipsite_id='$site_id'";
$trails_results = mysql_query($query);
if (mysql_num_rows($trails_results) != 0) {
	$kml[] = '<Folder>'; 
	$kml[] = '<flyToView>0</flyToView>'; 
	$kml[] = '	<name>Trails</name>'; 
	$kml[] = '	<visibility>0</visibility>'; 	
	// iterate through each row and generate placemark
	while ($trails = @mysql_fetch_assoc($trails_results)) 
	{	
		$kml[] = ' <Placemark id="trails-' . $trails['id'] . 'site' . $trails['stewardshipsite_id'] . '">';
		$kml[] = ' <name>' . $trails['name'] . '</name>'; 
		$km[] = '   <visibility>0</visibility>';  
		$kml[] = '   <Style><LineStyle><color>ffffff00</color><width>2</width></LineStyle></Style>';
		$kml[] = '   <LineString><tessellate>1</tessellate><coordinates>';
		$kml[] = $trails['coordinates'];
		$kml[] = ' </coordinates></LineString></Placemark>';
	}
	$kml[] = '</Folder>'; 
}

// landmarks
$query = "SELECT * FROM landmark WHERE stewardshipsite_id='$site_id'";
$landmark_results = mysql_query($query);
if (mysql_num_rows($landmark_results) != 0) {
	$kml[] = '<Folder>'; 
	$kml[] = '<flyToView>0</flyToView>'; 
	$kml[] = '	<name>Geographic features / Landmarks</name>'; 
	$kml[] = '	<visibility>0</visibility>'; 	
	
	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='landmark'";
	$private_layers = mysql_query($query);
	
	// iterate through each row and generate placemark
	while ($row = @mysql_fetch_assoc($landmark_results)) 
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
	$kml[] = '</Folder>'; 
}

// brush
$query = "SELECT * FROM brush WHERE stewardshipsite_id='$site_id' ORDER BY date ASC";
$brush_results = mysql_query($query);
if (mysql_num_rows($brush_results) != 0) {
	$kml[] = '<Folder>'; 
	$kml[] = '<flyToView>0</flyToView>'; 
	$kml[] = '	<name>Brush and tree removal</name>'; 
	$kml[] = '	<visibility>0</visibility>';
	
	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='brush'";
	$private_layers = mysql_query($query);

	// set variables for while loop
	$lastYear = '10000';
	$firstFolder = true;
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($brush_results)) 
	{
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
			$kml[] = ' <Placemark id="brush-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
			$kml[] = ' <name>' . $betterDate . ' ' . $row['title'] . '</name>'; 
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
				$kml[] = ' <Style><IconStyle><color>FF14F000</color>';
				$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
				$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
				$kml[] = $coordinates;
				$kml[] = ' </coordinates></Point></Placemark>';
			} else {
				// display a polygon
				$kml[] = '   <Style><LineStyle><color>FF14F000</color></LineStyle><PolyStyle><fill>0</fill></PolyStyle></Style>';
				$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
				$kml[] = $coordinates;
				$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
			}
			$lastYear = $thisYear;
		}
	} 
	if (!$firstFolder)
		$kml[] = ' </Folder>';
	$kml[] = '</Folder>'; 
}


// burns
$query = "SELECT * FROM burns WHERE stewardshipsite_id='$site_id' ORDER BY date ASC";
$burns_results = mysql_query($query);
if (mysql_num_rows($burns_results) != 0) {
	$kml[] = '<Folder>'; 
	$kml[] = '<flyToView>0</flyToView>'; 
	$kml[] = '	<name>Prescribed burns</name>'; 
	$kml[] = '	<visibility>0</visibility>';

	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='burns'";
	$private_layers = mysql_query($query);

	// set variables for while loop
	$lastYear = '10000';
	$firstFolder = true;
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($burns_results)) 
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
	if (!$firstFolder)
		$kml[] = ' </Folder>';
	$kml[] = '</Folder>'; 
}



// seed
$query = "SELECT * FROM seed WHERE stewardshipsite_id='$site_id' ORDER BY date ASC";
$seed_results = mysql_query($query);
if (mysql_num_rows($seed_results) != 0) {
	$kml[] = '<Folder>'; 
	$kml[] = '<flyToView>0</flyToView>'; 
	$kml[] = '	<name>Seed collection and planting</name>'; 
	$kml[] = '	<visibility>0</visibility>';

	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='seed'";
	$private_layers = mysql_query($query);
	
	// set variables for while loop
	$lastYear = '10000';
	$firstFolder = true;
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($seed_results)) 
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
			$kml[] = ' <Placemark id="seed-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
			$kml[] = ' <name>' . $betterDate . ' ' . $row['title'] . '</name>'; 
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
				$kml[] = ' <Style><IconStyle><color>FF14F0FF</color>';
				$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
				$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
				$kml[] = $coordinates;
				$kml[] = ' </coordinates></Point></Placemark>';
			} else {
				// display a polygon
				$kml[] = '   <Style><LineStyle><color>FF14F0FF</color></LineStyle><PolyStyle><fill>0</fill></PolyStyle></Style>';
				$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
				$kml[] = $coordinates;
				$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
			}
			
			$lastYear = $thisYear;
		}
	} 
	if (!$firstFolder)
		$kml[] = ' </Folder>';
	$kml[] = '</Folder>'; 
}	

// weed
$query = "SELECT * FROM weed WHERE stewardshipsite_id='$site_id' ORDER BY date ASC";
$weed_results = mysql_query($query);
if (mysql_num_rows($weed_results) != 0) {
	$kml[] = '<Folder>'; 
	$kml[] = '<flyToView>0</flyToView>'; 
	$kml[] = '	<name>Weed control</name>'; 
	$kml[] = '	<visibility>0</visibility>';

	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='weed'";
	$private_layers = mysql_query($query);

	// set variables for while loop
	$lastYear = '10000';
	$firstFolder = true;
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($weed_results)) 
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
			$kml[] = ' <Placemark id="weed-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
			$kml[] = ' <name>' . $betterDate . ' ' . $row['title'] . '</name>'; 
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
				$kml[] = ' <Style><IconStyle><color>FF7800F0</color>';
				$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
				$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
				$kml[] = $coordinates;
				$kml[] = ' </coordinates></Point></Placemark>';
			} else {
				// display a polygon
				$kml[] = '   <Style><LineStyle><color>FF7800F0</color></LineStyle><PolyStyle><fill>0</fill></PolyStyle></Style>';
				$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
				$kml[] = $coordinates;
				$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
			}
			
			$lastYear = $thisYear;
		}
	} 
	if (!$firstFolder)
		$kml[] = ' </Folder>';
	$kml[] = '</Folder>'; 
}	

// other
$query = "SELECT * FROM other WHERE stewardshipsite_id='$site_id' ORDER BY date ASC";
$other_results = mysql_query($query);
if (mysql_num_rows($other_results) != 0) {
	$kml[] = '<Folder>'; 
	$kml[] = '<flyToView>0</flyToView>'; 
	$kml[] = '	<name>Planning and other</name>'; 
	$kml[] = '	<visibility>0</visibility>';

	// select all the private map layers for this shape type
	$query = "SELECT * FROM authorized_users WHERE layer_type='other'";
	$private_layers = mysql_query($query);

	// set variables for while loop
	$lastYear = '10000';
	$firstFolder = true;
	
	// Iterates through the rows, printing a node for each row.
	while ($row = @mysql_fetch_assoc($other_results)) 
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
				$kml[] = ' <name>' .  $thisYear . '</name>'; 
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
			$kml[] = ' <Placemark id="other-' . $row['id'] . 'site' . $row['stewardshipsite_id'] . '">';
			$kml[] = ' <name>' . $betterDate . ' ' . $row['title'] . '</name>'; 
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
				$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
				$kml[] = $coordinates;
				$kml[] = ' </coordinates></Point></Placemark>';
			} else {
				// display a polygon
				$kml[] = '   <Style><LineStyle><color>' . $row['color'] . '</color></LineStyle><PolyStyle><fill>0</fill></PolyStyle></Style>';
				$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
				$kml[] = $coordinates;
				$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
			}
			
			$lastYear = $thisYear;
		}
	} 
	if (!$firstFolder)
		$kml[] = ' </Folder>';
	$kml[] = '</Folder>'; 
}	

// if kml_url is not NULL, add link to site specific layers
if ( !is_null($site['kml_url']) )
{
	$kml[] = '<NetworkLink>'; 
	$kml[] = '<flyToView>0</flyToView>'; 
	$kml[] = '	<name>Uploaded layers</name>'; 
	$kml[] = '	<open>0</open>'; 		
	$kml[] = '	<visibility>0</visibility>'; 
	$kml[] = '	<Link><viewRefreshMode>onRequest</viewRefreshMode>'; 
	$kml[] = '		<href>http://habitatproject.org/restorationmap/kml/' . $site['kml_url'] . '</href>'; 
	$kml[] = '	</Link>'; 
	$kml[] = '</NetworkLink>	';
}

// close site folder 
$kml[] = '</Folder>';
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>