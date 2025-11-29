<?php 
header('Content-Type: application/json');
include "../conn.php";
session_start();

$a = $_GET['a'] ?? '';

// --------------------------------------------
// TEAM LIST FOR DROPDOWN
// --------------------------------------------
if ($a == "team_list") {
    $id = $_SESSION['tournament_code'];

    $sql = $con->query("
        SELECT t.team_id, a.name AS ass_name
        FROM tbl_team AS t
        LEFT JOIN tbl_association AS a ON t.ass_id = a.ass_id
        WHERE t.tourna_id='$id' AND t.disqualify='0' AND t.inMatch='0'
    ");

    $teams = [];
    while ($row = mysqli_fetch_assoc($sql)) {
        $teams[] = $row;
    }

    echo json_encode($teams);
    exit;
}

// --------------------------------------------
// CREATE MANUAL MATCH
// --------------------------------------------
if ($a == "create_manual") {

    $team1 = $_POST['team1'];
    $team2 = $_POST['team2'];
    $id = $_SESSION['tournament_code'];

    // check tournament status
    $sql = $con->query("SELECT status FROM tbl_tournament WHERE tourna_id='$id'");
    $tour = mysqli_fetch_assoc($sql);

    if ($tour['status'] == 2) {
        echo json_encode(['status'=>false,'message'=>"Tournament already ended. Cannot set matches."]);
        exit;
    }

    if ($team1 == $team2) {
        echo json_encode(['status'=>false, 'message'=>"Teams cannot be the same"]);
        exit;
    }

    // auto start tournament if not started
    if ($tour['status'] == 0) {
        $con->query("UPDATE tbl_tournament SET status='1' WHERE tourna_id='$id'");
    }

    // insert match
    $con->query("INSERT INTO tbl_matches (team1, team2, tourna_id, status) 
                 VALUES ('$team1', '$team2', '$id', '0')");

    // mark teams as in match
    $con->query("UPDATE tbl_team SET inMatch='1' WHERE team_id IN ('$team1', '$team2')");

    echo json_encode(['status'=>true,'message'=>"Match successfully created."]);
    exit;
}
// --------------------------------------------
// DELETE MATCH (ENDED ONLY)
// --------------------------------------------
if ($a == "delete_match") {
    $match_id = $_GET['match_id'] ?? 0;
    $id = $_SESSION['tournament_code'];

    // Check match exists and is ended
    $check = $con->query("SELECT * FROM tbl_matches WHERE match_id='$match_id' AND tourna_id='$id'");
    $match = mysqli_fetch_assoc($check);

    if (!$match) {
        echo json_encode(['status'=>false,'message'=>"Match not found."]);
        exit;
    }

    if ($match['status'] != 2) {
        echo json_encode(['status'=>false,'message'=>"You can only delete concluded (ended) matches."]);
        exit;
    }

    $team1 = $match['team1'];
    $team2 = $match['team2'];

    // Delete scores
    $con->query("DELETE FROM tbl_score_match WHERE match_id='$match_id'");

    // Delete match
    $con->query("DELETE FROM tbl_matches WHERE match_id='$match_id'");

    // Free teams
    $con->query("UPDATE tbl_team SET inMatch='0' WHERE team_id IN ('$team1', '$team2')");

    echo json_encode(['status'=>true,'message'=>"Match deleted successfully."]);
    exit;
}

// --------------------------------------------
// END TOURNAMENT
// --------------------------------------------
if ($a == "end_tournament") {
    $id = $_SESSION['tournament_code'];

    // Check if any match is ongoing OR scheduled
    $check = $con->query("SELECT * FROM tbl_matches WHERE tourna_id='$id' AND status != 2");

    if ($check->num_rows > 0) {
        echo json_encode([
            'status' => false,
            'message' => "Cannot end tournament. There are still ongoing or scheduled matches."
        ]);
        exit;
    }

    // Update tournament status to 2 = ended
    $con->query("UPDATE tbl_tournament SET status='2' WHERE tourna_id='$id'");

    echo json_encode(['status'=>true,'message'=>"Tournament successfully ended."]);
    exit;
}

echo json_encode(['status'=>false,'message'=>"Invalid request"]);
