<?php
header('Content-Type: application/json');
include "conn.php";
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../php-error.log');


$status = false;
$message = "";

$a = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_STRING);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    if (!empty($postData)) {
        extract($postData, EXTR_SKIP);
    }
} else {
    echo json_encode(array('status' => false, 'message' => "Bad request"));
    die();
}

if ($a == "newEvent") {
    $sql = "INSERT INTO tbl_event(ev_name,ev_description,ev_address,start,end,ev_status) VALUES('$e_title','$e_description','$e_venue','$e_start','$e_end','1')";
    if ($con->query($sql)) {
        $status = true;
        $message = mysqli_insert_id($con);
    } else {
        $message = "Something went wrong.";
    }
}
if ($a == "editEvent") {
    $sql = "UPDATE tbl_event SET ev_name='$e_title',ev_description='$e_description',ev_address='$e_venue',start='$e_start',end='$e_end' WHERE ev_id='$e_id'";
    if ($con->query($sql)) {
        $status = true;
    } else {
        $message = "Something went wrong.";
    }
}
if ($a == "deleteEvent") {
    $sql = "DELETE FROM tbl_tournament WHERE ev_id='$id'";
    if ($con->query($sql)) {
        $con->query("DELETE FROM tbl_event WHERE ev_id='$id'");
        $status = true;
    } else {
        $message = "Something went wrong.";
    }
}
if ($a == "add_sport") {
    $name = $_POST['name'] ?? '';

    // Check if image uploaded
    if (!isset($_FILES['img']['tmp_name']) || !is_uploaded_file($_FILES['img']['tmp_name'])) {
        echo json_encode(['status' => false, 'message' => 'No image uploaded.']);
        exit;
    }

    // Check if rules uploaded
    if (!isset($_FILES['rules']['tmp_name']) || !is_uploaded_file($_FILES['rules']['tmp_name'])) {
        echo json_encode(['status' => false, 'message' => 'No rules file uploaded.']);
        exit;
    }

    // Encode image
    $img = base64_encode(file_get_contents($_FILES['img']['tmp_name']));

    // Move rules file
    $rules_tmp = $_FILES['rules']['tmp_name'];
    $rules_name = basename($_FILES['rules']['name']);
    $rules_path = "upload/rules/" . $rules_name;

    if (!is_dir("upload/rules")) {
        mkdir("upload/rules", 0777, true);
    }

    if (move_uploaded_file($rules_tmp, $rules_path)) {
        $sql = "INSERT INTO tbl_sports(name, img, rules) VALUES('$name', '$img', '$rules_path')";
        if ($con->query($sql)) {
            echo json_encode([
                "status" => true,
                "message" => "Sport added successfully!",
                "id" => mysqli_insert_id($con)
            ]);
            exit;
        } else {
            echo json_encode(["status" => false, "message" => "Database error: " . $con->error]);
            exit;
        }
    } else {
        echo json_encode(["status" => false, "message" => "Failed to upload rules file."]);
        exit;
    }
}

