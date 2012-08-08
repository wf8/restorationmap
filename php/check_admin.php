<?php
session_start(); 

if ( $_SESSION['admin'] )
        echo 'true';
else
    echo 'false';
?>