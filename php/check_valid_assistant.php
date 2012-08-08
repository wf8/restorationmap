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
 
// get user id out of session
$user_id = $_SESSION['user_id'];
$table = mysql_real_escape_string($_GET[table]);
$shape_id = mysql_real_escape_string($_GET[shape_id]);

 // check if the user is registered as steward of this site
$query = "SELECT user_id FROM $table WHERE id = $shape_id";
$result = mysql_query($query);
if (!$result || (mysql_num_rows($result) < 1) ) 
	die('not valid steward - user id-'.$user_id.' site id-'.$site_id);
else {
	$check = false;
	while ($row = @mysql_fetch_assoc($result)) 
	{
		$shape_user_id = $row['user_id'];
		if ($user_id == $shape_user_id) {
			$check = true;
		}
	}
	if ($check)
		echo 'success';
	else
		die('not valid steward - user id-'.$user_id.' site id-'.$site_id);
}
mysql_close($connection);
?>