if ($a == "edit_sport") {
    $sql = "UPDATE tbl_sports SET name='$name'";
    if (isset($_FILES['img'])) {
        $img = base64_encode(file_get_contents($_FILES['img']['tmp_name']));
        $sql = $sql . ", img='$img'";
    }
    if (isset($_FILES['rules'])) {
        $rules = $_FILES['rules']['tmp_name'];
        $rules_name = $_FILES['rules']['name'];
        if (move_uploaded_file($rules, "upload/rules/" . $rules_name)) {
            $location = "upload/rules/" . $rules_name;
            $sql = $sql . ", rules='$location'";
        }
    }
    $sport = $_SESSION['game_mode'];
    $sql = $sql . " WHERE sport_id='$sport'";
    if ($con->query($sql)) {
        $status = true;
    }
}
if ($a == "add_ass") {
    $logo = base64_encode(file_get_contents($_FILES['logo']['tmp_name']));

    $sql = "INSERT INTO tbl_association(name,ass_desc,img_logo) VALUES('$name','$desc','$logo')";
    if ($con->query($sql)) {
        $status = true;
        $message = mysqli_insert_id($con);
    } else {
        $message = "Something Went Wrong!";
    }
}
if ($a == "mod_ass") {
    if (isset($_POST['logo']) && $_POST['logo'] == "undefined") {
        $sql = "UPDATE `tbl_association` SET name='$name',ass_desc='$desc' WHERE ass_id='$id'";
    } else {
        $logo = base64_encode(file_get_contents($_FILES['logo']['tmp_name']));
        $sql = "UPDATE `tbl_association` SET name='$name',ass_desc='$desc',img_logo='$logo' WHERE ass_id='$id'";
    }
    if ($con->query($sql)) {
        $status = true;
    } else {
        $message = "Something Went Wrong!";
    }
}
if ($a == "addgame_modes") {
    $data = json_decode($_POST['data'], true);
    foreach ($data as $key) {
        $key0 = $key[0];
        $key1 = $key[1];
        $key2 = $key[2];
        $key3 = $key[3];
        $key4 = $key[4];
        if ($key[3] == 1) {
            $sql = "INSERT INTO tbl_game_modes(sport_id,name,players,gm_cat_id,scoring,sets) VALUES('$id','$key0','$key1','$key2','$key3','$key4')";
        } else {
            $key5 = $key[5];
            $sql = "INSERT INTO tbl_game_modes(sport_id,name,players,gm_cat_id,scoring,sets,point_base) VALUES('$id','$key0','$key1','$key2','$key3',$key4,'$key5')";
        }
        if ($con->query($sql)) {
            $status = true;
        }
    }
}
if ($a == "verify_acc") {
    $sql = "UPDATE tbl_user SET status='1' WHERE user_id='$id'";
    if ($con->query($sql)) {
        $status = true;
    } else {
        $message = "Something went wrong";
    }
}
if ($a == "reject_acc") {
    $sql = "UPDATE tbl_user SET status='2' WHERE user_id='$id'";
    if ($con->query($sql)) {
        $status = true;
    } else {
        $message = "Something went wrong";
    }
}
if ($a == "game_mode") {
    if (isset($game_id)) {
        if ($scoring == 1) {
            $sql = "UPDATE tbl_game_modes SET gm_cat_id='$category',name='$name_mode',players='$player',scoring='$scoring',sets='$quarters' WHERE game_id='$game_id'";
        } else {
            $sql = "UPDATE tbl_game_modes SET gm_cat_id='$category',name='$name_mode',players='$player',scoring='$scoring',sets='$game_set',point_base='$points' WHERE game_id='$game_id'";
        }
    } else {
        $sport = $_SESSION['game_mode'];
        if ($scoring == 1) {
            $sql = "INSERT INTO tbl_game_modes(gm_cat_id,name,players,scoring,sets,sport_id) VALUES('$category','$name_mode','$player','$scoring','$quarters','$sport')";
        } else {
            $sql = "INSERT INTO tbl_game_modes(gm_cat_id,name,players,scoring,sets,sport_id,point_base) VALUES('$category','$name_mode','$player','$scoring','$game_set','$sport','$points')";
        }
    }
    if ($con->query($sql)) {
        $status = true;
    } else {
        $message = "something went wrong.";
    }
}
if ($a == "addtournament") {
    $ev_id = $_SESSION['ev_id'];
    $status = false;
    $message = "Tournament Already Existed";

    // Check if tournament already exists for the same game and event
    $query = $con->query("SELECT tourna_id FROM tbl_tournament WHERE game_id='$game_mode' AND ev_id='$ev_id' LIMIT 1");
    if (mysqli_num_rows($query) == 0) {
        // Insert new tournament
        $sql = "INSERT INTO tbl_tournament (game_id, ev_id, maximum, minimum)
                VALUES ('$game_mode', '$ev_id', '$max_player', '$min_player')";
        if ($con->query($sql)) {
            $status = true;
            $message = "Tournament added successfully!";
            $id = mysqli_insert_id($con);

            // Create teams for all associations
            $sql = $con->query("SELECT ass_id FROM tbl_association");
            while ($row = mysqli_fetch_assoc($sql)) {
                $ass_id = $row['ass_id'];
                $con->query("INSERT INTO tbl_team (tourna_id, ass_id) VALUES ('$id', '$ass_id')");
            }
        } else {
            $message = "Error adding tournament: " . $con->error;
        }
    }

    echo json_encode(["status" => $status, "message" => $message]);
    exit;
}


if ($a == "modifytournament") {
    $status = false;
    $message = "Failed to update tournament";

    // Update tournament details including the new tournament_type column
    $sql = "UPDATE tbl_tournament 
            SET maximum='$max_player', minimum='$min_player' 
            WHERE tourna_id='$tourna_id'";
    if ($con->query($sql)) {
        $status = true;
        $message = "Tournament updated successfully!";
    } else {
        $message = "Error updating tournament: " . $con->error;
    }

    echo json_encode(["status" => $status, "message" => $message]);
    exit;
}


