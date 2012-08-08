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
$oldPass = $_POST['old'];
$newPass1 = $_POST['new1'];
$newPass2 = $_POST['new2'];
		
// check that the 2 passwords are the same
if($newPass1 != $newPass2) {
    echo "Passwords are not the same.";
    exit;
}

// check that the new password is long enough
if( strlen($newPass1) < 5 ) {
    echo "The new password is not long enough.";
    exit;
}

// get user id out of session
$user_id = $_SESSION['user_id'];

// check that user is not "guest"
if ($user_id == "15")
	die('You cannot change the "guest" password.');

// Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
	die('Not connected : ' . mysql_error());

// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
	die ('Can\'t use db : ' . mysql_error());

// check that old password is correct
$query = "SELECT password, salt FROM users WHERE id = '$user_id'";
$result = mysql_query($query);
if(mysql_num_rows($result) < 1) //no such user exists
{
    echo "User does not exist.";
    die();
}
$userData = mysql_fetch_assoc($result);
$hash = hash('sha256', $userData['salt'] . hash('sha256', $oldPass) );
if($hash != $userData['password']) //incorrect password
{
	echo "The old password is incorrect.";
	die();
}

// all good so insert new passwords

// first get new salt and hash
$helperString = md5(uniqid(rand(), true));
$salt = substr($helperString, 0, 3);
$hash = hash('sha256', $newPass1);
$hash = hash('sha256', $salt . $hash);

$query = "UPDATE users SET password = '$hash', salt = '$salt' WHERE id = '$user_id'";
$result = mysql_query($query, $connection);
if (!$result) 
	die('Database Error: ' . mysql_error());

mysql_close($connection);
echo "success";
?>