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

// $File = "troubleshooting.txt"; 
// $Handle = fopen($File, 'w');
// fwrite($Handle, $_POST[date]); 
// fwrite($Handle, "\n");

$table = mysql_real_escape_string($_POST[table]);
$site = mysql_real_escape_string($_POST[site]);
$shape_id = mysql_real_escape_string($_POST[id]);
$authorized_users = mysql_real_escape_string($_POST[authorized_users]);

// get user id out of session
$user_id = $_SESSION['user_id'];

// check if we are inserting a trail
if ($table == 'trails') {
	$name = mysql_real_escape_string($_POST[name]);
	$coordinates = ltrim(mysql_real_escape_string($_POST[coordinates]));
	
	// if shape_id is empty, we are inserting a new shape, otherwise we are updating
	if ($shape_id == '')
		$sql="INSERT INTO $table (stewardshipsite_id, name, coordinates, user_id) VALUES ('$site', '$name', '$coordinates', '$user')";
	else
		$sql="UPDATE $table SET stewardshipsite_id = '$site', name = '$name', coordinates = '$coordinates', user_id = '$user_id' WHERE id = '$shape_id'";
	if (!mysql_query($sql,$connection)) 
	  	die('Error: ' . mysql_error());
	  	
// check if we are inserting a landmark
} else if ($table == 'landmark') {
	$name = mysql_real_escape_string($_POST[name]);
	$description = mysql_real_escape_string($_POST[description]);
	$coordinates = ltrim(mysql_real_escape_string($_POST[coordinates]));
	$color = mysql_real_escape_string($_POST[color]);
	
	// if shape_id is empty, we are inserting a new landmark, otherwise we are updating
	if ($shape_id == '')
		$sql="INSERT INTO $table (stewardshipsite_id, name, description, coordinates, color, user_id) VALUES ('$site', '$name', '$description', '$coordinates', '$color', '$user')";
	else
		$sql="UPDATE $table SET stewardshipsite_id = '$site', name = '$name', color = '$color', description = '$description',coordinates = '$coordinates', user_id = '$user_id' WHERE id = '$shape_id'";
	if (!mysql_query($sql,$connection)) 
	  	die('Error: ' . mysql_error());	  	
	  	
// check if we are updating a site border
} else if ($table == 'border') {
	$coordinates = ltrim(mysql_real_escape_string($_POST[coordinates]));
	$sql="UPDATE $table SET coordinates = '$coordinates', user_id = '$user_id' WHERE id = '$shape_id'";
		if (!mysql_query($sql,$connection)) 
	  		die('Error: ' . mysql_error());
	  		
// else we need to insert a shape	
} else {
	$date = mysql_real_escape_string($_POST[date]);
	$title = mysql_real_escape_string($_POST[title]);
	$description = mysql_real_escape_string($_POST[description]);
	$coordinates = ltrim(mysql_real_escape_string($_POST[coordinates]));
	
	// allow 0 for month and day
	$sql="SET SESSION sql_mode='ALLOW_INVALID_DATES'";
	if (!mysql_query($sql,$connection)) 
		die('Error: ' . mysql_error());
		
	// check if inserting into 'other' table
	if ($table == 'other') {
		$color = mysql_real_escape_string($_POST[color]);
		// insert data with color
		// if shape_id is empty, we are inserting a new shape, otherwise we are updating
		if ($shape_id == '')
			$sql="INSERT INTO $table (stewardshipsite_id, date, title, description, coordinates, user_id, color) VALUES ('$site', '$date', '$title', '$description', '$coordinates', '$user_id', '$color')";
		else
			$sql="UPDATE $table SET stewardshipsite_id = '$site', date = '$date', title = '$title', description = '$description', coordinates = '$coordinates', user_id = '$user_id', color = '$color' WHERE id = '$shape_id'";
		if (!mysql_query($sql,$connection)) 
	  		die('Error: ' . mysql_error());
	} else {
		// insert data without color
		// if shape_id is empty, we are inserting a new shape, otherwise we are updating
		if ($shape_id == '')
			$sql="INSERT INTO $table (stewardshipsite_id, date, title, description, coordinates, user_id) VALUES ('$site', '$date', '$title', '$description', '$coordinates', '$user_id')";
		else
			$sql="UPDATE $table SET stewardshipsite_id = '$site', date = '$date', title = '$title', description = '$description', coordinates = '$coordinates', user_id = '$user_id' WHERE id = '$shape_id'";
		if (!mysql_query($sql,$connection)) 
	  		die('Error: ' . mysql_error());
	}
}
// now we need to check if this is a private or public layer
// borders and trails cannot be private
if ($authorized_users !== '' && $table !== 'border' && $table !== 'trails') {
	// ok, its a private shape
	// first check if the shape is a new one
	if ($shape_id == '') {
		// get the new shape's id from database
		$layer_id = mysql_insert_id($connection);		
	} else {
		// now handle the shape if it was not a new one	
		$layer_id = $shape_id;
		// now delete all existing authorized_users rows for this layer_id and $table
		$sql = "DELETE FROM authorized_users WHERE layer_id='$layer_id' AND layer_type='$table'";
		mysql_query($sql, $connection);
	}
	// now insert new authorized_users rows for this layer_id and $table and appropriate user_ids
	// if this is a private shape, the list of authorized users will be a string in
	// the format "last_name1, first_name1:last_name2, first_name2:last_name3, first_name3" 
	$names = explode(":", $authorized_users);
	for ($i = 0; $i < sizeof($names); ++$i) {
		$end_of_last_name = strpos($names[$i], ', ');
		$last_name = substr($names[$i], 0, $end_of_last_name);
		$first_name = substr($names[$i], $end_of_last_name + 2);
		// get the user id
		$sql = "SELECT id FROM users WHERE last_name='$last_name' AND first_name='$first_name'";
		$result = mysql_query($sql, $connection);
		if ($result) {
			$row = mysql_fetch_assoc($result);
			$this_user_id = $row['id'];
			// insert the authorized_user
			$sql = "INSERT INTO authorized_users (user_id, layer_id, layer_type) VALUES ('$this_user_id', '$layer_id', '$table')";
			mysql_query($sql, $connection);
		}
	}
} else {
	// it is a public shape, so clean up any authorized users
	if ($shape_id == '') 
		// get the shape id if it was a new shape
		$layer_id = mysql_insert_id($connection);		
	else 
		// get the shape id if the shape already existed and was being updated
		$layer_id = $shape_id;
	// now delete all existing authorized_users rows for this layer_id and $table
	$sql = "DELETE FROM authorized_users WHERE layer_id='$layer_id' AND layer_type='$table'";
	mysql_query($sql, $connection);
}
mysql_close($connection);
?>