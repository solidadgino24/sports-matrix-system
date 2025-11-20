<?php
include "../conn.php";
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

$prog_id = intval($_POST['prog_id'] ?? 0);
$prog_name = trim($_POST['prog_name'] ?? '');

if ($prog_id <= 0 || empty($prog_name)) {
    echo json_encode(['success'=>false,'message'=>'Missing program ID or name']);
    exit;
}

// Fetch existing program
$prog_sql = $con->prepare("SELECT img_logo, ass_id FROM tbl_programs WHERE prog_id=?");
$prog_sql->bind_param("i",$prog_id);
$prog_sql->execute();
$result = $prog_sql->get_result();
$prog = $result->fetch_assoc();
if(!$prog){
    echo json_encode(['success'=>false,'message'=>'Program not found']);
    exit;
}

// Handle new logo if uploaded
$img_data = $prog['img_logo']; // default old image
if(!empty($_FILES['prog_logo']['tmp_name']) && is_uploaded_file($_FILES['prog_logo']['tmp_name'])){
    $img_content = file_get_contents($_FILES['prog_logo']['tmp_name']);
    if($img_content !== false){
        $img_data = base64_encode($img_content);
    }
}

// Update program
if($img_data){
    $stmt = $con->prepare("UPDATE tbl_programs SET prog_name=?, img_logo=? WHERE prog_id=?");
    $stmt->bind_param("ssi", $prog_name, $img_data, $prog_id);
} else {
    $stmt = $con->prepare("UPDATE tbl_programs SET prog_name=? WHERE prog_id=?");
    $stmt->bind_param("si", $prog_name, $prog_id);
}

if($stmt->execute()){
    echo json_encode(['success'=>true,'message'=>'Program updated successfully!']);
} else {
    echo json_encode(['success'=>false,'message'=>'Failed to update: '.$stmt->error]);
}
exit;
