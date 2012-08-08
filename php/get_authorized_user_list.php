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

$table = mysql_real_escape_string($_GET['table']);
$shape_id = mysql_real_escape_string($_GET['shape_id']);

// get user id out of session
$user_id = $_SESSION['user_id'];

// check if shape is private or public
$sql = "SELECT * FROM authorized_users WHERE layer_id='$shape_id' AND layer_type='$table'";
$result = mysql_query($sql, $connection);
if (mysql_num_rows($result) == 0) {
	// no authorized_users for this shape, so it is public
	echo 'public:' . $_SESSION['last_name'] . ', ' . $_SESSION['first_name'];
} else {
	while ($row = @mysql_fetch_assoc($result)) {
		// skip if it is the current user
		if ($row['user_id'] != $user_id) {
			$this_id = $row['user_id'];
			// not the current user, so we need to look up user name
			$sql = "SELECT * FROM users WHERE id='$this_id'";
			$result2 = mysql_query($sql, $connection);
			if ($result2) {
				$row2 = mysql_fetch_assoc($result2);
				$list = $list . ':' . $row2['last_name'] . ', ' . $row2['first_name'];
			}
		}	
	}
	echo 'private:' . $_SESSION['last_name'] . ', ' . $_SESSION['first_name'] . $list;
}
?>