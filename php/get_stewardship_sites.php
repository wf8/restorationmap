<?php
session_start();

require('restorationmap_config.php');

//if the user is not logged in
if(!$_SESSION['valid'])
{
    echo "not logged in";
    die();
} 

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
 
$user_id = $_SESSION['user_id'];

 // Selects all the sites for which this user is steward
$query1 = "SELECT * FROM site_steward WHERE user_id='$user_id'";
$result1 = mysql_query($query1);
if (!$result1) 
	die('Invalid query: ' . mysql_error());

 // Selects all the sites for which this user is assistant
$query10 = "SELECT * FROM site_assistant WHERE user_id='$user_id'";
$result10 = mysql_query($query10);
if (!$result10) 
	die('Invalid query: ' . mysql_error());


// check if user is steward or assistant of only one site

if ((mysql_num_rows($result1) == 1) && (mysql_num_rows($result10) == 0)) {
	$row = @mysql_fetch_assoc($result1);
	$thisSiteId = $row['stewardshipsite_id'];
	$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
	$result2 = mysql_query($query2);
	if (!$result2) 
		die('Invalid query: ' . mysql_error());
	$row2 = @mysql_fetch_assoc($result2);
	echo "<select id='siteList'>";
	echo '<option value="' . $thisSiteId . '">' . $row2['name'] . '</option>';
	echo "</select>";
} else if ((mysql_num_rows($result1) == 0) && (mysql_num_rows($result10) == 1)) {
	$row = @mysql_fetch_assoc($result10);
	$thisSiteId = $row['stewardshipsite_id'];
	$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
	$result2 = mysql_query($query2);
	if (!$result2) 
		die('Invalid query: ' . mysql_error());
	$row2 = @mysql_fetch_assoc($result2);
	echo "<select id='siteList'>";
	echo '<option value="' . $thisSiteId . '">' . $row2['name'] . '</option>';
	echo "</select>";
	
}
// user is steward of numerous sites
else
{	
	echo "<select id='siteList'><option>Select Site</option>";
	$site_list = array();
	// Iterates through each site, gets name of site from database
	if (mysql_num_rows($result1) >= 1) {
		while ($row = @mysql_fetch_assoc($result1)) 
		{
			$thisSiteId = $row['stewardshipsite_id'];
			$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
			$result2 = mysql_query($query2);
			if (!$result2) 
				die('Invalid query: ' . mysql_error());
			$row2 = @mysql_fetch_assoc($result2);
			$site_list[$row2['name']] = '<option value="' . $thisSiteId . '">' . $row2['name'] . '</option>';
		}
	}
	if (mysql_num_rows($result10) >= 1) {
		while ($row = @mysql_fetch_assoc($result10)) 
		{
			$thisSiteId = $row['stewardshipsite_id'];
			$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
			$result2 = mysql_query($query2);
			if (!$result2) 
				die('Invalid query: ' . mysql_error());
			$row2 = @mysql_fetch_assoc($result2);
			$site_list[$row2['name']] = '<option value="' . $thisSiteId . '">' . $row2['name'] . '</option>';
		}
	}
	ksort($site_list);
	foreach ($site_list as $key => $val) {
		echo $val;
	}

	
	echo "</select>";
}
mysql_close($connection);
?>