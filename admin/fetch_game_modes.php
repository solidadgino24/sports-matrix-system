<?php
include_once("../conn.php");

$sport_id = $_POST['sport_id'];

$q = $con->query("
    SELECT game_id, name 
    FROM tbl_game_modes 
    WHERE sport_id = '$sport_id'
");

if ($q->num_rows == 0) {
    echo "<option disabled selected>No game modes available</option>";
    exit;
}

while ($r = $q->fetch_assoc()) {
    echo "<option value='{$r['game_id']}'>{$r['name']}</option>";
}
?>
