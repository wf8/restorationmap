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
$user_id = mysql_real_escape_string($_GET['user_id']);
$user_id_param = '&amp;user_id=' . $user_id;

// we also need to pass the opacity as a URL parameter.
// first get the opacity from the database (it may have been updated)
$opacity_query = "SELECT opacity FROM users WHERE id = '$user_id'";
$opacity_result = mysql_query($opacity_query);
$userData = mysql_fetch_assoc($opacity_result);
$opacity_param = '&amp;opacity=' . $userData['opacity'];


// Creates an array of strings to hold the lines of the KML file.
$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
$kml[] = ' <Document>';

// generate dummy parameter
$dummyparam = '&amp;dummy=' . rand(0,1000000);

// Iterates through the rows, printing a node for each row.
while ($row = @mysql_fetch_assoc($result)) 
{	
	// skip the openlands easements sites
	if ($row['id'] < 47 || 84 < $row['id']) {
	
	
		if ($row['name'] == "Openlands Lakeshore Preserve") {
		
			// insert Openlands easements folder
			$kml[] = '<Folder>'; 
			$kml[] = '<name>Openlands Easements</name>';
			$kml[] = '<flyToView>1</flyToView>'; 
			$kml[] = '<visibility>0</visibility>';	
			$kml[] = '<ListStyle><listItemType>checkOffOnly</listItemType></ListStyle>';
			
			$easement_id = 47;
			// add a folder for each easement
			while ($easement_id < 85) {
				
				$query_to_get_name = "SELECT name FROM stewardship_site WHERE id = '$easement_id'";
				$easement_result = mysql_query($query_to_get_name);
				$this_easement = @mysql_fetch_assoc($easement_result);
				
				// check if the easement is password protected
				// C2, C3, L15,  D2, K3, L5, L7, L13
				$ease_name = $this_easement['name'];
				$authorized = true;
				if ($ease_name == "C2" || $ease_name == "C3" || $ease_name == "L15"
					|| $ease_name == "D2" || $ease_name == "K3" || $ease_name == "L05"
					|| $ease_name == "L07" || $ease_name == "L13") {		
					if ($user_id == '14' || $user_id == '17' || $user_id == '28' || $user_id == '37')
						$authorized = true;
					else
						$authorized = false;
				}
				if ($authorized) {
					// add a folder for each easement
					$kml[] = '<Folder>'; 
					$kml[] = '<name>' . $ease_name . '</name>';
					$kml[] = '<flyToView>1</flyToView>'; 
					$kml[] = '<visibility>0</visibility>';	
					$kml[] = '<ListStyle><listItemType>checkOffOnly</listItemType></ListStyle>';
					
					 // get the border coordinates and id from the 'border' table.
					$site = $easement_id;
					$query2 = "SELECT id, coordinates FROM border WHERE stewardshipsite_id = '$site'";
					$result2 = mysql_query($query2);
					if (!$result2) 
						die('Invalid query: ' . mysql_error());
					$row2 = @mysql_fetch_assoc($result2);
					
					// add the border
					$kml[] = ' <Placemark id="border-' . $row2['id'] . 'site' . $easement_id . '">';
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
						$kml[] = '		<href>../php/trails-kml.php?id=' . $easement_id . '</href>'; 
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
						$kml[] = '		<href>../php/landmark-kml.php?id=' . $easement_id . $user_id_param . $opacity_param . '</href>';
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
						$kml[] = '		<href>../php/brush-kml.php?id=' . $easement_id . $user_id_param . $opacity_param . '</href>';
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
						$kml[] = '		<href>../php/burns-kml.php?id=' . $easement_id . $user_id_param . $opacity_param . '</href>';
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
						$kml[] = '		<href>../php/seed-kml.php?id=' . $easement_id . $user_id_param . $opacity_param . '</href>';
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
						$kml[] = '		<href>../php/weed-kml.php?id=' . $easement_id . $user_id_param . $opacity_param . '</href>';
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
						$kml[] = '		<href>../php/other-kml.php?id=' . $easement_id . $user_id_param . $opacity_param . '</href>';
						$kml[] = '	</Link>';
						$kml[] = '</NetworkLink>';
					}
					
					// close folder for this easement 
					$kml[] = '</Folder>';
				}
				$easement_id++;
			}
			// close easements folder
			$kml[] = '</Folder>';
		}
	
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
			$kml[] = '		<href>../php/landmark-kml.php?id=' . $row['id'] . $user_id_param . $opacity_param . '</href>';
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
			$kml[] = '		<href>../php/brush-kml.php?id=' . $row['id'] . $user_id_param . $opacity_param . '</href>';
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
			$kml[] = '		<href>../php/burns-kml.php?id=' . $row['id'] . $user_id_param . $opacity_param . '</href>';
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
			$kml[] = '		<href>../php/seed-kml.php?id=' . $row['id'] . $user_id_param . $opacity_param . '</href>';
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
			$kml[] = '		<href>../php/weed-kml.php?id=' . $row['id'] . $user_id_param . $opacity_param . '</href>';
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
			$kml[] = '		<href>../php/other-kml.php?id=' . $row['id'] . $user_id_param . $opacity_param . '</href>';
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
} 

// End XML file
$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>