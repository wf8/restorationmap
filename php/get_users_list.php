<?php
session_start();

require('restorationmap_config.php');

//if the user is not logged in
if(!$_SESSION['valid']) {
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

 // Selects all the sites for which this user is steward
$query1 = "SELECT first_name, last_name FROM users ORDER BY last_name";
$result1 = mysql_query($query1);
if (!$result1) 
	die('Invalid query: ' . mysql_error());

echo "<select id='userList'><option>Select User</option>";
// Iterates through each user
if (mysql_num_rows($result1) >= 1) {
	while ($row = @mysql_fetch_assoc($result1)) {
		$last_name = $row['last_name'];
		$first_name = $row['first_name'];
		$full_name = $last_name . ', ' . $first_name;
		if ($last_name !== 'guest')
			echo '<option value="' . $full_name . '">' . $full_name . '</option>';
	}
}

echo "</select>";
mysql_close($connection);
?>