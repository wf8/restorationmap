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

 // Selects all the rows in the stewardship_site table.
 $query = 'SELECT * FROM stewardship_site WHERE 1 ORDER BY stewardship_site.name';
 $result = mysql_query($query);
 if (!$result) 
 	die('Invalid query: ' . mysql_error());

// pass the user_id as URL parameter
$user_id = mysql_real_escape_string($_GET[user_id]);
$user_id_param = '&amp;user_id=' . $user_id;


// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';

// generate dummy parameter
$dummyparam = '&amp;dummy=' . rand(0,1000000);

// Iterates through the rows, printing a node for each row.
while ($row = @mysql_fetch_assoc($result)) 
{	
	// add a folder for each site
	$kml[] = '<Folder>'; 
	$kml[] = '<name>' . $row['name'] . '</name>';
	$kml[] = '<flyToView>1</flyToView>'; 
	$kml[] = '<visibility>0</visibility>';	
	$kml[] = '<ListStyle><listItemType>checkOffOnly</listItemType></ListStyle>';
	
	 // get the border coordinates and id from the 'border' table.
	$site = $row['id'];
 	$query2 = "SELECT id, coordinates FROM border WHERE stewardshipsite_id = '$site'";
 	$result2 = mysql_query($query2);
 	if (!$result2) 
 		die('Invalid query: ' . mysql_error());
 	$row2 = @mysql_fetch_assoc($result2);
 	
 	// add the border
	$kml[] = ' <Placemark id="border-' . $row2['id'] . 'site' . $row['id'] . '">';
	$kml[] = ' <name>Border</name>'; 
	$km[] = '   <visibility>1</visibility>';   		
	$kml[] = '   <Style><BalloonStyle><displayMode>hide</displayMode></BalloonStyle>';
	$kml[] = '   <LineStyle><color>FFFFFFFF</color><width>2</width></LineStyle><PolyStyle><fill>0</fill></PolyStyle></Style>';
	$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';
 	
 	// add border coordinates to kml
	$kml[] = $row2['coordinates'];
	$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
	
	
	// then add network links for trails and shapes
	
 	$query3 = "SELECT * FROM trails WHERE stewardshipsite_id='$site'";
 	$result3 = mysql_query($query3);
 	if (mysql_num_rows($result3) != 0) {
		$kml[] = '<NetworkLink>'; 
		$kml[] = '<flyToView>0</flyToView>'; 
		$kml[] = '	<name>Trails</name>'; 
		$kml[] = '	<visibility>0</visibility>'; 
		$kml[] = '	<Link><viewRefreshMode>onRequest</viewRefreshMode>'; 
		$kml[] = '		<href>../php/trails-kml.php?id=' . $row['id'] . '</href>'; 
		$kml[] = '	</Link>'; 
		$kml[] = '</NetworkLink>'; 
 	}
 	
 	$query3 = "SELECT * FROM landmark WHERE stewardshipsite_id='$site'";
 	$result3 = mysql_query($query3);
 	if (mysql_num_rows($result3) != 0) {
		$kml[] = '<NetworkLink>'; 
		$kml[] = '<flyToView>0</flyToView>'; 
		$kml[] = '	<name>Geographic features / Landmarks</name>'; 
		$kml[] = '	<visibility>0</visibility>'; 
		$kml[] = '	<Link><viewRefreshMode>onRequest</viewRefreshMode>'; 
		$kml[] = '		<href>../php/landmark-kml.php?id=' . $row['id'] . $user_id_param . '</href>';
		$kml[] = '	</Link>';
		$kml[] = '</NetworkLink>';	
 	}
 	
 	$query3 = "SELECT * FROM brush WHERE stewardshipsite_id='$site'";
 	$result3 = mysql_query($query3);
 	if (mysql_num_rows($result3) != 0) {
		$kml[] = '<NetworkLink>'; 
		$kml[] = '<flyToView>0</flyToView>'; 
		$kml[] = '	<name>Brush and tree removal</name>'; 
		$kml[] = '	<visibility>0</visibility>'; 
		$kml[] = '	<Link><viewRefreshMode>onRequest</viewRefreshMode>'; 
		$kml[] = '		<href>../php/brush-kml.php?id=' . $row['id'] . $user_id_param . '</href>';
		$kml[] = '	</Link>';
		$kml[] = '</NetworkLink>';		
 	}		
	
	$query3 = "SELECT * FROM burns WHERE stewardshipsite_id='$site'";
 	$result3 = mysql_query($query3);
 	if (mysql_num_rows($result3) != 0) {	
		$kml[] = '<NetworkLink>';
		$kml[] = '<flyToView>0</flyToView>';
		$kml[] = '	<name>Prescribed burns</name>';
		$kml[] = '	<visibility>0</visibility>';
		$kml[] = '	<Link><viewRefreshMode>onRequest</viewRefreshMode>';
		$kml[] = '		<href>../php/burns-kml.php?id=' . $row['id'] . $user_id_param . '</href>';
		$kml[] = '	</Link>';
		$kml[] = '</NetworkLink>';
 	}
	
	$query3 = "SELECT * FROM seed WHERE stewardshipsite_id='$site'";
 	$result3 = mysql_query($query3);
 	if (mysql_num_rows($result3) != 0) {
		$kml[] = '<NetworkLink>';
		$kml[] = '<flyToView>0</flyToView>';
		$kml[] = '	<name>Seed collection and planting</name>';
		$kml[] = '	<visibility>0</visibility>';
		$kml[] = '	<Link><viewRefreshMode>onRequest</viewRefreshMode>';
		$kml[] = '		<href>../php/seed-kml.php?id=' . $row['id'] . $user_id_param . '</href>';
		$kml[] = '	</Link>';
		$kml[] = '</NetworkLink>';
 	}
	
	$query3 = "SELECT * FROM weed WHERE stewardshipsite_id='$site'";
 	$result3 = mysql_query($query3);
 	if (mysql_num_rows($result3) != 0) {
		$kml[] = '<NetworkLink>';
		$kml[] = '<flyToView>0</flyToView>';
		$kml[] = '	<name>Weed control</name>';
		$kml[] = '	<visibility>0</visibility>';
		$kml[] = '	<Link><viewRefreshMode>onRequest</viewRefreshMode>';
		$kml[] = '		<href>../php/weed-kml.php?id=' . $row['id'] . $user_id_param . '</href>';
		$kml[] = '	</Link>';
		$kml[] = '</NetworkLink>';
 	}
	
	$query3 = "SELECT * FROM other WHERE stewardshipsite_id='$site'";
 	$result3 = mysql_query($query3);
 	if (mysql_num_rows($result3) != 0) {
		$kml[] = '<NetworkLink>';
		$kml[] = '<flyToView>0</flyToView>';
		$kml[] = '	<name>Planning and other</name>';
		$kml[] = '	<visibility>0</visibility>';
		$kml[] = '	<Link><viewRefreshMode>onRequest</viewRefreshMode>';
		$kml[] = '		<href>../php/other-kml.php?id=' . $row['id'] . $user_id_param . '</href>';
		$kml[] = '	</Link>';
		$kml[] = '</NetworkLink>';
 	}
	
	// if kml_url is not NULL, add link to site specific layers
	if ( !is_null($row['kml_url']) )
	{
		$kml[] = '<NetworkLink>'; 
		$kml[] = '<flyToView>0</flyToView>'; 
		$kml[] = '	<name>Uploaded layers</name>'; 
		$kml[] = '	<open>0</open>'; 		
		$kml[] = '	<visibility>0</visibility>'; 
		$kml[] = '	<Link><viewRefreshMode>onRequest</viewRefreshMode>'; 
		$kml[] = '		<href>../kml/' . $row['kml_url'] . '</href>'; 
		$kml[] = '	</Link>'; 
		$kml[] = '</NetworkLink>	';
	}
	// close site folder 
	$kml[] = '</Folder>';

} 

// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>