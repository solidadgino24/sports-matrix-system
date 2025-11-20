<?php
include "../conn.php";
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_POST['add_program'])) {
    echo json_encode(['success'=>false,'message'=>'Invalid request']);
    exit;
}

$ass_id = intval($_POST['ass_id'] ?? 0);
$prog_name = trim($_POST['prog_name'] ?? '');

if ($ass_id <= 0 || empty($prog_name)) {
    echo json_encode(['success'=>false,'message'=>'Missing program name or association ID']);
    exit;
}

// Handle image
$img_data = null;
if (!empty($_FILES['prog_logo']['tmp_name']) && is_uploaded_file($_FILES['prog_logo']['tmp_name'])) {
    $img_content = file_get_contents($_FILES['prog_logo']['tmp_name']);
    if ($img_content !== false) {
        $img_data = base64_encode($img_content);
    }
}

// Insert program
if ($img_data) {
    $stmt = $con->prepare("INSERT INTO tbl_programs (ass_id, prog_name, img_logo, date_created) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        echo json_encode(['success'=>false,'message'=>'Prepare failed: '.$con->error]);
        exit;
    }
    $stmt->bind_param("iss", $ass_id, $prog_name, $img_data);
} else {
    $stmt = $con->prepare("INSERT INTO tbl_programs (ass_id, prog_name, date_created) VALUES (?, ?, NOW())");
    if (!$stmt) {
        echo json_encode(['success'=>false,'message'=>'Prepare failed: '.$con->error]);
        exit;
    }
    $stmt->bind_param("is", $ass_id, $prog_name);
}

if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Program added successfully!']);
} else {
    echo json_encode(['success'=>false,'message'=>'Execute failed: '.$stmt->error]);
}

exit; // always exit after JSON