if ($a == "delete_tourna") {
    $status = false;
    $message = "Failed to delete tournament";

    $sql = "DELETE FROM tbl_tournament WHERE tourna_id='$id'";
    if ($con->query($sql)) {
        $status = true;
        $message = "Tournament deleted successfully!";
    } else {
        $message = "Error deleting tournament: " . $con->error;
    }

    echo json_encode(["status" => $status, "message" => $message]);
    exit;
}
/* 🧩 ACCEPT APPLICATION (ASSOCIATION) */
if ($a == "accept_applicant") {
    header('Content-Type: application/json');
    $status = false;
    $message = "";

    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(["status" => false, "message" => "❌ No application ID sent."]);
        exit;
    }

    $sql = $con->query("SELECT ta.tourna_id, ta.ass_id, ta.prof_id 
                        FROM tbl_tourna_application AS ta 
                        WHERE ta.app_id = '$id'");
    if (!$sql || mysqli_num_rows($sql) == 0) {
        echo json_encode(["status" => false, "message" => "❌ Application not found."]);
        exit;
    }

    $row = mysqli_fetch_assoc($sql);
    $tourna_id = $row['tourna_id'];
    $ass_id = $row['ass_id'];
    $prof_id = $row['prof_id'];

    // ✅ Get tournament info
    $chk = $con->query("SELECT maximum FROM tbl_tournament WHERE tourna_id='$tourna_id'");
    $chk_row = mysqli_fetch_assoc($chk);
    $max_players = $chk_row['maximum'];

    // ✅ Count accepted players for this association in this tournament
    $count = $con->query("SELECT COUNT(*) AS total 
                          FROM tbl_tourna_application 
                          WHERE tourna_id='$tourna_id' 
                          AND ass_id='$ass_id' 
                          AND status='1'");
    $count_row = mysqli_fetch_assoc($count);

    if ($count_row['total'] >= $max_players) {
        echo json_encode(["status" => false, "message" => "⚠️ Tournament has reached its maximum number of players."]);
        exit;
    }

    // ✅ Accept application
    $update = $con->query("UPDATE tbl_tourna_application SET status='1' WHERE app_id='$id'");
    if ($update) {
        $get_team = $con->query("SELECT team_id FROM tbl_team WHERE tourna_id='$tourna_id' AND ass_id='$ass_id' LIMIT 1");
        if ($team_row = mysqli_fetch_assoc($get_team)) {
            $team_id = $team_row['team_id'];
            $insert = $con->query("INSERT INTO tbl_team_players (team_id, app_id) VALUES ('$team_id', '$id')");
            $status = true;
            $message = "✅ Player accepted successfully!";
        } else {
            $status = true;
            $message = "✅ Player accepted, but team not found.";
        }
    } else {
        $message = "⚠️ Failed to update application status.";
    }

    echo json_encode(["status" => $status, "message" => $message]);
    exit;
}


/* 🧩 PLAYER APPLY TO TOURNAMENT */
if ($a == "apply_tourna") {
    $username = $_SESSION['session_username'];
    $sql = $con->query("
        SELECT p.prof_id, ap.ass_id
        FROM tbl_user AS u
        LEFT JOIN tbl_profile AS p ON u.user_id = p.user_id
        LEFT JOIN tbl_association_players AS ap ON p.user_id = ap.user_id
        WHERE u.username = '$username'
    ");
    $row = mysqli_fetch_assoc($sql);
    $prof_id = $row['prof_id'];
    $ass_id = $row['ass_id'];
    $tourna_id = $_SESSION['tournament_code'];

    // Get tournament info
    $q = $con->query("SELECT ev_id, g.gm_cat_id, t.maximum 
                      FROM tbl_tournament AS t
                      LEFT JOIN tbl_game_modes AS g ON t.game_id = g.game_id
                      WHERE t.tourna_id='$tourna_id'");
    $r = mysqli_fetch_assoc($q);
    $ev_id = $r['ev_id'];
    $gm_cat_id = $r['gm_cat_id'];
    $max_players = $r['maximum'];

    // ✅ Check if tournament is already full for this association
    $count = $con->query("SELECT COUNT(*) AS total 
                          FROM tbl_tourna_application 
                          WHERE tourna_id='$tourna_id' 
                          AND ass_id='$ass_id' 
                          AND status='1'");
    $c = mysqli_fetch_assoc($count);

    if ($c['total'] >= $max_players) {
        echo json_encode(['status' => false, 'message' => '⚠️ Tournament is already full.']);
        exit;
    }


    // ✅ Qualification
    $data = false;
    if ($gm_cat_id == 3)
        $data = true;
    else {
        $sql = $con->query("SELECT gender FROM tbl_profile WHERE prof_id='$prof_id' AND gender='$gm_cat_id'");
        if (mysqli_num_rows($sql) > 0)
            $data = true;
    }

    if ($data) {
        $check = $con->query("
            SELECT 1 FROM tbl_tourna_application AS ta
            LEFT JOIN tbl_profile AS p ON ta.prof_id = p.prof_id
            LEFT JOIN tbl_association_players AS ap ON p.user_id = ap.user_id
            WHERE ta.jersey_number = '$a_jersey_number'
              AND ap.ass_id = '$ass_id'
              AND ta.tourna_id = '$tourna_id'
              AND ta.ev_id = '$ev_id'
        ");
        if (mysqli_num_rows($check) == 0) {
            $insert = $con->query("
                INSERT INTO tbl_tourna_application (prof_id, tourna_id, ev_id, ass_id, jersey_number, status)
                VALUES ('$prof_id', '$tourna_id', '$ev_id', '$ass_id', '$a_jersey_number', 0)
            ");
            if ($insert) {
                $status = true;
                $message = "✅ Application submitted successfully.";
            } else {
                $message = "❌ Database error: " . $con->error;
            }
        } else {
            $message = "⚠️ Jersey number already exists for this association.";
        }
    } else {
        $message = "❌ You are not qualified for this tournament.";
    }

    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

/* 🧩 APPLY BY ASSOCIATION (AUTO-ACCEPT, LIMIT CHECK INCLUDED) */
if ($a == "apply_tourna_ass") {
    $prof_id = $_POST['prof_id'];
    $a_jersey_number = $_POST['a_jersey_number'];
    $tourna_id = $_SESSION['tournament_code'];

    // 🔹 Fetch tournament info
    $sql = $con->query("
        SELECT t.ev_id, g.gm_cat_id, t.maximum 
        FROM tbl_tournament AS t 
        LEFT JOIN tbl_game_modes AS g ON t.game_id = g.game_id 
        WHERE t.tourna_id = '$tourna_id'
    ");
    if (!$sql || mysqli_num_rows($sql) == 0) {
        echo json_encode(['status' => false, 'message' => '❌ Tournament not found.']);
        exit;
    }
    $tournament = mysqli_fetch_assoc($sql);
    $ev_id = $tournament['ev_id'];
    $gm_cat_id = $tournament['gm_cat_id'];
    $max_players = $tournament['maximum'];

    $data = false;
    $ass_id = null;

    // 🔹 Verify player eligibility
    if ($gm_cat_id == 3) {
        // Open category
        $sql = $con->query("
            SELECT P.user_id, AP.ass_id 
            FROM tbl_profile AS P 
            LEFT JOIN tbl_association_players AS AP ON P.user_id = AP.user_id 
            WHERE P.prof_id = '$prof_id'
        ");
        if ($row = mysqli_fetch_assoc($sql)) {
            $ass_id = $row['ass_id'];
            $data = true;
        }
    } else {
        // Gender-restricted category
        $sql = $con->query("
            SELECT P.user_id, AP.ass_id 
            FROM tbl_profile AS P 
            LEFT JOIN tbl_association_players AS AP ON P.user_id = AP.user_id 
            WHERE P.prof_id = '$prof_id' 
              AND P.gender = '$gm_cat_id'
        ");
        if ($row = mysqli_fetch_assoc($sql)) {
            $ass_id = $row['ass_id'];
            $data = true;
        }
    }

    if ($data) {
        // ✅ Check if tournament already reached maximum players for this association
        $count_query = $con->query("
            SELECT COUNT(*) AS total 
            FROM tbl_tourna_application 
            WHERE tourna_id = '$tourna_id' 
              AND ass_id = '$ass_id' 
              AND status = '1'
        ");
        $count_row = mysqli_fetch_assoc($count_query);

        if ($count_row['total'] >= $max_players) {
            echo json_encode(['status' => false, 'message' => '⚠️ Tournament has reached its maximum number of players.']);
            exit;
        }

        // ✅ Check if jersey number already exists in this association for the same tournament
        $check = $con->query("
            SELECT 1 
            FROM tbl_tourna_application AS ta
            LEFT JOIN tbl_profile AS p ON ta.prof_id = p.prof_id
            LEFT JOIN tbl_association_players AS ap ON p.user_id = ap.user_id
            WHERE ta.jersey_number = '$a_jersey_number'
              AND ap.ass_id = '$ass_id'
              AND ta.tourna_id = '$tourna_id'
              AND ta.ev_id = '$ev_id'
        ");

        if (mysqli_num_rows($check) == 0) {
            // ✅ Auto-accept the player (status = 1)
            $insert = $con->query("
                INSERT INTO tbl_tourna_application 
                    (prof_id, tourna_id, ev_id, ass_id, jersey_number, status)
                VALUES 
                    ('$prof_id', '$tourna_id', '$ev_id', '$ass_id', '$a_jersey_number', 1)
            ");

            if ($insert) {
                $app_id = mysqli_insert_id($con);

                // 🔹 Find or link player to team
                $get_team = $con->query("SELECT team_id FROM tbl_team WHERE tourna_id='$tourna_id' AND ass_id='$ass_id' LIMIT 1");
                if ($team_row = mysqli_fetch_assoc($get_team)) {
                    $team_id = $team_row['team_id'];
                    $insert_team_player = $con->query("
                        INSERT INTO tbl_team_players (team_id, app_id) VALUES ('$team_id', '$app_id')
                    ");

                    if ($insert_team_player) {
                        $status = true;
                        $message = "✅ Player successfully added and automatically accepted.";
                    } else {
                        $status = true;
                        $message = "⚠️ Application accepted but failed to link player to team: " . $con->error;
                    }
                } else {
                    $status = true;
                    $message = "✅ Player accepted but no team found for this association.";
                }
            } else {
                $status = false;
                $message = "❌ Database error while adding player: " . $con->error;
            }
        } else {
            $status = false;
            $message = "⚠️ Jersey number already exists among association players.";
        }
    } else {
        $status = false;
        $message = "❌ Player not qualified for this category.";
    }

    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}


if ($a == "set_match_date") {
    $match_id = $_COOKIE['match_id'];
    $sql = "UPDATE tbl_matches SET start_date='$game_start' WHERE match_id='$match_id'";
    if ($con->query($sql)) {
        $status = true;
    }
}
if ($a == "manage_match") {
    $match_id = $_COOKIE['match_id'];
    $user_id = $_SESSION['session_user_id'];
    $sql = $con->query("SELECT user_id FROM tbl_matches WHERE match_id='$match_id'");
    $row = mysqli_fetch_assoc($sql);

    if ($row['user_id'] == null) {
        $sql = "UPDATE tbl_matches SET user_id='$user_id' WHERE match_id='$match_id'";
        if ($con->query($sql)) {
            $status = true;
        }
    }
}

if ($a == "start_match") {
    $match_id = $_COOKIE['match_id'];
    $limit = 3 - $draw;

    $continue = true;
    $t1_dq = false;
    $t2_dq = false;

    $sql = $con->query("
        SELECT auto_disqualify, team1, team2, minimum, maximum 
        FROM tbl_matches AS m 
        LEFT JOIN tbl_tournament AS t ON m.tourna_id = t.tourna_id 
        WHERE m.match_id = '$match_id'
    ");

    $row = mysqli_fetch_assoc($sql);

    $team1 = $row['team1'];
    $team2 = $row['team2'];

    $chk1 = $con->query("SELECT COUNT(player_id) AS team FROM tbl_team_players WHERE team_id = '$team1'");
    $team1_count = mysqli_fetch_assoc($chk1);

    if ($team1_count['team'] < $row['minimum'] || $team1_count['team'] > $row['maximum']) {
        if ($row['auto_disqualify'] != 0 || $limit == 0) {
            $con->query("UPDATE tbl_team SET disqualify = '1' WHERE team_id = '$team1'");
            $t1_dq = true;
        } else {
            $continue = false;
        }
    }

    $chk2 = $con->query("SELECT COUNT(player_id) AS team FROM tbl_team_players WHERE team_id = '$team2'");
    $team2_count = mysqli_fetch_assoc($chk2);

    if ($team2_count['team'] < $row['minimum'] || $team2_count['team'] > $row['maximum']) {
        if ($row['auto_disqualify'] != 0 || $limit == 0) {
            $con->query("UPDATE tbl_team SET disqualify = '1' WHERE team_id = '$team2'");
            $t2_dq = true;
        } else {
            $continue = false;
        }
    }

    $start_date = date("Y-m-d H:i:s");

    if ($t1_dq && $t2_dq) {
        $message = "Both teams disqualified. Game concluded.";
        $con->query("UPDATE tbl_matches SET status='2', start_date='$start_date' WHERE match_id='$match_id'");
    } elseif ($t1_dq) {
        $message = "Team 1 disqualified. Game concluded.";
        $con->query("UPDATE tbl_matches SET status='2', start_date='$start_date', winner='$team2' WHERE match_id='$match_id'");
        $sql = $con->query("SELECT place FROM tbl_team WHERE team_id='$team2'");
        $winner = mysqli_fetch_assoc($sql);

        $place = $winner['place'] - 1;
        $con->query("UPDATE tbl_team SET place=$place,inMatch='0' WHERE team_id='$team2'");
        $con->query("UPDATE tbl_team SET inMatch='0' WHERE team_id='$team1'");

    } elseif ($t2_dq) {
        $message = "Team 2 disqualified. Game concluded.";
        $con->query("UPDATE tbl_matches SET status='2', start_date='$start_date', winner='$team1' WHERE match_id='$match_id'");
        $sql = $con->query("SELECT place FROM tbl_team WHERE team_id='$team1'");
        $winner = mysqli_fetch_assoc($sql);

        $place = $winner['place'] - 1;
        $con->query("UPDATE tbl_team SET place=$place,inMatch='0' WHERE team_id='$team1'");
        $con->query("UPDATE tbl_team SET inMatch='0' WHERE team_id='$team2'");
    } elseif (!$continue) {
        $message = "The team is not qualified. Fulfill the qualifications before starting the game. Auto disqualify in " . $limit . " click's";
    } else {
        $con->query("UPDATE tbl_matches SET status='1', start_date='$start_date' WHERE match_id='$match_id'");
        $status = true;
        $con->query("INSERT INTO tbl_score_match(match_id) VALUES('$match_id')");
        $message = mysqli_insert_id($con);
    }
}

if ($a == "player_stats") {
    $sql = "UPDATE tbl_team_players SET ingame='$stats' WHERE player_id='$id'";
    if ($con->query($sql)) {
        $status = true;
    }
}
if ($a == "game_log") {
    $match_id = $_COOKIE['match_id'];
    $sql = "INSERT INTO tbl_match_log(match_id,log_message) VALUES('$match_id','$log_message')";
    if ($con->query($sql)) {
        $status = true;
    }
}
if ($a == "scoring") {
    $match_id = $_COOKIE['match_id'];
    $history = json_encode($history);
    
    // Get current score record
    $current_score = $con->query("SELECT set_quarter, team1, team2 FROM tbl_score_match WHERE score_id='$score_id'");
    $current = mysqli_fetch_assoc($current_score);
    $current_quarter = intval($current['set_quarter']);
    $current_team1 = intval($current['team1']);
    $current_team2 = intval($current['team2']);
    
    // Get tournament sets
    $tourna = $con->query("SELECT gm.sets FROM tbl_matches AS m
                           LEFT JOIN tbl_tournament AS t ON m.tourna_id=t.tourna_id
                           LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id
                           WHERE m.match_id='$match_id'");
    $tourna_data = mysqli_fetch_assoc($tourna);
    $regulation_sets = intval($tourna_data['sets']);
    
    // Determine the new scores
    $team1_new = $current_team1;
    $team2_new = $current_team2;
    
    // Check if we're in overtime
    if($current_quarter > $regulation_sets){
        // Get the last non-zero score from regulation or previous OT quarters
        $baseline_sql = $con->query("SELECT team1, team2 FROM tbl_score_match 
                                    WHERE match_id='$match_id' 
                                    AND (team1 > 0 OR team2 > 0)
                                    ORDER BY score_id DESC LIMIT 1");
        $baseline = mysqli_fetch_assoc($baseline_sql);
        
        if($baseline){
            $baseline_team1 = intval($baseline['team1']);
            $baseline_team2 = intval($baseline['team2']);
            
            // If current scores are 0-0 (OT just started), start from baseline
            if($current_team1 == 0 && $current_team2 == 0){
                if($team == 'team1'){
                    $team1_new = $baseline_team1 + intval($score);
                    $team2_new = $baseline_team2;
                } else {
                    $team1_new = $baseline_team1;
                    $team2_new = $baseline_team2 + intval($score);
                }
            } else {
                // OT already has scores, add to existing OT scores
                if($team == 'team1'){
                    $team1_new = $current_team1 + intval($score);
                    $team2_new = $current_team2;
                } else {
                    $team1_new = $current_team1;
                    $team2_new = $current_team2 + intval($score);
                }
            }
        } else {
            // Fallback: just add to current scores
            if($team == 'team1'){
                $team1_new = $current_team1 + intval($score);
                $team2_new = $current_team2;
            } else {
                $team1_new = $current_team1;
                $team2_new = $current_team2 + intval($score);
            }
        }
    } else {
        // Regular season (not overtime) - add to current scores
        if($team == 'team1'){
            $team1_new = $current_team1 + intval($score);
            $team2_new = $current_team2;
        } else {
            $team1_new = $current_team1;
            $team2_new = $current_team2 + intval($score);
        }
    }
    
    // Update score in database with both team scores
    $sql = "UPDATE tbl_score_match SET team1='$team1_new', team2='$team2_new', score_history='$history' WHERE score_id='$score_id'";
    if ($con->query($sql)) {
        $status = true;
    }
}
if ($a == "chk") {
    $match_id = $_COOKIE['match_id'];
    $sql = $con->query("SELECT scoring,sets,point_base FROM tbl_matches AS m
                        LEFT JOIN tbl_tournament AS t ON m.tourna_id=t.tourna_id
                        LEFT JOIN tbl_game_modes AS gm ON gm.game_id=t.game_id
                        WHERE m.match_id='$match_id' AND gm.scoring='2'");
    $row = mysqli_fetch_assoc($sql);
    if ($score >= $row['point_base']) {
        $opp_team = ($team == "team1") ? 'team2' : 'team1';
        $point = $con->query("SELECT $opp_team FROM tbl_score_match WHERE match_id='$match_id' ORDER BY score_id DESC");
        $point = mysqli_fetch_assoc($point);
        $point = $point[$opp_team];

        if ($score <= $point + 1) {
            return;
        }

        $status = true;
        $sets = intval($row['sets']) / 2;
        $sql = $con->query("SELECT COUNT(set_quarter) AS set_quarter FROM tbl_score_match WHERE winner='$team' AND match_id='$match_id'");
        $val1 = mysqli_fetch_assoc($sql);

        $sql = $con->query("SELECT set_quarter FROM tbl_score_match WHERE score_id='$score_id'");
        $val2 = mysqli_fetch_assoc($sql);

        if (($val1['set_quarter'] + 1) > $sets) {
            $con->query("UPDATE tbl_score_match SET winner='$team' WHERE score_id ='$score_id'");
            $sql = $con->query("SELECT $team,place,a.name
                                FROM tbl_matches AS m 
                                LEFT JOIN tbl_team AS t ON m.$team=t.team_id 
                                LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id
                                WHERE match_id='$match_id'");
            $winner = mysqli_fetch_assoc($sql);
            $win = $winner[$team];
            $place = $winner['place'] - 1;
            $con->query("UPDATE tbl_team SET place=$place WHERE team_id='$win'");
            $con->query("UPDATE tbl_matches SET winner ='$win' WHERE match_id='$match_id'");

            $sql = $con->query("SELECT team1,team2 FROM tbl_matches WHERE match_id='$match_id'");
            $row = mysqli_fetch_assoc($sql);
            $team1 = $row['team1'];
            $team2 = $row['team2'];
            $con->query("UPDATE tbl_team SET inMatch='0' WHERE team_id IN ('$team1','$team2')");

            $message = [
                "status" => true,
                "winner" => $winner['name']
            ];
        } else {
            $new_set = intval($val2['set_quarter']) + 1;
            $con->query("UPDATE tbl_score_match SET winner='$team' WHERE score_id ='$score_id'");
            $con->query("INSERT INTO tbl_score_match(match_id,set_quarter) VALUES('$match_id','$new_set')");

            $message = [
                "status" => false,
                "id" => mysqli_insert_id($con)
            ];
        }
    }
}

if ($a == "quarter") {
    $match_id = $_COOKIE['match_id'];
    extract($_POST['score']);

    $sql = $con->query("SELECT sets FROM tbl_matches AS m
                        LEFT JOIN tbl_tournament AS t ON m.tourna_id=t.tourna_id
                        LEFT JOIN tbl_game_modes AS gm ON gm.game_id=t.game_id
                        WHERE m.match_id='$match_id' AND gm.scoring='1'");
    $row = mysqli_fetch_assoc($sql);
    if ($row['sets']) {
        $status = true;
        $sets = intval($row['sets']);

        $sql = $con->query("SELECT set_quarter,team1,team2 FROM tbl_score_match WHERE score_id='$score_id'");
        $val1 = mysqli_fetch_assoc($sql);

        $current_set = intval($val1['set_quarter']);
        $regulation_sets = $sets;
        $team = null;

// Helper function to display quarter/OT label
$getQuarterLabel = function($quarter_num, $regulation) {
    if ($quarter_num <= $regulation) {
        if ($quarter_num == 1) return "1st Quarter";
        elseif ($quarter_num == 2) return "2nd Quarter";
        elseif ($quarter_num == 3) return "3rd Quarter";
        else return "4th Quarter";
    } else {
        $ot_num = $quarter_num - $regulation;
        return ($ot_num === 1) ? "OT" : "OT" . $ot_num;
    }
};

        // If we are at or past regulation (e.g. 4th quarter) handle end-of-game or overtime
        if ($current_set >= $regulation_sets) {
            // Determine leader for this set
            if ($val1['team1'] > $val1['team2']) {
                $team = "team1";
            } else if ($val1['team1'] < $val1['team2']) {
                $team = "team2";
            } else {
                // Tie at end of regulation -> start overtime
                $new_set = $current_set + 1;
                $con->query("UPDATE tbl_score_match SET winner='draw' WHERE score_id ='$score_id'");
                $con->query("INSERT INTO tbl_score_match(match_id,set_quarter) VALUES('$match_id','$new_set')");

                $quarter_label = $getQuarterLabel($new_set, $regulation_sets);
                $message = [
                    "id" => mysqli_insert_id($con),
                    "quarter" => $new_set,
                    "quarter_label" => $quarter_label,
                    "end" => false
                ];
            }

            // If not a tie, conclude the match and update places/winner
            if ($team !== null) {
                $con->query("UPDATE tbl_score_match SET winner='$team' WHERE score_id ='$score_id'");
                $sql = $con->query("SELECT $team,place FROM tbl_matches AS m LEFT JOIN tbl_team AS t ON m.$team=t.team_id WHERE match_id='$match_id'");
                $winner = mysqli_fetch_assoc($sql);
                $win = $winner[$team];
                $place = $winner['place'] - 1;
                $con->query("UPDATE tbl_team SET place=$place WHERE team_id='$win'");
                $con->query("UPDATE tbl_matches SET winner ='$win' WHERE match_id='$match_id'");

                $sql = $con->query("SELECT team1,team2 FROM tbl_matches WHERE match_id='$match_id'");
                $row = mysqli_fetch_assoc($sql);
                $team1 = $row['team1'];
                $team2 = $row['team2'];
                $con->query("UPDATE tbl_team SET inMatch='0' WHERE team_id IN ('$team1','$team2')");

                $message = true;
            }
        } else {
            // Normal quarter transition (not end of regulation)
            $new_set = $current_set + 1;
            $end = ($regulation_sets == $new_set) ? true : false;
            if ($val1['team1'] > $val1['team2']) {
                $team = "team1";
            } else if ($val1['team1'] < $val1['team2']) {
                $team = "team2";
            } else {
                $team = "draw";
            }
            $con->query("UPDATE tbl_score_match SET winner='$team' WHERE score_id ='$score_id'");
            $con->query("INSERT INTO tbl_score_match(match_id,set_quarter,team1,team2) VALUES('$match_id','$new_set','$team1','$team2')");

            $quarter_label = $getQuarterLabel($new_set, $regulation_sets);
            $message = [
                "id" => mysqli_insert_id($con),
                "quarter" => $new_set,
                "quarter_label" => $quarter_label,
                "end" => $end
            ];
        }
    }
}

if ($a == "redo_score") {
    $history = (isset($history)) ? json_encode($history) : null;
    extract($_POST['score']);

    $sql = "UPDATE tbl_score_match SET team1='$team1',team2='$team2',score_history='$history' WHERE score_id ='$score_id'";
    if ($con->query($sql)) {
        $status = true;
    }
}
if ($a == "delete_applicant") {
    $app_id = $_POST['app_id'] ?? '';
    if (!empty($app_id)) {
        $delete = $con->query("DELETE FROM tbl_tourna_application WHERE app_id='$app_id'");
        echo json_encode(['status' => $delete ? true : false]);
    } else {
        echo json_encode(['status' => false]);
    }
    exit;
}

if ($a == "delete_ass") {
    if ($con->query("DELETE FROM tbl_association WHERE ass_id='$id'")) {
        $status = true;
    }
}
if ($a == "point_system") {
    if ($point_id != 0) {
        $sql = "UPDATE tbl_point_system SET point='$points' WHERE point_id='$point_id'";
    } else {
        $sql = "INSERT INTO tbl_point_system(point,game_id) VALUES('$points','$game_id')";
    }

    if ($con->query($sql)) {
        $status = true;
        $message = $game_id;
    }
}
/* UPDATE PROFILE (ASSOCIATION USER) */
if ($a == "update_profile_ass") {
    $status = false;
    $message = "No changes detected or invalid user";

    // Validate user
    if (!isset($user_id) || empty($user_id)) {
        echo json_encode(["status" => false, "message" => "Invalid user."]);
        exit;
    }

    // Prepare fields
    $fullname = trim($fullname ?? "");
    $gender = trim($gender ?? "");
    $birthday = trim($birthday ?? "");
    $contact = trim($contact ?? "");
    $email = trim($email ?? "");

    // Validate fields
    if ($fullname == "" || $gender == "" || $birthday == "" || $contact == "" || $email == "") {
        echo json_encode(["status" => false, "message" => "All fields are required."]);
        exit;
    }

    // Check if changes are the same
    $current = $con->prepare("SELECT fullname, gender, birthday, contact, email FROM tbl_profile_ass WHERE user_id = ?");
    $current->bind_param("i", $user_id);
    $current->execute();
    $result = $current->get_result()->fetch_assoc();

    if (
        $result['fullname'] == $fullname &&
        $result['gender'] == $gender &&
        $result['birthday'] == $birthday &&
        $result['contact'] == $contact &&
        $result['email'] == $email
    ) {
        echo json_encode(["status" => false, "message" => "No changes detected."]);
        exit;
    }

    // Update query
    $stmt = $con->prepare("
        UPDATE tbl_profile_ass 
        SET fullname=?, gender=?, birthday=?, contact=?, email=?
        WHERE user_id=?
    ");
    $stmt->bind_param("sisssi", $fullname, $gender, $birthday, $contact, $email, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "Profile updated successfully."]);
    } else {
        echo json_encode(["status" => false, "message" => "Error updating profile: " . $con->error]);
    }

    exit;
}


if ($_GET['a'] == 'update_profile') {
    // Get user ID from POST or session
    $user_id = $_POST['user_id'] ?? $_SESSION['session_user_id'] ?? null;
    if (!$user_id) {
        echo json_encode(['status' => false, 'message' => 'User not logged in']);
        exit;
    }

    // Collect POST data
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $prog_id = trim($_POST['prog_id'] ?? '');
    $year_level = trim($_POST['year_level'] ?? '');
    $ass_id = trim($_POST['ass_id'] ?? '');

    // Validate required fields
    if (
        $first_name === '' || $last_name === '' || $gender === '' || $birthday === '' ||
        $contact === '' || $email === '' || $prog_id === '' || $year_level === '' || $ass_id === ''
    ) {
        echo json_encode(['status' => false, 'message' => 'All required fields must be filled']);
        exit;
    }

    // Optional profile image
    $profile = null;
    if (isset($_FILES['profile']) && $_FILES['profile']['size'] > 0) {
        $profile = base64_encode(file_get_contents($_FILES['profile']['tmp_name']));
    }

    // Start transaction
    $con->begin_transaction();

    try {
        // Update tbl_profile
        if ($profile) {
            $stmt = $con->prepare("
                UPDATE tbl_profile 
                SET first_name = ?, middle_name = ?, last_name = ?, suffix = ?, gender = ?, birthday = ?, contact = ?, email = ?, profile = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param("sssssssssi", $first_name, $middle_name, $last_name, $suffix, $gender, $birthday, $contact, $email, $profile, $user_id);
        } else {
            $stmt = $con->prepare("
                UPDATE tbl_profile 
                SET first_name = ?, middle_name = ?, last_name = ?, suffix = ?, gender = ?, birthday = ?, contact = ?, email = ?
                WHERE user_id = ?
            ");
            $stmt->bind_param("ssssssssi", $first_name, $middle_name, $last_name, $suffix, $gender, $birthday, $contact, $email, $user_id);
        }
        $stmt->execute();

        // Check if association record exists
        $check = $con->prepare("SELECT 1 FROM tbl_association_players WHERE user_id = ?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // Update existing association info
            $update = $con->prepare("
                UPDATE tbl_association_players
                SET ass_id = ?, prog_id = ?, year_level = ?
                WHERE user_id = ?
            ");
            $update->bind_param("sssi", $ass_id, $prog_id, $year_level, $user_id);
            $update->execute();
        } else {
            // Insert new association info
            $insert = $con->prepare("
                INSERT INTO tbl_association_players (ass_id, user_id, prog_id, year_level)
                VALUES (?, ?, ?, ?)
            ");
            $insert->bind_param("siss", $ass_id, $user_id, $prog_id, $year_level);
            $insert->execute();
        }

        $con->commit();
        echo json_encode(['status' => true, 'message' => 'Profile updated successfully']);
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode(['status' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
    }
    exit;
}




if ($a == "delete_point_system") {
    if ($con->query("DELETE FROM tbl_point_system WHERE point_id='$point_id'")) {
        $status = true;
    }
}
// if($a == "get_pending_count"){
//     session_start();
//     include "../conn.php";

//     $ev_id = $_SESSION['ev_id'] ?? null;
//     $ass_id = $_SESSION['ass_id'] ?? null;

//     $total = 0;
//     if($ev_id && $ass_id){
//         $sql = $con->query("
//             SELECT COUNT(*) AS total
//             FROM tbl_tourna_application AS ta
//             LEFT JOIN tbl_profile AS p ON ta.prof_id = p.prof_id
//             LEFT JOIN tbl_association_players AS ap ON p.user_id = ap.user_id
//             WHERE ta.status = '0'
//             AND (ta.ev_id = '$ev_id' OR ta.ev_id IS NULL)
//             AND ap.ass_id = '$ass_id'
//         ");
//         if($sql && $row = $sql->fetch_assoc()){
//             $total = (int)$row['total'];
//         }
//     }

//     echo json_encode([
//         "status" => true,
//         "total" => $total
//     ]);
//     exit;
// }
// Default response
echo json_encode(array('status' => $status, 'message' => $message));
?>
