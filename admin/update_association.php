<?php
include "../conn.php";
session_start();

$id = intval($_POST['id']);
$name = $_POST['name'];
$desc = $_POST['desc'];

// Check if logo file is uploaded
$logoBlob = null;
if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0){
    $logoBlob = file_get_contents($_FILES['logo']['tmp_name']);
}

// Build query
if($logoBlob){
    $stmt = $con->prepare("UPDATE tbl_association SET name=?, ass_desc=?, img_logo=? WHERE ass_id=?");
    $stmt->bind_param("ssbi", $name, $desc, $null, $id);
    $stmt->send_long_data(2, $logoBlob);
} else {
    $stmt = $con->prepare("UPDATE tbl_association SET name=?, ass_desc=? WHERE ass_id=?");
    $stmt->bind_param("ssi", $name, $desc, $id);
}

$stmt->execute();
echo "College updated successfully!";
