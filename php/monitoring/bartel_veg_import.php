<?php 

require('../restorationmap_config.php');

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
	die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
	die ('Can\'t use db : ' . mysql_error());

// create bartel_veg_observations table
// $sql = "CREATE TABLE bartel_veg_observations(id INT NOT NULL, PRIMARY KEY(id), plot INT NOT NULL, quadrat INT NOT NULL, year INT NOT NULL, native_n_spp INT NOT NULL, native_mean_c VARCHAR(15) NOT NULL DEFAULT '0', weighted_native_fqi VARCHAR(15) NOT NULL DEFAULT '0', mean_wetness VARCHAR(15) NOT NULL DEFAULT '0', brome_cover INT NOT NULL, fescue_cover INT NOT NULL, solalt_cover INT NOT NULL)";
// mysql_query($sql); 
// echo "bartel_veg_observations table created!";

// create locations table
// $sql = "CREATE TABLE bartel_veg_locations(id INT NOT NULL, PRIMARY KEY(id), plot INT, quadrat INT, latitude VARCHAR(15), longitude VARCHAR(15))";
// mysql_query($sql); 
// echo "locations table created!";

if ($_FILES["upload_file"]["error"] == 4)
	$result = "Error: Please select a file.";
else if ($_FILES["upload_file"]["error"] > 0)
	$result = "Error: " . $_FILES["upload_file"]["error"];
else if	($_FILES["upload_file"]["size"] > 100000000) // 100 kb restriction // made it 100000kb temporarily
	$result = "Error: File must be under 750 mb.";
else {
	$result = "";
	if (($handle = fopen($_FILES['upload_file']['tmp_name'], "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 0, "\t", '"')) !== FALSE) {
			// do not insert if there is already an observation for that plot/quadrat and year
			$sql = "SELECT * FROM bartel_veg_observations WHERE plot='$data[0]' AND quadrat='$data[1]' AND year='$data[2]'";
			$existing_observation = mysql_query($sql);
			if (mysql_num_rows($existing_observation) == 0) {
				$sql="INSERT INTO bartel_veg_observations (plot, quadrat, year, native_n_spp, native_mean_c, weighted_native_fqi, mean_wetness, brome_cover, fescue_cover, solalt_cover) VALUES ('$data[0]', '$data[1]', '$data[2]', '$data[3]', '$data[4]', '$data[5]', '$data[6]', '$data[7]', '$data[8]', '$data[9]')";
				mysql_query($sql);	
			} else {
				$result = "Error: Observations for plot #".$data[0]." quadrat #".$data[1]." for the year ".$data[2]." already exist.";
				break;
			}
		}
		fclose($handle);	
	}
}
?> 
<html>
<head>
<script language="javascript" type="text/javascript">
	var result = <?= json_encode($result); ?>;
	window.top.window.stop_bartel_veg_upload(result);
</script>
</head>
<body></body>
</html>