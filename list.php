<?php
header('Content-Type: application/json');
include "conn.php";
session_start();
$s = isset($_GET['s']) ? filter_var($_GET['s'], FILTER_SANITIZE_STRING) : null;
    
    
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    if (!empty($postData)) {
        extract($postData, EXTR_SKIP);
    }
}
$data = [];
$status = false;

if($s == "sport_details"){
    $sql = $con->query("SELECT img FROM tbl_sports WHERE sport_id='$id'");
    if($row = mysqli_fetch_assoc($sql)){
        $data['img'] = $row['img'];
        $status = true;
        $game_mode = [];
        $sql = $con->query("SELECT gm.game_id,gm.name,c.category 
                            FROM tbl_game_modes AS gm 
                            LEFT JOIN tbl_game_mode_cat AS c ON gm.gm_cat_id=c.gm_cat_id 
                            WHERE sport_id='$id'");
        while($row = mysqli_fetch_assoc($sql)){
            $game_mode[] = [
                'game_id' => $row['game_id'],
                'name' => $row['name'],
                'category' => $row['category']
            ];
        }
        $data['data'] = $game_mode;
    }
}
if($s=="get_player_minimum"){
    $sql = $con->query("SELECT gm.name,gm.players,gm.scoring,c.category
                        FROM tbl_game_modes AS gm 
                        LEFT JOIN tbl_game_mode_cat AS c ON gm.gm_cat_id=c.gm_cat_id  
                        WHERE game_id='$id'");
    if($row = mysqli_fetch_assoc($sql)){
        $status = true;
        $row['scoring'] = ($row['scoring'] ==1)? "Quarter":"Sets";
        $data = $row;
    }
}
if($s=="check_qualification"){
    $status = true;
    $data = ['qualified' => false, 'reason' => ''];
    
    $id = $_SESSION['tournament_code'] ?? null; // tournament ID
    $username = $_SESSION['session_username'] ?? null;

    if(!$id || !$username){
        echo json_encode(["data"=>$data, "status"=>false]);
        exit;
    }

    // Get the tournament’s gender category (gm_cat_id)
    $sql = $con->query("SELECT gm_cat_id 
                        FROM tbl_tournament AS t 
                        LEFT JOIN tbl_game_modes AS g ON t.game_id=g.game_id 
                        WHERE t.tourna_id='$id'");

    $row = mysqli_fetch_assoc($sql);
    if(!$row){
        echo json_encode(["data"=>$data, "status"=>false]);
        exit;
    }

    $category = intval($row['gm_cat_id']); // 1 = Male/Boys, 2 = Female/Girls, 3 = Mixed

    // Get player gender
    $sql = $con->query("SELECT p.gender 
                        FROM tbl_user AS u 
                        LEFT JOIN tbl_profile AS p ON u.user_id=p.user_id 
                        WHERE u.username='$username' LIMIT 1");
    $player = mysqli_fetch_assoc($sql);

    if(!$player){
        echo json_encode(["data"=>$data, "status"=>false]);
        exit;
    }

    $playerGender = intval($player['gender']);

    // Determine qualification
    if($category == 3){ 
        // Mixed = all qualified
        $data['qualified'] = true;
    } elseif($category == 1 && $playerGender == 1){
        // Boys event, male player
        $data['qualified'] = true;
    } elseif($category == 2 && $playerGender == 2){
        // Girls event, female player
        $data['qualified'] = true;
    } else {
        // Gender mismatch — create reason
        if($category == 1){
            $data['reason'] = "This tournament is for boys only.";
        } elseif($category == 2){
            $data['reason'] = "This tournament is for girls only.";
        }
    }

    echo json_encode(["data"=>$data, "status"=>$status]);
    exit;
}

if($s == "get_match_start"){
    $match_id = $_COOKIE['match_id'];
    $sql = $con->query("SELECT start_date FROM tbl_matches WHERE match_id='$match_id' AND start_date != 'NULL'");
    if(mysqli_num_rows($sql) > 0){
        $row = mysqli_fetch_assoc($sql);
        $data = $row['start_date'];
        $status = true;
    }
}
if($s == "matches"){
    $tourna_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $data = [];
    $status = false;

    if($tourna_id){
        $sql = $con->query("SELECT m.match_id,
                                   a1.name AS teamA,
                                   a2.name AS teamB,
                                   m.status,
                                   m.winner,
                                   m.team1,
                                   m.team2,
                                   m.start_date
                            FROM tbl_matches m
                            LEFT JOIN tbl_team t1 ON m.team1=t1.team_id
                            LEFT JOIN tbl_association a1 ON t1.ass_id=a1.ass_id
                            LEFT JOIN tbl_team t2 ON m.team2=t2.team_id
                            LEFT JOIN tbl_association a2 ON t2.ass_id=a2.ass_id
                            WHERE m.tourna_id='$tourna_id'
                            ORDER BY m.match_id DESC");

        if(mysqli_num_rows($sql) > 0){
            $status = true;
            while($row = mysqli_fetch_assoc($sql)){
                // Determine winner name
                $winner_name = '';
                if($row['winner']){
                    $winner_name = ($row['winner'] == $row['team1']) ? $row['teamA'] : $row['teamB'];
                }

                $data[] = [
                    'match_id'   => $row['match_id'],
                    'teamA'      => $row['teamA'],
                    'teamB'      => $row['teamB'],
                    'start_date' => $row['start_date'] ? date("F j, Y - g:i A", strtotime($row['start_date'])) : '',
                    'status'     => intval($row['status']),
                    'winner'     => $winner_name
                ];
            }
        }
    }
}


if($s == "get_players"){
    $match_id = $_COOKIE['match_id'] ?? null;
    if($match_id){
        $sql = $con->query("SELECT team1, team2 FROM tbl_matches WHERE match_id='$match_id'");
        $row_data = mysqli_fetch_assoc($sql);
        if($row_data){
            $teamA = [];
            $teamB = [];
            $team1 = $row_data['team1'] ?? null;
            $team2 = $row_data['team2'] ?? null;

            if($team1){
                $team1_sql = $con->query("SELECT tp.player_id, ta.jersey_number, tp.ingame, p.profile, p.fullname
                                          FROM tbl_team_players AS tp 
                                          LEFT JOIN tbl_tourna_application AS ta ON tp.app_id=ta.app_id 
                                          LEFT JOIN tbl_profile AS p ON ta.prof_id=p.prof_id 
                                          WHERE team_id='$team1'");
                while($row = mysqli_fetch_assoc($team1_sql)){
                    $teamA[] = $row;
                }
            }

            if($team2){
                $team2_sql = $con->query("SELECT tp.player_id, ta.jersey_number, tp.ingame, p.profile, p.fullname
                                          FROM tbl_team_players AS tp 
                                          LEFT JOIN tbl_tourna_application AS ta ON tp.app_id=ta.app_id 
                                          LEFT JOIN tbl_profile AS p ON ta.prof_id=p.prof_id 
                                          WHERE team_id='$team2'");
                while($row = mysqli_fetch_assoc($team2_sql)){
                    $teamB[] = $row;
                }
            }

            $data = [
                'teamId_A'=> $team1,
                'teamId_B'=> $team2,
                'teamA' => $teamA,
                'teamB'=> $teamB,
            ];
            $status = true;
        }
    }
}
if($s=="max_playing"){
    $match_id = $_COOKIE['match_id'];
    $sql =$con->query("SELECT gm.players FROM tbl_matches AS m LEFT JOIN tbl_tournament AS t ON m.tourna_id=t.tourna_id LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id WHERE m.match_id='$match_id'");
    if($row = mysqli_fetch_assoc($sql)){
        $status = true;
        $data = $row['players'];
    }
}
if($s=="scoreHistory"){
    $sql =$con->query("SELECT score_history FROM tbl_score_match WHERE score_id='$score_id'");
    if($row = mysqli_fetch_assoc($sql)){
        $status = true;
        $data = json_decode($row['score_history'],true);

        if ($data === null) {
            $data = [];
        }
    }
}
if($s=="fetch_set"){
    $match_id = $_COOKIE['match_id'];
    $sql =$con->query("SELECT SUM(winner='team1') AS team1_wins,SUM(winner='team2') AS team2_wins FROM tbl_score_match WHERE match_id='$match_id'");
    if($data = mysqli_fetch_assoc($sql)){
        $status = true;
    }
}
if($s=="get_assoc"){
    $sql = $con->query("SELECT name FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE team_id='$teamId'");
    if($data = mysqli_fetch_assoc($sql)){
        $data = $data['name'];
        $status = true;
    }
}
if($s=="match_status"){
    $sql = $con->query("SELECT status,winner FROM tbl_matches WHERE match_id='$id'");
    if($data = mysqli_fetch_assoc($sql)){
        if($data['status'] == 2){
            $win = $data['winner'];
            $sql = $con->query("SELECT name FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE t.team_id='$win'");
            $row = mysqli_fetch_assoc($sql);
            $data['winner'] = $row['name'];
        }
        $status = true;
    }
}
if($s=="logEntries"){
    $sql = $con->query("SELECT log_message FROM tbl_match_log  WHERE match_id='$id' ORDER BY log_id DESC");
    while($row = mysqli_fetch_assoc($sql)){
        $data[] = $row['log_message'];
        $status = true;
    }
}
if($s=="player_ingame"){
    $sql = $con->query("SELECT ingame FROM tbl_team_players WHERE player_id='$id'");
    if($data = mysqli_fetch_assoc($sql)){
        $data = $data['ingame'];
        $status = true;
    }
}

if($s=="score_audience"){
    $match_id = $_COOKIE['match_id'];

    // Get sets won
    $sql = $con->query("SELECT SUM(winner='team1') AS team1_wins, SUM(winner='team2') AS team2_wins 
                        FROM tbl_score_match 
                        WHERE match_id='$match_id'");
    $set = mysqli_fetch_assoc($sql);

    // Get latest score
    $sql = $con->query("SELECT set_quarter, team1, team2, winner FROM tbl_score_match 
                        WHERE match_id='$match_id' ORDER BY score_id DESC LIMIT 1");
    $score = mysqli_fetch_assoc($sql);

    // Get scoring type
    $tourna = $con->query("SELECT gm.scoring 
                           FROM tbl_matches AS m
                           LEFT JOIN tbl_tournament AS t ON m.tourna_id=t.tourna_id
                           LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id
                           WHERE m.match_id='$match_id'");
    $scoring = mysqli_fetch_assoc($tourna);

    // Check if match is concluded
    $match = $con->query("SELECT status, winner FROM tbl_matches WHERE match_id='$match_id'");
    $match_status = mysqli_fetch_assoc($match);

    $data = [
        "sets" => $set,
        "scores" => $score,
        "scoring" => $scoring['scoring'],
        "concluded" => ($match_status['status'] == 2),
        "winner" => $match_status['status'] == 2 ? $match_status['winner'] : null
    ];
    $status = true;
}


if($s == "ifConcluded"){
    $match_id = $_COOKIE['match_id'];
    $sql = $con->query("SELECT a.name FROM tbl_matches AS m LEFT JOIN tbl_team AS t ON m.winner=t.team_id LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE m.status='2' AND m.match_id='$match_id' LIMIT 1");
    if(mysqli_num_rows($sql) > 0){
        $data = mysqli_fetch_assoc($sql);
        $data = $data['name'];
        $status = true;
    }
}
if($s=="medals"){
    $ev_id = $_SESSION['ev_id'];

    $sql = $con->query("SELECT name, ass_id FROM tbl_association");
    while ($row = mysqli_fetch_assoc($sql)) {
        $association = [
            "name" => $row['name'],
            "medals" => [
                "gold" => 0,
                "silver" => 0,
                "bronze" => 0,
            ]
        ];

        $id = $row['ass_id'];

        $team = $con->query("
            SELECT * 
            FROM tbl_team t 
            LEFT JOIN tbl_tournament AS tn ON t.tourna_id = tn.tourna_id 
            WHERE ass_id = '$id' AND tn.ev_id = '$ev_id'
        ");

        while ($teamRow = mysqli_fetch_assoc($team)) {
            if($teamRow['place'] != null){
                if ($teamRow['place'] == 0) {
                    $association['medals']['gold']++;
                } elseif ($teamRow['place'] == 1) {
                    $association['medals']['silver']++;
                } elseif ($teamRow['place'] == 2) {
                    $association['medals']['bronze']++;
                }
            }
        }

        $data[] = $association;
        $status = true;
    }

}

if($s=="medals_by_assoc"){
    $ev_id = $_SESSION['ev_id'];

    $sql = $con->query("
            SELECT place
            FROM tbl_team t 
            LEFT JOIN tbl_tournament AS tn ON t.tourna_id = tn.tourna_id 
            WHERE ass_id = '$ass_id' AND tn.ev_id = '$ev_id'
        ");
    if($row = mysqli_fetch_assoc($sql)) {
        $medals = [
            "gold" => 0,
            "silver" => 0,
            "bronze" => 0,
        ];
        if($row['place'] != null){
            if ($row['place'] == 0) {
                $medals['gold']++;
            } elseif ($row['place'] == 1) {
                $medals['silver']++;
            } elseif ($row['place'] == 2) {
                $medals['bronze']++;
            }
        }
        

        $data = $medals;
        $status = true;
    }
}
if($s=="concluded"){
    $match_id = $_COOKIE['match_id'];
    $sql = $con->query("SELECT tbl_matches WHERE status='2' AND match_id='$match_id' LIMIT 1");
    if(mysqli_num_rows($sql) == 0){
        $status = true;
    }
}
if($s=="player_list"){
    $tourna = $_SESSION['tournament_code'];
    $ass = $_SESSION['association_id'];
    $query = $con->query("SELECT gm_cat_id FROM tbl_tournament AS t LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id WHERE tourna_id='$tourna'");
    $row = mysqli_fetch_assoc($query);
    if($row['gm_cat_id'] == 3){
        $sql = $con->query("SELECT p.fullname,p.prof_id FROM tbl_profile AS p LEFT JOIN tbl_association_players AS ap ON p.user_id=ap.user_id WHERE ap.ass_id='$ass'");
    }else{
        $cat = $row['gm_cat_id'];
        $sql = $con->query("SELECT p.fullname,p.prof_id FROM tbl_profile AS p LEFT JOIN tbl_association_players AS ap ON p.user_id=ap.user_id WHERE ap.ass_id='$ass' AND p.gender='$cat'");
    }

    while($row = mysqli_fetch_assoc($sql)){
        $prof_id = $row['prof_id'];

        $query = $con->query("SELECT prof_id FROM tbl_tourna_application WHERE tourna_id='$tourna' AND prof_id='$prof_id' LIMIT 1");
        if(mysqli_num_rows($query) == 0){
            $status = true;
            $data[] = $row;
        }
    }
}
if($s=="points"){
    $sql = $con->query("SELECT * FROM tbl_point_system WHERE point_id='$id'");
    if($row = mysqli_fetch_assoc($sql)) {
        $data = $row;
        $status = true;
    }
}
if($s=="scoring_points"){
    $sql = $con->query("SELECT point FROM tbl_point_system  WHERE game_id='$game_id' ORDER BY point ASC");
    $status = true;
    if(mysqli_num_rows($sql) > 0){
        while($row = mysqli_fetch_assoc($sql)){
            $data[] = intval($row['point']);
        }
    }else{
        $data[] = 1;
    }
}
echo json_encode(array("data"=>$data,"status" => $status));
?>