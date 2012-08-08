<?php
require('../restorationmap_config.php');

set_time_limit(0);

function outputCSV($data) {
    $outstream = fopen("php://output", "w");
    function __outputCSV(&$vals, $key, $filehandler) {
        fputcsv($filehandler, $vals); // add parameters if you want
    }
    array_walk($data, "__outputCSV", $outstream);
    fclose($outstream);
}

$array = array(
		array("Date", "Weed", "Abundance", "Longitude", "Latitude", "Note", "Name"));

 // Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
	die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
	die ('Can\'t use db : ' . mysql_error());
	

$startDate = mysql_real_escape_string($_GET[startDate]);
$endDate = mysql_real_escape_string($_GET[endDate]);
$weed = mysql_real_escape_string($_GET[weed]);
$abundance = mysql_real_escape_string($_GET[abundance]);

if ($startDate == 'all') {
	if ($weed == 'All' && $abundance == 'All')
		$query = "SELECT * FROM weed_scouts ORDER BY date ASC";
	if ($weed != 'All' && $abundance == 'All')
		$query = "SELECT * FROM weed_scouts WHERE LCASE(weed)=LCASE('$weed') ORDER BY date ASC";
	if ($weed == 'All' && $abundance != 'All')
		$query = "SELECT * FROM weed_scouts WHERE LCASE(abundance)=LCASE('$abundance') ORDER BY date ASC";
	if ($weed != 'All' && $abundance != 'All')
		$query = "SELECT * FROM weed_scouts WHERE LCASE(abundance)=LCASE('$abundance') AND LCASE(weed)=LCASE('$weed') ORDER BY date ASC";
} else {
	if ($weed == 'All' && $abundance == 'All')
		$query = "SELECT * FROM weed_scouts WHERE date >= '$startDate' AND date <= '$endDate' ORDER BY date ASC";
	if ($weed != 'All' && $abundance == 'All')
		$query = "SELECT * FROM weed_scouts WHERE date >= '$startDate' AND date <= '$endDate' AND LCASE(weed)=LCASE('$weed') ORDER BY date ASC";
	if ($weed == 'All' && $abundance != 'All')
		$query = "SELECT * FROM weed_scouts WHERE date >= '$startDate' AND date <= '$endDate' AND LCASE(abundance)=LCASE('$abundance') ORDER BY date ASC";
	if ($weed != 'All' && $abundance != 'All')
		$query = "SELECT * FROM weed_scouts WHERE date >= '$startDate' AND date <= '$endDate' AND LCASE(abundance)=LCASE('$abundance') AND LCASE(weed)=LCASE('$weed') ORDER BY date ASC";
}

$result = mysql_query($query);
if (!$result) 
 	die('Invalid query: ' . mysql_error());	
				
// cycle through each weed sighting and make placemark
while ($sighting = mysql_fetch_assoc($result)) {
	
	array_push($array, 
			array(htmlspecialchars($sighting["date"]), htmlspecialchars($sighting["weed"]), htmlspecialchars($sighting["abundance"]), htmlspecialchars($sighting["longitude"]), htmlspecialchars($sighting["latitude"]), htmlspecialchars($sighting["note"]), htmlspecialchars($sighting["name"])));

}

outputCSV($array);	

?>