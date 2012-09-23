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


echo "<select id='siteList'><option>Select Site</option>";


$query_sites = "SELECT * FROM stewardship_site ORDER BY name";
$result_sites = mysql_query($query_sites);

// Iterates through each site, gets name of site from database
while ($row = @mysql_fetch_assoc($result_sites)) 
{
	$siteId = $row['id'];
	$siteName = $row['name'];
	echo '<option value="' . $siteId . '">' . $siteName . '</option>';
}
echo "</select>";
				
mysql_close($connection);
?>