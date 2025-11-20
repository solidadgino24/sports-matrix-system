<?php
    include "conn.php";

    ob_start();
    session_start(); 
    $session_username = $_SESSION['session_username'];
    $session_user_type =  $_SESSION['session_type'];

    if(session_destroy()){
        header("location:../login.php");
        die();
    }
?>