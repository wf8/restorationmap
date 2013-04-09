<?php
session_start();

require('restorationmap_config.php');

set_time_limit(0);

//if the user is not logged in
if(!$_SESSION['valid']) {
    echo "not logged in";
    die();
} 

function lon2x($lon) { return deg2rad($lon * 6378137.0); }
function lat2y($lat) { return log(tan(M_PI_4 + deg2rad($lat) / 2.0)) * 6378137.0; }
function x2lon($x) { return rad2deg($x / 6378137.0); }
function y2lat($y) { return rad2deg(2.0 * atan(exp($y / 6378137.0)) - M_PI_2); }

function calculate_acreage($these_coordinates) {
	// first project longitude and latitude as x and y, then calculate area according to this formula:
	// A = (1/2)(x1*y2 - x2*y1 + x2*y3 - x3*y2 + x3y4 - x4y3 + x4*y5 - x5*y4 + x5*y1 - x1*y5)
	$points = explode(' ', trim($these_coordinates));
	$number_of_points = count($points);
	if ($number_of_points < 3)
		return 0;
	if (trim($points[0]) != trim(end($points)))
		return 0;
	if (trim($points[0]) == trim(end($points)) && $number_of_points == 3)
		return 0;
	$counter = 0;
	$sum = 0;
	while ($counter < $number_of_points - 1) {		
		$thisCoordinate = explode(',', $points[$counter]);
		$thisX = lon2x($thisCoordinate[0]); 
		$thisY = lat2y($thisCoordinate[1]); 
		if ($counter > 0) {
			$sum += ($oldX * $thisY) - ($thisX * $oldY);
		}
		$oldX = $thisX;
		$oldY = $thisY;
		$counter++;
	}
	// convert square meters to acres, round and return
	return abs(round($sum * 0.000247105381 / 4 * 1.118614, 2));
}

function outputCSV($data) {
    $outstream = fopen("php://output", "w");
    function __outputCSV(&$vals, $key, $filehandler) {
        fputcsv($filehandler, $vals); // add parameters if you want
    }
    array_walk($data, "__outputCSV", $outstream);
    fclose($outstream);
}

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
	die('Not connected : ' . mysql_error());	


// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
	die ('Can\'t use db : ' . mysql_error());


$site_id = $_GET['site_id'];

$array = array(
		array("Stewardship Site", "County", "Type", "Date", "Name", "Description", "Acreage"));


$query = "SELECT * FROM brush WHERE stewardshipsite_id = '$site_id' ORDER BY date";
$results = mysql_query($query);
if ($results)  
{	
	while ($row = @mysql_fetch_assoc($results)) 
	{
		$thisSiteId = $row['stewardshipsite_id'];
		$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
		$result2 = mysql_query($query2);
		if (!$result2) 
			die('Invalid query: ' . mysql_error());
		$row2 = @mysql_fetch_assoc($result2);
		array_push($array, 
			array($row2['name'], $row2['county'], "Bush and tree removal", $row['date'], $row['title'], $row['description'], calculate_acreage($row['coordinates'])));
	}
}

$query = "SELECT * FROM landmark WHERE stewardshipsite_id = '$site_id' ORDER BY date";
$results = mysql_query($query);
if ($results)  
{	
	while ($row = @mysql_fetch_assoc($results)) 
	{
		$thisSiteId = $row['stewardshipsite_id'];
		$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
		$result2 = mysql_query($query2);
		if (!$result2) 
			die('Invalid query: ' . mysql_error());
		$row2 = @mysql_fetch_assoc($result2);
		array_push($array, 
			array($row2['name'], $row2['county'], "Geographic feature / Landmark", "N/A", $row['name'], $row['description'], calculate_acreage($row['coordinates'])));
	}
}

$query = "SELECT * FROM other WHERE stewardshipsite_id = '$site_id' ORDER BY date";
$results = mysql_query($query);
if ($results)  
{	
	while ($row = @mysql_fetch_assoc($results)) 
	{
		$thisSiteId = $row['stewardshipsite_id'];
		$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
		$result2 = mysql_query($query2);
		if (!$result2) 
			die('Invalid query: ' . mysql_error());
		$row2 = @mysql_fetch_assoc($result2);
		array_push($array, 
			array($row2['name'], $row2['county'], "Planning and other", $row['date'], str_replace('"','',$row['title']), str_replace('"','',$row['description']), calculate_acreage($row['coordinates'])));
	}
}


$query = "SELECT * FROM burns WHERE stewardshipsite_id = '$site_id' ORDER BY date";
$results = mysql_query($query);
if ($results)  
{	
	while ($row = @mysql_fetch_assoc($results)) 
	{
		$thisSiteId = $row['stewardshipsite_id'];
		$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
		$result2 = mysql_query($query2);
		if (!$result2) 
			die('Invalid query: ' . mysql_error());
		$row2 = @mysql_fetch_assoc($result2);
		array_push($array, 
			array($row2['name'], $row2['county'], "Prescribed burn", $row['date'], str_replace('"','',$row['title']), str_replace('"','',$row['description']), calculate_acreage($row['coordinates'])));
	}
}


$query = "SELECT * FROM seed WHERE stewardshipsite_id = '$site_id' ORDER BY date";
$results = mysql_query($query);
if ($results)  
{	
	while ($row = @mysql_fetch_assoc($results)) 
	{
		$thisSiteId = $row['stewardshipsite_id'];
		$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
		$result2 = mysql_query($query2);
		if (!$result2) 
			die('Invalid query: ' . mysql_error());
		$row2 = @mysql_fetch_assoc($result2);
		array_push($array, 
			array($row2['name'], $row2['county'], "Seed collection and planting", $row['date'], str_replace('"','',$row['title']), str_replace('"','',$row['description']), calculate_acreage($row['coordinates'])));
	}
}


$query = "SELECT * FROM weed WHERE stewardshipsite_id = '$site_id' ORDER BY date";
$results = mysql_query($query);
if ($results)  
{	
	while ($row = @mysql_fetch_assoc($results)) 
	{
		$thisSiteId = $row['stewardshipsite_id'];
		$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
		$result2 = mysql_query($query2);
		if (!$result2) 
			die('Invalid query: ' . mysql_error());
		$row2 = @mysql_fetch_assoc($result2);
		array_push($array, 
			array($row2['name'], $row2['county'], "Weed control", $row['date'], str_replace('"','',$row['title']), str_replace('"','',$row['description']), calculate_acreage($row['coordinates'])));
	}
}

$query = "SELECT * FROM trails WHERE stewardshipsite_id = '$site_id'";
$results = mysql_query($query);
if ($results)  
{	
	while ($row = @mysql_fetch_assoc($results)) 
	{
		$thisSiteId = $row['stewardshipsite_id'];
		$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
		$result2 = mysql_query($query2);
		if (!$result2) 
			die('Invalid query: ' . mysql_error());
		$row2 = @mysql_fetch_assoc($result2);
		array_push($array, 
			array($row2['name'], $row2['county'], "Trails", "N/A", str_replace('"','',$row['name']), "N/A", "N/A"));
	}
}

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=site_report.csv");
header("Pragma: no-cache");
header("Expires: 0");

outputCSV($array);					
					
?>
