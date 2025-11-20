<?php
date_default_timezone_set("Asia/Manila");
function genRanNum($length) {
    $result = '';
    for($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9); 
    }
    return $result;
}

$con = mysqli_connect("localhost","root","","db_sports");
if (mysqli_connect_errno()){
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die();
}
