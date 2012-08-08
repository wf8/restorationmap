<?php 
session_start(); 

require('restorationmap_config.php');

function isLoggedIn()
{
    if($_SESSION['valid'])
        return true;
    return false;
}

//if the user has not logged in
if(!isLoggedIn())
{
    echo "not logged in";
    die();
} 
else
{
	// if we are logged in, return the user id
	echo $_SESSION['user_id'];
}
?>