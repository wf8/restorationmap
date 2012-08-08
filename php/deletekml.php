<?php
session_start();

require('restorationmap_config.php');

//if the user is not logged in
if (!$_SESSION['valid']) {
    echo "not logged in";
    die();
} 

 // Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) {
  die('Not connected : ' . mysql_error());
}

// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) {
  die ('Can\'t use db : ' . mysql_error());
}

// get the table we are deleting the shape from and the shape's id
$table = mysql_real_escape_string($_POST[table]);
$id = mysql_real_escape_string($_POST[id]);

// delete authorized_users (if any)
$sql = "DELETE FROM authorized_users WHERE layer_id='$id' AND layer_type='$table'";
mysql_query($sql,$connection);

// delete the shape itself
$sql="DELETE FROM $table WHERE id='$id'";
if (!mysql_query($sql,$connection)) {
  die('Error: ' . mysql_error());
}

mysql_close($connection);
?>