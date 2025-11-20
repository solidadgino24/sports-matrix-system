<?php
include "../conn.php"; // adjust path if needed

if(!isset($_GET['ass_id'])) {
    echo '<option value="">Invalid association</option>';
    exit;
}

$ass_id = intval($_GET['ass_id']); // sanitize input

$sql = $con->query("SELECT prog_id, prog_name FROM tbl_programs WHERE ass_id='$ass_id' ORDER BY prog_name ASC");

if(!$sql){
    echo '<option value="">Query error: '.$con->error.'</option>';
    exit;
}

if(mysqli_num_rows($sql) > 0){
    echo '<option selected disabled>--SELECT PROGRAM--</option>';
    while($row = mysqli_fetch_assoc($sql)){
        echo '<option value="'.$row['prog_id'].'">'.htmlspecialchars($row['prog_name']).'</option>';
    }
} else {
    echo '<option value="">No programs available</option>';
}
?>
