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

// query to get all observations
$query_observations = "SELECT * FROM bartel_veg_observations ORDER BY year";
$observations_results = mysql_query($query_observations, $connection);

$options = "<select id='bartel_veg_year'>";
$last_year = 1900;

while ($observations = mysql_fetch_assoc($observations_results)) {
	if ($observations['year'] !== $last_year) {
		$options = $options . "<option>" . $observations['year'] . "</option>";
		$last_year = $observations['year'];
	}
}

$options = $options . "</select>";
echo $options;

mysql_close($connection);
?>