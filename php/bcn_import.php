<?php
/* 

Simple script to update the BCN database for a single year's worth of data by parsing an uploaded 
csv file. The uploaded file should be the BCN csv file edited to include only the year(s) that 
need updating; otherwise the file will be to large to upload and will require too many database hits.



Column names from the BCN .cvs will be converted to table columns:
OBS_ID = bcn_obs_id VARCHAR(15), NOT NULL AUTO_INCREMENT, PRIMARY KEY(bcn_obs_id),  
PRIMARY_COM_NAME = common_name VARCHAR(50), 
HOW_MANY_ATLEAST = how_many_at_least INT,
HOW_MANY_MOST = how_many_most INT, 
TIME = time VARCHAR(10),
PROTOCOL_ID = protocol_id VARCHAR(10), 
USER_ID = user_id VARCHAR(10),
LAST_NAME =last_name VARCHAR(30),
FIRST_NAME = first_name VARCHAR(30), 
LOC_ID =location_id VARCHAR(10),
NAME =location_name VARCHAR(30),
LATITUDE =latitude VARCHAR(15),
LONGITUDE = longitude VARCHAR(15),
COUNTY =county VARCHAR(10),
SUBNATIONAL1_CODE = subnational1_code VARCHAR(10),
MonthDay = month_day INT,
YEARS =year INT


// create observations table
$sql = "CREATE TABLE observations(bcn_obs_id VARCHAR(15) NOT NULL, PRIMARY KEY(bcn_obs_id), common_name VARCHAR(50), how_many_at_least INT,how_many_most INT, time VARCHAR(10),protocol_id VARCHAR(10), user_id VARCHAR(10), last_name VARCHAR(30), first_name VARCHAR(30), location_id VARCHAR(10), month_day INT, year INT)";
mysql_query($sql); 
echo "observations table created!";

// create locations table
$sql = "CREATE TABLE locations(location_id VARCHAR(10) NOT NULL, PRIMARY KEY(location_id), location_name VARCHAR(30), latitude VARCHAR(15), longitude VARCHAR(15), county VARCHAR(10), subnational1_code VARCHAR(10))";
mysql_query($sql); 
echo "locations table created!";

*/

session_start(); 
require('restorationmap_config.php');

//if the user is not logged in
if( !$_SESSION['valid'] )
{
    echo "not logged in";
    die();
} 

// is user registered as admin
if ( !$_SESSION['admin'] )
{
	echo 'you are not authorized';
	die();
}


// connect to mysql server
$connection = mysql_connect ($db_server, $bcn_db_username, $bcn_db_password, true);
if (!$connection) 
	die('Not connected : ' . mysql_error());

// set the active database
$db_selected = mysql_select_db($bcn_db_database, $connection);
if (!$db_selected) 
	die ('Can\'t use db : ' . mysql_error());  
	
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Restoration Map: Upload BCN Data</title>
	</head>
	<body>
	<font face="verdana, helvetica" size="1">
	Back to <a href="admin.php">Admin</a><br>
	<br>
	<br>
	<font face="arial, helvetica" size="8">
	Restoration Map: Upload BCN Data
	</font>
	<font face="verdana, helvetica" size="1">
	<br><br><br>
<?php


// check file size and type
$allowed_exts = array("csv", "tsv", "txt");
$extension = end(explode(".", trim($_FILES["file"]["name"])));
//if ($_FILES["file"]["size"] > 10000000)
//	die("File too big! File size: " . ($_FILES["file"]["size"] / 1000000) . " MB<br></body></html>");

if (!in_array($extension, $allowed_exts))
	die("File type '$extension' not allowed. Only .csv, .tsv, or .txt files can be uploaded.</body></html>");

echo "Uploaded file: " . $_FILES["file"]["name"] . "<br>";
echo "File type: " . $_FILES["file"]["type"] . "<br>";
echo "File size: " . ($_FILES["file"]["size"] / 1000000) . " MB<br>";
echo "Stored as temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

// get year	
$year_to_input = mysql_real_escape_string($_POST['year']);
echo "Uploading data for year: " . $year_to_input . "<br>";

