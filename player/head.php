<?php
ob_start();
session_start(); 
$session_username = $_SESSION['session_username'];
$session_user_id = $_SESSION['session_user_id'];
$session_user_type =  $_SESSION['session_type'];
if($session_user_type != 3){
    header("location:../login.php?e=Restricted Area");
}else{
    $user = $con->query("SELECT * FROM tbl_user WHERE username ='$session_username'");
    $user = mysqli_fetch_assoc($user);

    if(!isset($_GET['e_id'])){
        $sql = $con->query("SELECT * FROM tbl_event WHERE ev_status='1'");
    }else{
        $event_id = $_GET['e_id'];
        $sql = $con->query("SELECT * FROM tbl_event WHERE ev_id='$event_id'");
    }
    $event_details = mysqli_fetch_assoc($sql);
    $event_id =  $event_details['ev_id'];
    $_SESSION['ev_id'] = $event_details['ev_id'];

    $event_status = "Starting Soon";

    if($event_details['ev_remarks'] != 2){
        if(strtotime($event_details['start']) <= strtotime(date("Y-m-d")) && strtotime(date("Y-m-d")) <= strtotime($event_details['end'])){
            $con->query("UPDATE tbl_event SET ev_remarks ='1' WHERE ev_id='$event_id'");
            $event_status = "Event Ongoing";
        }else if(strtotime($event_details['end']) <= strtotime(date("Y-m-d"))){
            $con->query("UPDATE tbl_event SET ev_remarks ='2' WHERE ev_id='$event_id'");
            $event_status = "Event Concluded";
        }
    }else{
        $event_status = "Event Concluded";
    }
    
    if($user['status'] != 1){
        header("location:verify.php");
    }
}