<?php
include "../conn.php";
session_start();

$ass_id = $_SESSION['ass_id'] ?? null;
$ev_id = $_SESSION['ev_id'] ?? null;

$response = ["pending_applications" => 0, "pending_players" => 0];

if ($ass_id) {
    // Pending tournament applications
    $count_sql = $con->query("
        SELECT COUNT(*) AS total
        FROM tbl_tourna_application AS ta
        LEFT JOIN tbl_profile AS p ON ta.prof_id = p.prof_id
        LEFT JOIN tbl_association_players AS ap ON p.user_id = ap.user_id
        WHERE ta.status = '0'
        AND (ta.ev_id = '$ev_id' OR ta.ev_id IS NULL)
        AND ap.ass_id = '$ass_id'
    ");
    if ($count_sql && $row = $count_sql->fetch_assoc()) {
        $response['pending_applications'] = (int)$row['total'];
    }

    // Pending player verifications
    $verify_sql = $con->query("
        SELECT COUNT(*) AS total
        FROM tbl_user AS u
        LEFT JOIN tbl_association_players AS ap ON u.user_id = ap.user_id
        WHERE u.status = '0' AND u.user_type = '3' AND ap.ass_id = '$ass_id'
    ");
    if ($verify_sql && $row = $verify_sql->fetch_assoc()) {
        $response['pending_players'] = (int)$row['total'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
