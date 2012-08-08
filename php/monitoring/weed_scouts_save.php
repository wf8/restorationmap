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
	

$date = mysql_real_escape_string($_GET[date]);
$latitude = trim(mysql_real_escape_string($_GET[latitude]));
$longitude = trim(mysql_real_escape_string($_GET[longitude]));
$weed = mysql_real_escape_string($_GET[weed]);
$abundance = mysql_real_escape_string($_GET[abundance]);
$name = trim(mysql_real_escape_string($_GET[name]));
$note = trim(mysql_real_escape_string($_GET[note]));

$sql="INSERT INTO weed_scouts (date, latitude, longitude, weed, abundance, name, note) VALUES ('$date', '$latitude', '$longitude', '$weed', '$abundance', '$name', '$note')";

if (!mysql_query($sql,$connection)) 
	die('Error: ' . mysql_error());

echo "success";
mysql_close($connection);
?>