// check to see if observations for this year already exist
$year_query = "SELECT * FROM observations WHERE year='$year_to_input'";
$year_result = mysql_query($year_query, $connection);
if (!$year_result) 
	die('Invalid query: ' . mysql_error());
if (mysql_num_rows($year_result) > 0) 
	die("<b>No data uploaded.</b> Observations have already been entered for year: $year_to_input</body></html>");
	
// open csv file
if (($handle = fopen($_FILES["file"]["tmp_name"], "r")) !== FALSE) {

	// get a list of all locations
	$location_query = "SELECT location_id FROM locations WHERE 1";
	$location_results = mysql_query($location_query,$connection);
	if (!$location_results) 
    	die('Invalid query: ' . mysql_error());
	echo 'Processing...';
	$observations_count = 0;
    $locations_count = 0;    
    $i = 0;
    // loop through each row in the csv file
    while (($data = fgetcsv($handle, 0, "\t", '"')) !== FALSE) {
    	$i++;    	
    	if ($i == 10000) {
    		// echo .... to show processing over time
    		echo '.';
    		$i = 0;
    	}

		// dont insert header row and only insert point count data for the correct year
		if ( ($data[0] != 'SUB_ID') && ( strpos($data[13], 'eBird - Stationary Count') !== FALSE )
				&& ( trim($data[11]) == trim($year_to_input) ) ) {
        	// get all necessary data from row
        	$bcn_obs_id = mysql_real_escape_string($data[1]);
        	$common_name = mysql_real_escape_string($data[4]);
        	$how_many_at_least = mysql_real_escape_string($data[5]);
        	$how_many_most = mysql_real_escape_string($data[6]);
        	$time = mysql_real_escape_string($data[9]);
        	$protocol_id = mysql_real_escape_string($data[12]);
        	$user_id = mysql_real_escape_string($data[14]);
        	$last_name = mysql_real_escape_string($data[15]);
        	$first_name = mysql_real_escape_string($data[16]);
        	$location_id = mysql_real_escape_string($data[24]);
        	$location_name = mysql_real_escape_string($data[26]);
        	$latitude = mysql_real_escape_string($data[27]);
        	$longitude = mysql_real_escape_string($data[28]);
        	$county = mysql_real_escape_string($data[29]);
        	$subnational1_code = mysql_real_escape_string($data[30]);
        	$month_day = mysql_real_escape_string($data[10]);
        	$year = mysql_real_escape_string($data[11]);
        	
        	$sql="INSERT INTO observations (bcn_obs_id, common_name, how_many_at_least, how_many_most, time, protocol_id, user_id, last_name, first_name, location_id, month_day, year) VALUES ('$bcn_obs_id', '$common_name', '$how_many_at_least', '$how_many_most', '$time', '$protocol_id', '$user_id', '$last_name', '$first_name', '$location_id', '$month_day', '$year')";
        	$observation_result = mysql_query($sql, $connection);   
        	if (!$observation_result) 
		    	die('Invalid query: ' . mysql_error());    	
        	$observations_count++;     	
			// check to see if this is a new or existing location
			// reset row pointer in the location results
			mysql_data_seek($location_results, 0);
			$location_exists == false;
			while ($location = mysql_fetch_assoc($location_results)) {
				if ($location['location_id'] == $location_id) {
					$location_exists = true;
					break;
				}
			}
		
			// insert new location if necessary
			if (!$location_exists) {
				$sql="INSERT INTO locations (location_id, location_name, latitude, longitude, county, subnational1_code) VALUES ('$location_id', '$location_name', '$latitude', '$longitude', '$county', '$subnational1_code')";
				mysql_query($sql, $connection);
				$locations_count++;
				// get a new list of all locations
				$location_results = mysql_query($location_query,$connection);
				if (!$location_results) 
    				die('Invalid query: ' . mysql_error());
			}
        }
    }
    fclose($handle);
    echo "<br><br>Successfully added total observations: ".$observations_count."<br>";
    echo "Total locations added: ".$locations_count."<br>";
    echo "<b>Finished adding data for the year: ".$year_to_input."</b><br></body></html>";
}	

?> 
