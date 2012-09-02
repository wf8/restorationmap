<?php
session_start();

require('../restorationmap_config.php');

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, 'habitaw0_sc2011', 'b0b0l1nk');
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db('habitaw0_sc2011birdblitz', $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
 
$species = mysql_real_escape_string($_GET[species]);

// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="sc2011document"><name>Spring Creek 2011 Invasives Blitz</name>';

$kml[] = "<LookAt><longitude>-88.212580664947</longitude><latitude>42.11615626209326</latitude><altitude>0</altitude>";
$kml[] = "<heading>-3.33309209613983e-10</heading><tilt>0</tilt><range>11949.96973006292</range><altitudeMode>relativeToGround</altitudeMode>";
$kml[] = "<gx:altitudeMode>relativeToSeaFloor</gx:altitudeMode></LookAt>";

//$kml[] = "<Style id='both'><PolyStyle><color>EE1478FF</color></PolyStyle><LineStyle><color>FF1478FF</color><width>2</width></LineStyle></Style>";
//$kml[] = "<Style id='dense'><PolyStyle><color>AA1478FF</color></PolyStyle><LineStyle><color>CC1478FF</color><width>2</width></LineStyle></Style>";
//$kml[] = "<Style id='scattered'><PolyStyle><color>331478FF</color></PolyStyle><LineStyle><color>881478FF</color><width>2</width></LineStyle></Style>";

$kml[] = "<Style id='both'><PolyStyle><color>501400FF</color></PolyStyle><LineStyle><color>FF1400FF</color><width>2</width></LineStyle></Style>";
$kml[] = "<Style id='dense'><PolyStyle><color>501478FF</color></PolyStyle><LineStyle><color>FF1478FF</color><width>2</width></LineStyle></Style>";
$kml[] = "<Style id='scattered'><PolyStyle><color>5014F0FF</color></PolyStyle><LineStyle><color>FF14F0FF</color><width>2</width></LineStyle></Style>";

$species_list = array(array("oriental_bittersweet", "Oriental Bittersweet"));		
$species_list[] = array("garlic_mustard", "Garlic Mustard");
$species_list[] = array("japanese_barberry", "Japanese Barberry");
$species_list[] = array("reed_canary_grass", "Reed Canary Grass");
$species_list[] = array("purple_loosestrife", "Purple Loosestrife");
$species_list[] = array("common_reed", "Common Reed");
$species_list[] = array("cattail", "Cattail");
$species_list[] = array("wild_parsnip", "Wild Parsnip");
$species_list[] = array("crown_vetch", "Crown Vetch");
$species_list[] = array("leafy_spurge", "Leafy Spurge");
$species_list[] = array("sweet_clover", "Sweet Clover");
$species_list[] = array("teasel", "Teasel"); 


// build query 
if ($species == 'all')
	$query = "SELECT * FROM subunits";
else
	$query = 'SELECT full_name, coordinates, '.$species.' FROM subunits WHERE '.$species.' != 0';
			
// number of kml placemarks shown
$placemarks = 0;

// Perform Query
$result = mysql_query($query, $connection);
// Check result
if ($result) {
	
	$lastLocation = null;	
	// Start a while loop to fetch each record in the result set
	while ($row = mysql_fetch_assoc($result)) {
		
		if ($species != 'all') {
			$kml[] = "<Placemark><name>".htmlspecialchars($row["full_name"])."</name>";
			$kml[] = "<description><![CDATA[<b>";
		
			$i = 0;
			while($i < count($species_list)) {
				if ($species_list[$i][0] === $species) 
					$kml[] = $species_list[$i][1] . ":</b> <br> ";
				$i++;
			} 
			
			if ($row[$species] == 1) {
				$kml[] = "- scattered<br>";	
				$kml[] = "]]></description><styleUrl>";
				$kml[] = '#scattered';
			}
			if ($row[$species] == 2) {
				$kml[] = "- dense<br>";	
				$kml[] = "]]></description><styleUrl>";
				$kml[] = '#dense';
			}
			if ($row[$species] == 3) {
				$kml[] = "- scattered and dense<br>";	
				$kml[] = "]]></description><styleUrl>";
				$kml[] = '#both';
			}		
		
			$kml[] = "</styleUrl><Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>";
			$kml[] = $row["coordinates"];		
			$kml[] = "</coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>";
			$placemarks++;
			
		} else {
			
			$density = 0;
			$species_for_info_bubble = '';
			
			$i = 0;
			while($i < count($species_list)) {
				if ($row[$species_list[$i][0]] != 0) {
					if ($row[$species_list[$i][0]] > $density)
						$density = $row[$species_list[$i][0]];
					if ($row[$species_list[$i][0]] == 1)
						$species_for_info_bubble = $species_for_info_bubble ."<b>". $species_list[$i][1].":</b> <br> - scattered<br>";
					if ($row[$species_list[$i][0]] == 2)
						$species_for_info_bubble = $species_for_info_bubble ."<b>". $species_list[$i][1].":</b> <br> - dense<br>";
					if ($row[$species_list[$i][0]] == 3)
						$species_for_info_bubble = $species_for_info_bubble ."<b>". $species_list[$i][1].":</b> <br> - dense and scattered<br>";		
				}
				$i++;
			}
			if ($density != 0) {
				$kml[] = "<Placemark><name>".htmlspecialchars($row["full_name"])."</name>";
				$kml[] = "<description><![CDATA[";
				$kml[] = $species_for_info_bubble;
				$kml[] = "]]></description><styleUrl>";
				if ($density == 1)
					$kml[] = '#scattered';
				if ($density == 2)
					$kml[] = '#dense';
				if ($density == 3)
					$kml[] = '#both';
				$kml[] = "</styleUrl><Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>";
				$kml[] = $row["coordinates"];		
				$kml[] = "</coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>";
				$placemarks++;
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
	$kml[] = "<Style><LabelStyle><scale>1</scale><color>ff1f00ff</color></LabelStyle> <IconStyle> <scale>0</scale> ";
	$kml[] = "</IconStyle></Style><Point><coordinates>-88.216696,42.118758,0</coordinates> </Point></Placemark>";
}

// finish kml document
$kml[] = '</Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
mysql_close($connection);
?>