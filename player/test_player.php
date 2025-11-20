<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "../conn.php";

echo "<h2>Session Debug</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

$user_id = $_SESSION['session_user_id'] ?? null;
$user_type = $_SESSION['session_type'] ?? null;

if(!$user_id){
    echo "<p style='color:red'>❌ user_id missing in session</p>";
    exit;
}

$sql = $con->prepare("SELECT * FROM tbl_user WHERE user_id=?");
$sql->bind_param("i", $user_id);
$sql->execute();
$result = $sql->get_result();
$user = $result->fetch_assoc();

echo "<h3>DB Result:</h3>";
echo "<pre>";
print_r($user);
echo "</pre>";

echo "<p>✅ Script reached end successfully.</p>";
?>
