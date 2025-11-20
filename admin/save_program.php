<?php
include "../conn.php";
header('Content-Type: application/json');

$ass_id = $_POST['ass_id'] ?? '';
$prog_name = trim($_POST['prog_name'] ?? '');
$prog_desc = trim($_POST['prog_desc'] ?? '');

if ($ass_id == '' || $prog_name == '') {
    echo json_encode(['success' => false, 'message' => 'Program name is required.']);
    exit;
}

$stmt = $con->prepare("INSERT INTO tbl_program (ass_id, prog_name, prog_desc) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $ass_id, $prog_name, $prog_desc);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Program added successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding program.']);
}
$stmt->close();
