<?php
include "../conn.php";
session_start();
header('Content-Type: application/json');

if (!isset($_POST['prog_id'])) {
    echo json_encode(['success'=>false,'message'=>'Missing program ID']);
    exit;
}

$prog_id = intval($_POST['prog_id']);
$stmt = $con->prepare("DELETE FROM tbl_programs WHERE prog_id=?");
$stmt->bind_param("i", $prog_id);

if ($stmt && $stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Program deleted successfully']);
} else {
    echo json_encode(['success'=>false,'message'=>'Database error: '.$con->error]);
}
