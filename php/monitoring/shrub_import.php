<?php 

require('../restorationmap_config.php');

// connect to MySQL server.
$connection = mysql_connect ($db_server, $shrub_user, $shrub_password, true);
if (!$connection) 
  die('Not connected : ' . mysql_error());

// set active MySQL database.
$db_selected = mysql_select_db($shrub_database, $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());

// create observations table
// $sql = "CREATE TABLE observations(id INT NOT NULL, PRIMARY KEY(id), location_id INT NOT NULL, INDEX(location_id), percent_grass VARCHAR(15), percent_forbs VARCHAR(15), grass_to_forb_ration VARCHAR(15), percent_rosa VARCHAR(15), percent_woody_knee VARCHAR(15), percent_woody_knee_waist VARCHAR(15), percent_woody_1_meter VARCHAR(15), percent_woody_waist_head VARCHAR(15), percent_woody_head VARCHAR(15), percent_woody_total VARCHAR(15), bl_ss_habitat_suitability VARCHAR(20), m_habitat_suitability VARCHAR(20), gs_habitat_suitability VARCHAR(20), 4_spp_habitat_suitability VARCHAR(20), comments TEXT, date_recorded VARCHAR(15), year INT)";
// mysql_query($sql); 
// echo "observations table created!";

// create locations table
// $sql = "CREATE TABLE locations(id INT NOT NULL, PRIMARY KEY(id), site VARCHAR(30), transect INT, quadrat INT, latitude VARCHAR(15), longitude VARCHAR(15))";
// mysql_query($sql); 
// echo "locations table created!";


if ($_FILES["upload_file"]["error"] == 4)
	$result = "Error: Please select a file.";
else if ($_FILES["upload_file"]["error"] > 0)
	$result = "Error: " . $_FILES["upload_file"]["error"];
else if	($_FILES["upload_file"]["size"] > 100000000) // 100 kb restriction // made it 100000kb temporarily
		$result = "Error: File must be under 750 mb.";
else {
	if (($handle = fopen($_FILES['upload_file']['tmp_name'], "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 0, "\t", '"')) !== FALSE) {
			// do not insert if there is already an observation for that location and year
			$sql = "SELECT * FROM observations WHERE location_id='$data[0]' AND year='$data[17]'";
			$existing_observation = mysql_query($sql);
//			if (!$existing_observation) {
			if (mysql_num_rows($existing_observation) == 0) {
				$sql="INSERT INTO observations (location_id, percent_grass, percent_forbs, grass_to_forb_ration, percent_rosa, percent_woody_knee, percent_woody_knee_waist, percent_woody_1_meter, percent_woody_waist_head, percent_woody_head, percent_woody_total, bl_ss_habitat_suitability, m_habitat_suitability, gs_habitat_suitability, 4_spp_habitat_suitability, comments, date_recorded, year) VALUES ('$data[0]', '$data[1]', '$data[2]', '$data[3]', '$data[4]', '$data[5]', '$data[6]', '$data[7]', '$data[8]', '$data[9]', '$data[10]', '$data[11]', '$data[12]', '$data[13]', '$data[14]', '$data[15]', '$data[16]', '$data[17]')";
				mysql_query($sql);	
			}
		}
		fclose($handle);
		$result = "";
	}
}
?> 
<html>
<head>
<script language="javascript" type="text/javascript">
	var result = <?= json_encode($result); ?>;
	window.top.window.stop_shrub_survey_upload(result);
</script>
</head>
<body></body>
</html>