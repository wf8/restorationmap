<?php
session_start();

require('../restorationmap_config.php');

set_time_limit(0);

 // Opens a connection to a MySQL server.
$connection = mysql_connect($db_server, "nat_comm", "n6tur6l");
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db("fpdcc_natural_communities", $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
 
$nat_comm_how = mysql_real_escape_string($_GET['nat_comm_how']);
$nat_comm_opacity = mysql_real_escape_string($_GET['nat_comm_opacity']);

if ($nat_comm_opacity == '0')
	$opacity = '00';
if ($nat_comm_opacity == '20')
	$opacity = '33';
if ($nat_comm_opacity == '40')
	$opacity = '64';
if ($nat_comm_opacity == '60')
	$opacity = '99';
if ($nat_comm_opacity == '80')
	$opacity = 'CC';
if ($nat_comm_opacity == '100')
	$opacity = 'FF';

$reforestation = '1400FF'; // html=FF0014
$prairie = '14F0FF'; // html=FFF014
$crop = '78DCF0'; // html=F0DC78
$eurasian_meadow = '14B4D2'; // html=D2B414
$dolomite_cliff = '143C96'; // html=963C14
$savanna = '1478FF'; // html=FF7814
$sedge_meadow = '78FFB4'; // html=B4FF78
$woodland = '14F000'; // html=00F014
$forest = '147800'; // html=007814
$fen = 'B4FF14'; // html=14FFB4
$marsh = 'F0C814'; // html=14C8F0
$shrubland = '785AF0'; // html=F05A78
$unassociated_woody_growth = '4F1878'; // html=78184F
$unmanaged = 'DCDCDC'; // html=DCDCDC

// set line width and line opacity
if ($nat_comm_how != 'outline' && $opacity == '00') {
	$width = '4';
	$line_opacity = 'FF';
} else {
	$width = '2';
	$line_opacity = 'AA';
}
// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document id="fpdcc_nat_comm"><name>FPDCC Natural Communities</name>';

// old outline color AAF0FF14
$kml[] = "<Style id='outline_style'><PolyStyle><fill>0</fill></PolyStyle><LineStyle><color>AAFFFFFF</color><width>2</width></LineStyle></Style>";
$kml[] = "<Style id='Reforestation_style'><PolyStyle><fill>1</fill><color>".$opacity.$reforestation."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$reforestation."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Prairie_style'><PolyStyle><fill>1</fill><color>".$opacity.$prairie."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$prairie."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Crop_style'><PolyStyle><fill>1</fill><color>".$opacity.$crop."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>AA".$crop."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Eurasian_Meadow_style'><PolyStyle><fill>1</fill><color>".$opacity.$eurasian_meadow."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$eurasian_meadow."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Dolomite_Cliff_style'><PolyStyle><fill>1</fill><color>".$opacity.$dolomite_cliff."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$dolomite_cliff."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Savanna_style'><PolyStyle><fill>1</fill><color>".$opacity.$savanna."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$savanna."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Sedge_Meadow_style'><PolyStyle><fill>1</fill><color>".$opacity.$sedge_meadow."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$sedge_meadow."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Woodland_style'><PolyStyle><fill>1</fill><color>".$opacity.$woodland."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$woodland."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Forest_style'><PolyStyle><fill>1</fill><color>".$opacity.$forest."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$forest."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Fen_style'><PolyStyle><fill>1</fill><color>".$opacity.$fen."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$fen."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Marsh_style'><PolyStyle><fill>1</fill><color>".$opacity.$marsh."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$marsh."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Shrubland_style'><PolyStyle><fill>1</fill><color>".$opacity.$shrubland."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$shrubland."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='UW_style'><PolyStyle><fill>1</fill><color>".$opacity.$unassociated_woody_growth."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$unassociated_woody_growth."</color><width>".$width."</width></LineStyle></Style>";
$kml[] = "<Style id='Unmanaged_style'><PolyStyle><fill>1</fill><color>".$opacity.$unmanaged."</color><colorMode>normal</colorMode></PolyStyle><LineStyle><color>".$line_opacity.$unmanaged."</color><width>".$width."</width></LineStyle></Style>";



// build query 
$query = "SELECT * FROM natural_communities";
			
// number of kml placemarks shown
$placemarks = 0;

// Perform Query
$result = mysql_query($query, $connection);
// Check result
if ($result) {
	
	// Start a while loop to fetch each record in the result set
	while ($row = mysql_fetch_assoc($result)) {
		
		$kml[] = "<Placemark><name>".htmlspecialchars($row["NATCOMNOW"])."</name>";
		$kml[] = "<description><![CDATA[";
		$kml[] = "<b>Management Unit:</b><br>".$row["CLSMGDPRTN"]."<br>";
		$kml[] = "<b>Natural Community Now:</b><br>".$row["NATCOMNOW"]."<br>";
		$kml[] = "<b>Natural Community Future:</b><br>".$row["NATCOMFUTU"]."<br>";
		$kml[] = "<b>Current Condition:</b><br>".$row["CONDNOW"]."<br>";
		$kml[] = "<b>Acreage:</b><br>".$row["ACREAGE"]."<br>";
		$kml[] = "]]></description>";
		// set the style of the polygon
		if ($nat_comm_how === 'outline') {
			$kml[] = "<styleUrl>#outline_style</styleUrl>";
		} else if ($nat_comm_how === 'now') {
				if ($row["NATCOMNOW"] === 'Eurasian Meadow')
					$kml[] = "<styleUrl>#Eurasian_Meadow_style</styleUrl>";
				else if ($row["NATCOMNOW"] === 'Dolomite Cliff')
					$kml[] = "<styleUrl>#Dolomite_Cliff_style</styleUrl>";
				else if ($row["NATCOMNOW"] === 'Sedge Meadow')
					$kml[] = "<styleUrl>#Sedge_Meadow_style</styleUrl>";
				else
					$kml[] = "<styleUrl>#" . $row["NATCOMNOW"] . "_style</styleUrl>";
		} else if ($nat_comm_how === 'future') {
				if ($row["NATCOMFUTU"] === 'Eurasian Meadow')
					$kml[] = "<styleUrl>#Eurasian_Meadow_style</styleUrl>";
				else if ($row["NATCOMFUTU"] === 'Dolomite Cliff')
					$kml[] = "<styleUrl>#Dolomite_Cliff_style</styleUrl>";
				else if ($row["NATCOMFUTU"] === 'Sedge Meadow')
					$kml[] = "<styleUrl>#Sedge_Meadow_style</styleUrl>";
				else
					$kml[] = "<styleUrl>#" . $row["NATCOMFUTU"] . "_style</styleUrl>";		
		}
		$kml[] = "<Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>";
		$kml[] = $row["coordinates"];		
		$kml[] = "</coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>";
		$placemarks++;		
			
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
?>