<?php
session_start();

require('../restorationmap_config.php');

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $bcn_db_username, $bcn_db_password, true);
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($bcn_db_database, $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
  
// also connect to sc bird blitz db to get bird species list
$connection2 = mysql_connect($db_server, $sc2011_db_username, $sc2011_db_password, true);
if (!$connection2) 
  die('Not connected : ' . mysql_error());
$db_selected2 = mysql_select_db($sc2011_db_database, $connection2);
if (!$db_selected2) 
  die ('Can\'t use db : ' . mysql_error());  
 
$north = mysql_real_escape_string($_GET['north']);
$south = mysql_real_escape_string($_GET['south']);
$west = mysql_real_escape_string($_GET['west']);
$east = mysql_real_escape_string($_GET['east']);
$species = mysql_real_escape_string($_GET['species']);
$habitat = mysql_real_escape_string($_GET['habitat']);
$status = mysql_real_escape_string($_GET['status']);
$month_begin = mysql_real_escape_string($_GET['month_begin']) * 100;
$month_end = mysql_real_escape_string($_GET['month_end']) * 100 + 99;
$year_begin = mysql_real_escape_string($_GET['year_begin']);
$year_end = mysql_real_escape_string($_GET['year_end']);
$coordinates = trim(mysql_real_escape_string($_GET['coordinates']));

$icon_color = "AA14B4FF";

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="sc2011document"><name>Spring Creek 2011 Bird Blitz</name>';


// fix strings
if ($habitat == 'Woodland & Savanna')
	$habitat = 'woodland';	
if ($habitat == 'Urban & Introduced')
	$habitat = 'urban';	
$habitat = strtolower($habitat);	
$species = htmlspecialchars(strtolower($species));	
		
// number of kml placemarks shown
$placemarks = 0;		
$points = null;
$number_of_points = null;

// check to see if we are viewing data within the whole screen or within a polygon
if ($coordinates == "")
	// query to get all locations within the entire visible bounding box
	$query = "SELECT * FROM locations WHERE ((CAST(latitude AS DECIMAL(30,20)) > '$south') && (CAST(latitude AS DECIMAL(30,20)) < '$north') && (CAST(longitude AS DECIMAL(30,20)) > '$west') && (CAST(longitude AS DECIMAL(30,20)) < '$east'))"; 
else {
	// build query to get results from within the polygon's bounding box	
	$points = explode(' ', $coordinates);
	// find the sum of all points
	$number_of_points = count($points);
	$counter = 0;
	$farthest_north = null;
	$farthest_south = null;
	$farthest_east = null;
	$farthest_west = null;
	while ($counter < $number_of_points) {
		$thisCoordinate = explode(',', $points[$counter]);
		if ($farthest_south == null || $farthest_south > $thisCoordinate[1]) $farthest_south = $thisCoordinate[1];
		if ($farthest_north == null || $farthest_north < $thisCoordinate[1]) $farthest_north = $thisCoordinate[1];
		if ($farthest_west == null || $farthest_west > $thisCoordinate[0]) $farthest_west = $thisCoordinate[0];
		if ($farthest_east == null || $farthest_east < $thisCoordinate[0]) $farthest_east = $thisCoordinate[0];
		$counter++;
	}
	$query = "SELECT * FROM locations WHERE ((CAST(latitude AS DECIMAL(30,20)) > '$farthest_south') && (CAST(latitude AS DECIMAL(30,20)) < '$farthest_north') && (CAST(longitude AS DECIMAL(30,20)) > '$farthest_west') && (CAST(longitude AS DECIMAL(30,20)) < '$farthest_east'))";
}

// perform query
$result = mysql_query($query, $connection);

// check if we got a result
if ($result) {
	
	if ($species == 'all') {
		
		// get a list of all bird species with the correct habitat and status
		if ( ($status != 'All birds') && ($habitat != 'all') ) {
			$habitat_status_query = 'SELECT * FROM birds WHERE ( birds.chicago_status != "" OR birds.illinois_status != "" ) AND birds.habitat LIKE "%' . trim($habitat) . '%"';
		} else if ( ($status != 'All birds') && ($habitat == 'all') ) {		
			$habitat_status_query = 'SELECT * FROM birds WHERE ( birds.chicago_status != "" OR birds.illinois_status != "" )';
		} else if ( ($status == 'All birds') && ($habitat != 'all') ) {		
			$habitat_status_query = 'SELECT * FROM birds WHERE birds.habitat LIKE "%' . trim($habitat) . '%"';
		}
			
		$bird_results = mysql_query($habitat_status_query, $connection2);		
	}
		
	// cycle through each location one by one
	while ($location = mysql_fetch_assoc($result)) {
				
		// if using a polygon, check if each location is within the polygon
		if ($coordinates != "") {
			
			$point_in_polygon = false;
			$i = 0;
			$j = $number_of_points - 1;
			$y = $location["longitude"];
			$x = $location["latitude"];
			while ($i < $number_of_points) {
				$coordinate_i = explode(',', $points[$i]);
				$x_i = $coordinate_i[1];
				$y_i = $coordinate_i[0];
				$coordinate_j = explode(',', $points[$j]);
				$x_j = $coordinate_j[1];
				$y_j = $coordinate_j[0];
				if ( ( (($y_i < $y) && ($y_j >= $y)) || (($y_j < $y) && ($y_i >= $y)) ) && (($x_i <= $x) || ($x_j <= $x)) ) {
      				if ( ( $x_i + ($y - $y_i) / ( $y_j - $y_i ) * ( $x_j - $x_i ) ) < $x )
      					$point_in_polygon = !$point_in_polygon; 
      			}
    			$j = $i; 
				$i++;
			}
		}

		if ( ($coordinates == "") || $point_in_polygon)
		{	
			// location is either in the polygon or in the screen
			// so now we can get all observations from this location
			
			$date = null;
			$old_date = null;
			$total_location_count = 0;
			$bird_list = '';
			$location_id = $location["location_id"];
			
			// construct the query to get the observations for this location:
			if (($month_begin == 'all' || $month_end == 'all') && ($year_begin == 'all' || $year_end == 'all')) {
			
				// get all observations for this location regardless of time
				if ($species == 'all')
					$query = "SELECT * FROM observations WHERE (observations.location_id = '$location_id') ORDER BY observations.year, observations.month_day";
				else
					$query = "SELECT * FROM observations WHERE (observations.location_id = '$location_id') && LCASE(observations.common_name) LIKE '%$species%' ORDER BY observations.year, observations.month_day";	
					
			} else if (($month_begin != 'all' && $month_end != 'all') && ($year_begin == 'all' || $year_end == 'all')) {
				
				// get all observations for this location within the month range for all years
				
				if ($month_begin <= $month_end) 
					$month_comparison = "&&";
				else 
					$month_comparison = "||";	
				
				if ($species == 'all')
					$query = "SELECT * FROM observations WHERE (observations.location_id = '$location_id') &&  ( ('$month_begin' <= observations.month_day) ".$month_comparison." (observations.month_day <= '$month_end') ) ORDER BY observations.year, observations.month_day";
				else
					$query = "SELECT * FROM observations WHERE (observations.location_id = '$location_id') && ( ('$month_begin' <= observations.month_day) ".$month_comparison." (observations.month_day <= '$month_end') ) && LCASE(observations.common_name) LIKE '%$species%' ORDER BY observations.year, observations.month_day";
					
			} else if (($month_begin == 'all' || $month_end == 'all') && ($year_begin != 'all' && $year_end != 'all')) {
				
				// get all observations for this location within the year range for all months
				if ($species == 'all')
					$query = "SELECT * FROM observations WHERE (observations.location_id = '$location_id') && ('$year_begin' <= observations.year) && (observations.year <= '$year_end') ORDER BY observations.year, observations.month_day";
				else
					$query = "SELECT * FROM observations WHERE (observations.location_id = '$location_id') && ('$year_begin' <= observations.year) && (observations.year <= '$year_end') && LCASE(observations.common_name) LIKE '%$species%' ORDER BY observations.year, observations.month_day"; 
					
			} else if (($month_begin != 'all' && $month_end != 'all') && ($year_begin != 'all' && $year_end != 'all')) {
				
				// get all observations for this location within the month and year range
				
				if ($month_begin <= $month_end) 
					$month_comparison = "&&";
				else 
					$month_comparison = "||";
					
				
				if ($species == 'all')
					$query = "SELECT * FROM observations WHERE (observations.location_id = '$location_id') && ('$year_begin' <= observations.year) && (observations.year <= '$year_end') && ( ('$month_begin' <= observations.month_day) ".$month_comparison." (observations.month_day <= '$month_end') ) ORDER BY observations.year, observations.month_day";
				else
					$query = "SELECT * FROM observations WHERE (observations.location_id = '$location_id') && ('$year_begin' <= observations.year) && (observations.year <= '$year_end') && ( ('$month_begin' <= observations.month_day) ".$month_comparison." (observations.month_day <= '$month_end') ) && LCASE(observations.common_name) LIKE '%$species%' ORDER BY observations.year, observations.month_day"; 
					
			}	
			$observation_results = mysql_query($query, $connection);	
				
			// cycle through each observation at this location
			while ($observations = mysql_fetch_assoc($observation_results)) {			
				
				$is_valid_bird = true;
				
				if ( ($species == 'all') && ( ($status != 'All birds') || ($habitat != 'all') ) ) {
						
					// we need to see if the bird for this observation has the correct status and/or habitat
					$is_valid_bird = false;
					
					// reset row pointer in the search results
					mysql_data_seek($bird_results, 0);
					
					// cycle through all the valid birds and check if this bird is valid
					// (valid in terms of habitat and status)
					while ($valid_bird = mysql_fetch_assoc($bird_results)) {
	
						if (trim(strtolower($observations["common_name"])) == trim($valid_bird["name"])) {		
							// we found the bird - if it is in this list it already has the correct habitat
							// now check its conservation status if necessary
							if ($status == 'All birds') {
								$is_valid_bird = true;
							} else {
									
								if ($status == 'Breeding Season Birds of Concern') {
									if ( (trim($valid_bird["chicago_status"]) != "") || (trim($valid_bird["illinois_status"]) != "") ) 
										$is_valid_bird = true;
								}
								if ($status == 'CW Priority 1') {
									if (trim($valid_bird["chicago_status"]) == "CW-PR1")
										$is_valid_bird = true;
								}
								if ($status == 'CW Priority 2') {
									if (trim($valid_bird["chicago_status"]) == "CW-PR2")
										$is_valid_bird = true;
								}
								if ($status == 'CW Priority 3') {
									if (trim($valid_bird["chicago_status"]) == "CW-PR3")
										$is_valid_bird = true;
								}
								if ($status == 'Endangered in Illinois') {
									if (trim($valid_bird["illinois_status"]) == "IL-ENDGR.")
										$is_valid_bird = true;
								}
								if ($status == 'Threatened in Illinois') {
									if (trim($valid_bird["illinois_status"]) == "IL-THRT.")
										$is_valid_bird = true;
								}
								
							} 
						}
					}
				}
				
				if ($is_valid_bird) {				
					// status & habitat is good so we add this observation
					
					// first format the date
					if (strlen($observations["month_day"]) == 3) {
						$month = substr($observations["month_day"], 0, 1);
						$day = substr($observations["month_day"], 1);
					} else {
						$month = substr($observations["month_day"], 0, 2);
						$day = substr($observations["month_day"], 2);
					}
					$date = $month.'/'.$day.'/'.$observations["year"];
					// if a new date
					if ($date != $old_date) {
						$bird_list = $bird_list . $date . "<br>";
					}
					$bird_list = $bird_list . " - " . $observations["common_name"] . " - " . $observations["how_many_at_least"] . "<br>";
					$total_location_count = $total_location_count + $observations["how_many_at_least"];
					$old_date = $date;
				}
			}
			mysql_free_result($observation_results);
			
			// if any birds were at this location then make a placemark for this location
			if ($total_location_count > 0) {
				// keep track of the number of placemarks
				$placemarks++;
				$kml[] = "<Placemark>";
				$kml[] = "<description><![CDATA[";
				$kml[] = "<b>Location:</b> " . htmlspecialchars($location["location_name"]) . "<br>";	
				$kml[] = "<b>Birds observed:</b> " . "<br>";
				$kml[] = $bird_list;
				$kml[] = "]]></description>";
				$kml[] = "<Point><altitudeMode>clampToGround</altitudeMode><coordinates>";
				$kml[] = $location["longitude"] . "," . $location["latitude"] . ",0";
				$kml[] = "</coordinates></Point>";
				$kml[] = "<Style><IconStyle><color>".$icon_color."</color><colorMode>normal</colorMode><scale>";
				$kml[] = round( pow( ( $total_location_count / 100 ) , ( 1 / 4 ) ) , 2);
		  		$kml[] = "</scale><Icon><href>http://www.habitatproject.org/restorationmap/kml/images/circle.png</href></Icon>";
		 		$kml[] = '<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/></IconStyle></Style></Placemark>';
			}
		}
	}
	// Close the recordset
	mysql_free_result($result);
}	

// check the number of results found
if ($placemarks == 0) {
	// if no results found show message
	$kml[] = "<Placemark><name>No results found.</name>";	
	$kml[] = "<Style><LabelStyle><scale>1</scale><color>ff1400E6</color></LabelStyle> <IconStyle> <scale>0</scale> ";
	$x = ($east + $west) / 2;
	$y = ($north + $south) / 2;
	$kml[] = "</IconStyle></Style><Point><coordinates>".$x.",".$y.",0</coordinates> </Point></Placemark>";
}

// finish kml document
$kml[] = '</Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
mysql_close($connection);
?>