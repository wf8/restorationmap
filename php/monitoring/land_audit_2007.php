<?php
session_start();

require('../restorationmap_config.php');

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, 'habitaw0_landaud', 'fq1fq1', true);
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db('habitaw0_land_audit', $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
  
$icon_color = "AAF0FF14";

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="land_audit_doc"><name>Cook County Land Audit 2007-2008</name>';	

// query to get all locations within the entire visible bounding box
$query = "SELECT * FROM 2007_data ORDER BY Triad_Number";

// perform query
$result = mysql_query($query, $connection);

// check if we got a result
if ($result) {

	$last_triad = null;	
	$preserve_name = null;
	$plant_community = null;
	$native_fqi = 0;
	$native_spp = 0;
	$lat = 0;
	$lon = 0;
	$plots_per_triad = 1;
	
	// Start a while loop to go through each plot
	while ($row = mysql_fetch_assoc($result)) {
		$this_triad = $row["Triad_Number"];
		if ($this_triad !== $last_triad) {	
			if ($last_triad != null) {			
				// we've finished a triad so now
				// calculate the means for the triad
				$native_fqi = round( ( $native_fqi / $plots_per_triad ), 1);
				$native_spp = round( ( $native_spp / $plots_per_triad ), 1);
				// determine floristic quality
				// FQI 0-4 = poor
				// FQI 4-7 = fair
				// FQI 7-9 = good
				// FQI 9 or greater = excellent
				if ($native_fqi < 4) {
					$floristic_quality = "poor";
					$icon_color = "AA1400FF";
				} else if ($native_fqi < 7) {
					$floristic_quality = "fair";
					$icon_color = "AA1478FF";
				} else if ($native_fqi < 9) {
					$floristic_quality = "good";
					$icon_color = "AA14F0FF";
				} else {
					$floristic_quality = "excellent";
					$icon_color = "AA14F000";
				}
				
				// make placemark for the triad
				$kml[] = "<Placemark><description>";
				$kml[] = "<![CDATA[";
				$kml[] = "<b>2007-2008 Land Audit</b><br><br>";
				$kml[] = "<b>" . $preserve_name . "</b><br><br>";
				$kml[] = "<b>Triad:</b><br>" . $last_triad . "<br>";
				$kml[] = "<b>Floristic Quality:</b><br>" . $floristic_quality . "<br>";
				$kml[] = "<b>Native FQI:</b><br>" . $native_fqi . "<br>";
				$kml[] = "<b>Number of native species<br>per square 1/4 meter:</b><br>" . $native_spp . "<br>";
				$kml[] = "<b>Plant Community:</b><br>" . $plant_community . "<br>";	
				$kml[] = "]]>";
				$kml[] = "</description>";
				$kml[] = "<Point><altitudeMode>clampToGround</altitudeMode><coordinates>";
				$kml[] = $lon . "," . $lat . ",0";
				$kml[] = "</coordinates></Point>";
				$kml[] = "<Style><IconStyle><color>".$icon_color."</color><colorMode>normal</colorMode><scale>";
				$kml[] = round( pow( ( $native_fqi / 10 ) , ( 1 / 2 ) ) , 2);
		  		$kml[] = "</scale><Icon><href>http://www.habitatproject.org/restorationmap/kml/images/circle.png</href></Icon>";
		 		$kml[] = '<hotSpot x="0.5" y="0.5" xunits="fraction" yunits="fraction"/></IconStyle></Style></Placemark>';
			}
			// get the info for the new triad
			$plant_community = $row["Plant_Community"];
			$preserve_name = $row["Preserve_Name"];
			$lat = $row["Lat_Triad_Center_Pt"];
			$lon = $row["Long_Triad_Center_Pt"];
			$native_fqi = $row["Weighted_Native_FQI"];
			$native_spp = $row["NnativeSpp"];
			$plots_per_triad = 1;
		} else {
			// we are still on the same triad, so we need to add the fqi and species#
			$native_fqi = $native_fqi + $row["Weighted_Native_FQI"];
			$native_spp = $native_spp + $row["NnativeSpp"];	
			$plots_per_triad++;		
		}
		$last_triad = $this_triad;
	}
	// Close the recordset
	mysql_free_result($result);
	// finish the kml document, set headers and echo it
	$kml[] = '</Document>';
	$kml[] = '</kml>';
	$kmlOutput = join("\n", $kml);
	header('Content-type: application/vnd.google-earth.kml+xml');
	echo $kmlOutput;
	mysql_close($connection);
}
?>