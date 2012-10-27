<?php
session_start();

require('../restorationmap_config.php');

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, 'habitaw0_shrub', 'h0neysuckle');
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db('habitaw0_shrub_survey', $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
  
$year = mysql_real_escape_string($_GET['year']);
$data_type = mysql_real_escape_string($_GET['data_type']);

// determine circle color and metric
if ($data_type == '1') {
	$icon_color = "AA14B4FF";
	$column = "bl_ss_habitat_suitability";
	$title = "Suitability+for+Bobolinks+and+Savanna+Sparrows";
} else if ($data_type == '2') {
	$icon_color = "AA3bff00";
	$column = "m_habitat_suitability";
	$title = "Habitat+suitability+for+Meadowlarks";
} else if ($data_type == '3') {
	$icon_color = "AAF0A014";
	$column = "gs_habitat_suitability";
	$title = "Suitability+for+Grasshopper+Sparrows";
} else if ($data_type == '4') {
	$icon_color = "AAF0FF14";
	$column = "4_spp_habitat_suitability";
	$title = "Suitability+for+all+4+focal+species";
} else if ($data_type == '5') {
	$icon_color = "AA143CFF";
	$column = "percent_woody_total";
	$title = "Percent+Woody+Cover";
}	


// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="shrubdocument"><name>Shrub Survey</name>';	
		
// number of kml placemarks shown
$placemarks = 0;		

// query to get all locations 
$query_locations = "SELECT * FROM locations ORDER BY id";
$locations_results = mysql_query($query_locations, $connection);

// query to get all observations
$query_observations = "SELECT * FROM observations ORDER BY location_id, year";
$observations_results = mysql_query($query_observations, $connection);


// check if we got a result
if ($observations_results) {

	// put observations into an array
	for ($observations = array(); $row = mysql_fetch_assoc($observations_results); $observations[] = $row); 

	$i = 0;
	// loop through each location
	while ($location = mysql_fetch_assoc($locations_results)) {

		// get all data from all years for this location and construct strings for chart
		$data_string = '';
		$year_string = '';
		while($observations[$i]["location_id"] == $location["id"]) {
		
			$the_data = trim($observations[$i][$column]);		

			if($observations[$i]["year"] == $year)
				$current_year_data = $the_data;		
				
			if ($the_data == 'suitable habitat')
				$the_data = 3;
			else if ($the_data == 'marginally suitable')
				$the_data = 2;
			else if ($the_data == 'unsuitable habitat')
				$the_data = 1;

			// 1,3,2,3,1,2
			if ($data_string == '')
				$data_string = $the_data;
			else
				$data_string = $data_string . ',' . $the_data;

			// |2009|2010|2011|2012|2013|2014
			$year_string = $year_string . '|' . $observations[$i]["year"];

			$i++;
		}

		$kml_string = "<Placemark><description><![CDATA[";

		$kml_string = $kml_string . "<font face='arial'>Location ID: " . $location["id"] . '<br>';
		$kml_string = $kml_string . "Lat: " . round($location["latitude"], 5) . '<br>';
		$kml_string = $kml_string . "Lon: " . round($location["longitude"], 5) . '<br><br>';
		if ($data_type == '5') {
			$kml_string = $kml_string . '<img src="http://chart.googleapis.com/chart?chxl=0:|0%25|50%25|100%25|1:'.$year_string.'&chxp=0,0,50,100&chxr=1,0,1&chxtc=0,3&chxt=y,x&chbh=a,5&chs=300x225&cht=bvg&chco=224499&chd=t:'.$data_string.'&chtt='.$title.'" width="300" height="225" />';
		} else {
			$kml_string = $kml_string . '<img src="http://chart.googleapis.com/chart?chxl=0:|unsuitable|suitable|marginally+suitable|1:'.$year_string.'&chxp=0,1,3,2&chxr=0,0,3|1,0,1&chxtc=0,3&chxt=y,x&chbh=a&chs=300x225&cht=bvg&chco=224499&chds=0,3&chd=t:'.$data_string.'&chtt='.$title.'" width="300" height="225" alt="title" />';
		}

		$kml_string = $kml_string . "]]></description><Point><altitudeMode>clampToGround</altitudeMode><coordinates>";
		$kml_string = $kml_string . $location["longitude"] . "," . $location["latitude"] . ",0";
		$kml_string = $kml_string . "</coordinates></Point>";
		
		// set scale for placemark icon
		if ($current_year_data == 'suitable habitat')
			$scale = 20;
		else if ($current_year_data == 'marginally suitable')
			$scale = 5;
		else if ($current_year_data == 'unsuitable habitat')
			$scale = 0.25;
		else 
			$scale = 20 * ( ($current_year_data + 0.25) / 100);

		$kml_string = $kml_string . "<Style><IconStyle><color>".$icon_color."</color><colorMode>normal</colorMode><scale>";
		$kml_string = $kml_string . round( pow( ( $scale / 10 ) , ( 1 / 3 ) ) , 2);
		$kml_string = $kml_string . "</scale><Icon><href>http://www.habitatproject.org/restorationmap/kml/images/circle.png</href></Icon>";
		$kml_string = $kml_string . '<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/></IconStyle></Style></Placemark>';
		
		$kml[] = $kml_string;
		$placemarks++;
	}
}	

// check the number of results found
if ($placemarks == 0) {
	// if no results found show message
	$noKML = array("<?xml version=\"1.0\" encoding=\"UTF-8\"?>");
	$noKML[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
	$noKML[] = ' <Document id="shrubdocument"><name>Shrub Survey</name>';
	$noKML[] = "<Placemark><name>No results found.</name>";	
	$noKML[] = "<Style><LabelStyle><scale>1</scale><color>ff1400E6</color></LabelStyle> <IconStyle> <scale>0</scale> ";
	$noKML[] = "</IconStyle></Style><Point><coordinates>-87.83784484863281,42.14151763916016,0</coordinates> </Point></Placemark></Document></kml>";
	$kmlOutput = join("\n", $noKML);
	header('Content-type: application/vnd.google-earth.kml+xml');
	echo $kmlOutput;
} else {
	// otherwise all is good so finish the kml document
	$kml[] = '</Document>';
	$kml[] = '</kml>';
	$kmlOutput = join("\n", $kml);
	header('Content-type: application/vnd.google-earth.kml+xml');
	echo $kmlOutput;
}
mysql_close($connection);
?>