<?php
session_start();

//if the user is not logged in
if(!$_SESSION['valid'])
{
    echo "not logged in";
    die();
} 

// display full name
echo $_SESSION['last_name'] . ', ' . $_SESSION['first_name'];
?>