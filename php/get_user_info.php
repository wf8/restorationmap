<?php
session_start();
require('restorationmap_config.php');

//if the user is not logged in
if(!$_SESSION['valid'])
{
    echo "not logged in";
    die();
} 

// display email and admin info
echo 'Hi ' . $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] . ',<br>';
echo 'Your email is registered as ' . $_SESSION['email'] . '<br>';

if ( $_SESSION['admin'] )
	echo 'You have admin permissions.<br>';
else
	echo 'You do not have admin permissions.<br>';
	
// get user id out of session
$user_id = $_SESSION['user_id'];

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
  die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
  die ('Can\'t use db : ' . mysql_error());
	
 // Selects all the sites for which this user is steward
$query1 = "SELECT * FROM site_steward WHERE user_id='$user_id'";
$result1 = mysql_query($query1);
if (mysql_num_rows($result1) > 0) {
	// user is steward 
	echo '<br>You are registered as site steward of:<br>';	
	// iterate through each site, displays name of site from database
	while ($row = mysql_fetch_assoc($result1)) {
		$thisSiteId = $row['stewardshipsite_id'];
		$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
		$result2 = mysql_query($query2);
		if (!$result2) 
			die('Invalid query: ' . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		echo '&nbsp;&nbsp;&nbsp;' . $row2['name'] . '<br>';
	}
}

 // Selects all the sites for which this user is assistant
$query1 = "SELECT * FROM site_assistant WHERE user_id='$user_id'";
$result1 = mysql_query($query1);
if (mysql_num_rows($result1) > 0) {
	// user is assistant 	
	echo '<br>You are registered as site assistant of:<br>';
	// iterate through each site, displays name of site from database
	while ($row = mysql_fetch_assoc($result1)) {
		$thisSiteId = $row['stewardshipsite_id'];
		$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
		$result2 = mysql_query($query2);
		if (!$result2) 
			die('Invalid query: ' . mysql_error());
		$row2 = mysql_fetch_assoc($result2);
		echo '&nbsp;&nbsp;&nbsp;' . $row2['name'] . '<br>';
	}
}


mysql_close($connection);
?>