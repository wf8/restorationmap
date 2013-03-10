<?php
session_start();

require('restorationmap_config.php');

//if the user is not logged in
if(!$_SESSION['valid'])
{
    echo "not logged in";
    die();
} 

//retrieve our data from POST
$newOpacity = $_POST['newOpacity'];

// save new opacity in user session
$_SESSION['opacity'] = $newOpacity;	

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

// update user's opacity 
$query = "UPDATE users SET opacity = '$newOpacity' WHERE id = '$user_id'";
$result = mysql_query($query, $connection);
if (!$result) 
	die('Database Error: ' . mysql_error());

mysql_close($connection);
echo "success";
?>