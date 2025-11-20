<?php
include "../conn.php";

if (isset($_POST['sport_id'])) {
    $id = intval($_POST['sport_id']);

    $sql = $con->prepare("DELETE FROM tbl_sports WHERE sport_id = ?");
    $sql->bind_param("i", $id);

    if ($sql->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete sport.']);
    }

    $sql->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
