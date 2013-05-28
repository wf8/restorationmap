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
  
$year = mysql_real_escape_string($_GET['year']);
$data_type = mysql_real_escape_string($_GET['data_type']);

//$icon_color = "AA14B4FF";
//$icon_color = "AA3bff00";
//$icon_color = "AAF0A014";
//$icon_color = "AAF0FF14";
$icon_color = "AA143CFF";

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="bartelveg"><name>Bartel Veg Monitoring</name>';	
		
// number of kml placemarks shown
$placemarks = 0;		

// query to get all locations 
$query_locations = "SELECT * FROM bartel_veg_locations ORDER BY plot, quadrat";
$locations_results = mysql_query($query_locations, $connection);

// query to get all observations
$query_observations = "SELECT * FROM bartel_veg_observations WHERE year='$year' ORDER BY plot, quadrat";
$observations_results = mysql_query($query_observations, $connection);


// check if we got a result
if ($observations_results) {

	// put observations into an array
	for ($observations = array(); $row = mysql_fetch_assoc($observations_results); $observations[] = $row); 

	$i = 0;
	// loop through each location
	while ($location = mysql_fetch_assoc($locations_results)) {

		if ($data_type == "Weighted Native FQI")
			$scale = round( pow( ( ($observations[$i]["weighted_native_fqi"]+0.5) / 5 ) , ( 1 / 2 ) ) , 2);
		if ($data_type == "NativeNSpp")
			$scale = round( pow( ( ($observations[$i]["native_n_spp"]+0.5) / 5 ) , ( 1 / 2 ) ) , 2);
		if ($data_type == "Native Mean C")
			$scale = round( pow( ( ($observations[$i]["native_mean_c"]+0.5) / 5 ) , ( 1 / 2 ) ) , 2);
		if ($data_type == "Mean Wetness")
			$scale = round( pow( ( ($observations[$i]["mean_wetness"]+0.5) / 5 ) , ( 1 / 2 ) ) , 2);
		if ($data_type == "Brome Cover")
			$scale = round( pow( ( ($observations[$i]["brome_cover"]+0.5) / 10 ) , ( 1 / 2 ) ) , 2);
		if ($data_type == "Fescue Cover")
			$scale = round( pow( ( ($observations[$i]["fescue_cover"]+0.5) / 10 ) , ( 1 / 2 ) ) , 2);
		if ($data_type == "SOLALT Cover")
			$scale = round( pow( ( ($observations[$i]["solalt_cover"]+0.5) / 10 ) , ( 1 / 2 ) ) , 2);
		
		$kml_string = "<Placemark><description><![CDATA[";
		$kml_string = $kml_string . "<font face='arial'>Year: " . $year . '<br>';
		$kml_string = $kml_string . "Plot: " . $location["plot"] . '<br>';
		$kml_string = $kml_string . "Quadrat: " . $location["quadrat"] . '<br>';
		$kml_string = $kml_string . "Lat: " . round($location["latitude"], 5) . '<br>';
		$kml_string = $kml_string . "Lon: " . round($location["longitude"], 5) . '<br><br>';
		
		$kml_string = $kml_string . "NativeNSpp: " . $observations[$i]["native_n_spp"] . '<br>';
		$kml_string = $kml_string . "Native Mean C: " . ($observations[$i]["native_mean_c"] + 0) . '<br>';
		$kml_string = $kml_string . "Weighted Native FQI: " . ($observations[$i]["weighted_native_fqi"] + 0) . '<br>';
		$kml_string = $kml_string . "Mean Wetness: " . ($observations[$i]["mean_wetness"] + 0) . '<br>';
		$kml_string = $kml_string . "Brome Cover: " . $observations[$i]["brome_cover"] . '<br>';
		$kml_string = $kml_string . "Fescue Cover: " . $observations[$i]["fescue_cover"] . '<br>';
		$kml_string = $kml_string . "SOLALT Cover: " . $observations[$i]["solalt_cover"] . '<br>';

		$kml_string = $kml_string . "]]></description><Point><altitudeMode>clampToGround</altitudeMode><coordinates>";
		$kml_string = $kml_string . $location["longitude"] . "," . $location["latitude"] . ",0";
		$kml_string = $kml_string . "</coordinates></Point>";

		$kml_string = $kml_string . "<Style><IconStyle><color>".$icon_color."</color><colorMode>normal</colorMode><scale>";
		$kml_string = $kml_string . $scale;
		$kml_string = $kml_string . "</scale><Icon><href>http://www.habitatproject.org/restorationmap/kml/images/circle.png</href></Icon>";
		$kml_string = $kml_string . '<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/></IconStyle></Style></Placemark>';
		
		$kml[] = $kml_string;
		$placemarks++;
		$i++;
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
	$noKML[] = "</IconStyle></Style><Point><coordinates>-87.76285,41.53781,0</coordinates> </Point></Placemark></Document></kml>";
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