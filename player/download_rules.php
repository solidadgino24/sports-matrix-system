<?php
include("../conn.php");

$id = $_GET['id'] ?? 0;

// Fetch the rules file from the database based on the tournament
$sql = $con->query("
    SELECT s.rules 
    FROM tbl_tournament AS t
    LEFT JOIN tbl_game_modes AS gm ON t.game_id = gm.game_id
    LEFT JOIN tbl_sports AS s ON gm.sport_id = s.sport_id
    WHERE t.tourna_id = '$id'
");
$row = mysqli_fetch_assoc($sql);

if (!$row || empty($row['rules'])) {
    die("Rules file not found in database.");
}

$file = "../" . ltrim($row['rules'], "/"); // ensure correct relative path

// ✅ Debug safety check
if (!file_exists($file)) {
    die("Rules file not found on server: " . htmlspecialchars($file));
}

// ✅ Determine file type for headers
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$mimeTypes = [
    'pdf' => 'application/pdf',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png'
];
$contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

// ✅ Send file download headers
header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
?>
