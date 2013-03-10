<?php
session_start(); 

require('restorationmap_config.php');

function isLoggedIn()
{
    if( $_SESSION['valid'] )
        return true;
    return false;
}
function logout()
{
    $_SESSION = array(); //destroy all of the session variables
    session_destroy();
}


if ( isLoggedIn() )
{
	logout();
	echo "success logout";
	exit;
} 
else 
{
	// Opens a connection to a MySQL server.
	$connection = mysql_connect ($db_server, $db_username, $db_password);
	if (!$connection) 
		die('Not connected : ' . mysql_error());
	
	// Sets the active MySQL database.
	$db_selected = mysql_select_db($db_database, $connection);
	if (!$db_selected) 
		die ('Can\'t use db : ' . mysql_error());

	$email = $_POST['email'];
	$login_password = $_POST['password'];
	$email = mysql_real_escape_string($email);
	
	$query = "SELECT * FROM users WHERE email = '$email';";
	$result = mysql_query($query);
	if(mysql_num_rows($result) < 1) //no such user exists
	{
	    echo "Login or password incorrect.";
	    die();
	}
	$userData = mysql_fetch_assoc($result);
	$hash = hash('sha256', $userData['salt'] . hash('sha256', $login_password) );
	if($hash != $userData['password']) //incorrect password
	{
	    echo "Login or password incorrect.";
	    die();
	}
	else
	{
		// regen session id as security measure
		session_regenerate_id (); 
		//set the session data for this user
    	$_SESSION['valid'] = 1;
    	$_SESSION['email'] = $email;
    	$_SESSION['user_id'] = $userData['id'];
    	$_SESSION['admin'] = $userData['admin'];
    	$_SESSION['first_name'] = $userData['first_name'];
    	$_SESSION['last_name'] = $userData['last_name'];
    	$_SESSION['opacity'] = $userData['opacity'];
	}
	echo "success login:" . $userData['id'];
	exit;
}
?>