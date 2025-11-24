<?php
// Fetch match info
$sql = $con->query("SELECT team1,team2,user_id,status,tourna_id FROM tbl_matches WHERE match_id='$match_id'");
$row = mysqli_fetch_assoc($sql);

// Fetch score info
$score_id = $con->query("SELECT set_quarter,score_id,team1,team2 FROM tbl_score_match WHERE match_id='$match_id' ORDER BY score_id DESC");
$score_id = mysqli_fetch_assoc($score_id);

// Fetch sets info
$set = $con->query("SELECT SUM(winner='team1') AS team1_wins,SUM(winner='team2') AS team2_wins FROM tbl_score_match WHERE match_id='$match_id'");
$set = mysqli_fetch_assoc($set);

// Fetch tournament info
$tourna_id = $row['tourna_id'];
$tourna = $con->query("SELECT gm.name AS gamemode,s.name,s.img,gm.scoring,gm.sets FROM tbl_tournament AS t 
                        LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id 
                        LEFT JOIN tbl_sports AS s ON gm.sport_id=s.sport_id
                        WHERE tourna_id='$tourna_id'");
$tourna = mysqli_fetch_assoc($tourna);

// Tournament image fix
$tourna_img = !empty($tourna['img']) ? base64_encode($tourna['img']) : file_get_contents("../assets/default-logo.png");

// Fetch team1 info
$team1id = $row['team1'];
$team1 = $con->query("SELECT name,ass_desc,img_logo FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE team_id='$team1id'");
$team1 = mysqli_fetch_assoc($team1);
$team1_logo = !empty($team1['img_logo']) ? base64_encode($team1['img_logo']) : base64_encode(file_get_contents("../assets/default-logo.png"));

// Fetch team2 info
$team2id = $row['team2'];
$team2 = $con->query("SELECT name,ass_desc,img_logo FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE team_id='$team2id'");
$team2 = mysqli_fetch_assoc($team2);
$team2_logo = !empty($team2['img_logo']) ? base64_encode($team2['img_logo']) : base64_encode(file_get_contents("../assets/default-logo.png"));

// Display quarter if score exists
$display = "none";
$quarter = "";
$team_a_display = 0;
$team_b_display = 0;

if(isset($score_id['set_quarter']) && $score_id['set_quarter'] != null){
    $display = "block";
    $quarter_num = intval($score_id['set_quarter']);
    $regulation_sets = intval($tourna['sets']);
    
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
    
    $quarter = $getQuarterLabel($quarter_num, $regulation_sets);
    
    // Handle overtime scoring logic
    if ($quarter_num > $regulation_sets) {
        $team_a_display = intval($score_id['team1']);
        $team_b_display = intval($score_id['team2']);
        
        // If current OT scores are 0-0, get the last non-zero score
        if ($team_a_display == 0 && $team_b_display == 0) {
            $prev_sql = $con->query("SELECT team1, team2 FROM tbl_score_match 
                                    WHERE match_id='$match_id' 
                                    AND (team1 > 0 OR team2 > 0)
                                    ORDER BY score_id DESC LIMIT 1");
            if ($prev_row = mysqli_fetch_assoc($prev_sql)) {
                $team_a_display = intval($prev_row['team1']);
                $team_b_display = intval($prev_row['team2']);
            }
        } else {
            // OT already has scores, add them to the baseline
            $baseline_sql = $con->query("SELECT team1, team2 FROM tbl_score_match 
                                       WHERE match_id='$match_id' 
                                       AND (team1 > 0 OR team2 > 0)
                                       ORDER BY score_id DESC LIMIT 1");
            $baseline = mysqli_fetch_assoc($baseline_sql);
            
            if ($baseline) {
                $baseline_team1 = intval($baseline['team1']);
                $baseline_team2 = intval($baseline['team2']);
                
                // Get first score of current OT quarter
                $ot_start_sql = $con->query("SELECT team1, team2 FROM tbl_score_match 
                                           WHERE match_id='$match_id' 
                                           AND set_quarter = '$quarter_num'
                                           ORDER BY score_id ASC LIMIT 1");
                if ($ot_start = mysqli_fetch_assoc($ot_start_sql)) {
                    $ot_start_team1 = intval($ot_start['team1']);
                    $ot_start_team2 = intval($ot_start['team2']);
                    
                    // If OT started with 0-0, add OT scores to baseline
                    if ($ot_start_team1 == 0 && $ot_start_team2 == 0) {
                        $team_a_display = $baseline_team1 + $team_a_display;
                        $team_b_display = $baseline_team2 + $team_b_display;
                    }
                }
            }
        }
    } else {
        // Regulation quarters - display current score
        $team_a_display = intval($score_id['team1']);
        $team_b_display = intval($score_id['team2']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="data:image/png;base64,<?php echo $tourna_img ?>"/>
    <title><?php echo htmlspecialchars($tourna['name']) ?></title>
    <script src="../js/jquery.js"></script>
    <link href="../css/animate.css" rel="stylesheet">
    <link href="../css/bootstrap.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #121212;
            color: white;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        h1 {
            font-size: 2.5em;
            margin-top: 20px;
            color: #ffffff;
        }
        .dashboard {
            display: flex;
            justify-content: space-between;
            padding: 30px;
            margin-top: 20px;
        }
        .team {
            width: 30%;
            padding: 20px;
            background: #1e1e1e;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }
        .team h3 {
            font-size: 1.8em;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .players, .bench {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 10px;
            min-height: 180px;
            background: #333333;
            border-radius: 10px;
            margin-top: 15px;
            transition: 0.5s ease-in-out;
        }
        .player {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 150px;
            margin: 10px;
            padding: 10px;
            background: #444444;
            border-radius: 10px;
            transition: 0.3s ease-in-out;
        }
        .player img {
            margin-top:50px;
            width: 60px;
            height: 60px;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        .player .remove-player,
        .player .add-score-btn {
            font-size: 11px;
            padding: 5px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .player .remove-player, .player .add-player {
            font-size: 11px;
            background: #ff1493;
            color: white;
            position: absolute;
            top: 5px;
            right: 5px;
        }
        .player .remove-player:hover,.player .add-player:hover {
            background: #ff6a00;
        }
        .player .add-score-btn {
            background: #4CAF50;
            color: white;
            position: absolute;
            top: 5px;
            left:0;
            width: 60%;
            padding: 5px;
            border-radius: 5px;
            border: none;
        }
        .player .add-score-btn:hover {
            background: #45a049;
        }
        .scoreboard {
            width: 30%;
            background: #1e1e1e;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .scoreboard h3 {
            font-size: 2em;
            color: #ffffff;
        }
        .scoreboard .score {
            font-size: 3em;
            color: #ff1493;
            margin-top: 10px;
        }
        .logs {
            margin-top: 15px;
            background: #333333;
            padding: 10px;
            border-radius: 10px;
            height: 500px;
            overflow-y: auto;
        }
        .logs p {
            font-size: 14px;
            color: white;
            margin: 5px 0;
        }
        button {
            margin: 5px;
            padding: 10px;
            font-size: 1em;
            border-radius: 5px;
            border: none;
            background-color: #ff1493;
            color: white;
        }
        .set-counter {
            margin-top: 15px;
            background: #222;
            padding: 10px;
            border-radius: 10px;
        }
        .set-score {
            font-size: 2em;
            color: #ff1493;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .team > h3{
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #quarter_next{
            color: white;
            border: 1px solid #fff;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            box-shadow:1px 1px 1px 1px grey;
        }
        #quarter_next:hover{
            color: #9a9a9a;
            border: 1px solid;
            box-shadow:1px 1px 1px 1px transparent;
        }
        body {
            scrollbar-width: thin;
            scrollbar-color: #222222 black;
        }
        .player > p{
            max-width: 100px;
            line-break: anywhere;
        }
        #modal_notify{
            height: 100vh;
            width: 100vw;
            position: fixed;
            top: 0;
            left: 0;
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .modal-content{
            width: 40vw;
            height: 50vh;
            background-color: #fff;
            border-radius: 10px;
            min-height: 400px;
            align-content: center;
        }
        .modal-content > .modal_head{
            color:green;
        }
        .modal-content > .modal_icon{
            margin-top:30px;
            color:green;
            font-size:64px;
        }
        .modal-content > .modal_msg{
            color:black;
            font-size:18px;
        }
        @media (max-width: 992px) {
            .dashboard {
                flex-direction: column;
                padding: 15px;
            }
            .team, .scoreboard {
                width: 100%;
                margin-bottom: 20px;
            }
            .team h3 img {
                width: 60px;
                margin-right: 10px;
            }
            .player {
                width: 80px;
                height: 130px;
            }
            .player img {
                width: 50px;
                height: 50px;
            }
            .scoreboard .score {
                font-size: 2em;
            }
            .logs {
                height: auto;
                max-height: 300px;
            }
            .modal-content {
                width: 90vw;
                height: auto;
                padding: 20px;
            }
        }
        @media (max-width: 576px) {
            h1 {
                font-size: 1.8em;
            }
            .player p {
                font-size: 12px;
            }
            button {
                font-size: 0.9em;
                padding: 8px;
            }
            .scoreboard h3, .set-counter h3 {
                font-size: 1.5em;
            }
            .scoreboard .score {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <h1>🏆 <?php echo htmlspecialchars($tourna['gamemode']) ?> 🏆</h1>
    <div class="dashboard">
        <div class="team" id="<?php echo $team1id ?>">
            <h3>
                <img src="data:image/png;base64,<?php echo $team1_logo ?>" alt="" width="100px"> 
                <?php echo htmlspecialchars($team1['name']) ?>
            </h3>
            <div class="players" data-team="teamA"></div>
<div class="bench">
    <?php 
    $sql = $con->query("SELECT player_id, pr.profile, pr.fullname, ap.jersey_number 
                        FROM tbl_team_players p 
                        LEFT JOIN tbl_tourna_application AS ap ON p.app_id=ap.app_id 
                        LEFT JOIN tbl_profile AS pr ON ap.prof_id=pr.prof_id 
                        WHERE p.team_id='$team1id'");
    while($player = mysqli_fetch_assoc($sql)){
        // Match the encoding from index.php
        $player_img = '';
        if (!empty($player['profile'])) {
            // Check if it's already base64 encoded string or binary data
            if (preg_match('/^[a-zA-Z0-9+\/]*={0,2}$/', $player['profile']) && strlen($player['profile']) % 4 == 0) {
                // It's already base64
                $player_img = $player['profile'];
            } else {
                // It's binary data, encode it
                $player_img = base64_encode($player['profile']);
            }
        } else {
            // Fallback to default image
            if (file_exists("../icons/ico.png")) {
                $default_img = file_get_contents("../icons/ico.png");
                $player_img = base64_encode($default_img);
            }
        }
    ?>
    <div class="player" player-id="<?php echo $player['player_id'] ?>">
        <img src="data:image/png;base64,<?php echo $player_img ?>" alt="">
        <span><?php echo htmlspecialchars($player['jersey_number']) ?></span>
        <p><?php echo htmlspecialchars($player['fullname']) ?></p>
    </div>
    <?php } ?>
</div>
        </div>

        <div class="scoreboard">
            <h3>🏆 Scoreboard</h3>
            <div class="score"><?php echo $team_a_display ?> - <?php echo $team_b_display ?></div>
            <div class="set-counter" style="display:<?php echo $display ?>">
                <?php if($tourna['scoring'] == 2){ ?>
                    <h3>🏅 Sets</h3>
                    <div class="set-score">
                        <span id="teamA-set"><?php echo $set['team1_wins'] ?? 0; ?></span> - 
                        <span id="teamB-set"><?php echo $set['team2_wins'] ?? 0; ?></span>
                    </div>
                <?php }else{ ?>
                    <h3>🏅 <span id="quarter_load"><?php echo $quarter ?></span></h3>
                <?php } ?>
            </div>
            <div class="logs">
                <strong>📜 Game Logs</strong>
                <div id="logEntries"></div>
            </div>
        </div>

        <div class="team" id="<?php echo $team2id ?>">
            <h3>
                <img src="data:image/png;base64,<?php echo $team2_logo ?>" alt="" width="100px"> 
                <?php echo htmlspecialchars($team2['name']) ?>
            </h3>
            <div class="players" data-team="teamB"></div>

<div class="bench">
    <?php 
    $sql = $con->query("SELECT player_id, pr.profile, pr.fullname, ap.jersey_number 
                        FROM tbl_team_players p 
                        LEFT JOIN tbl_tourna_application AS ap ON p.app_id=ap.app_id 
                        LEFT JOIN tbl_profile AS pr ON ap.prof_id=pr.prof_id 
                        WHERE p.team_id='$team2id'");
    while($player = mysqli_fetch_assoc($sql)){
        // Match the encoding from index.php
        $player_img = '';
        if (!empty($player['profile'])) {
            // Check if it's already base64 encoded string or binary data
            if (preg_match('/^[a-zA-Z0-9+\/]*={0,2}$/', $player['profile']) && strlen($player['profile']) % 4 == 0) {
                // It's already base64
                $player_img = $player['profile'];
            } else {
                // It's binary data, encode it
                $player_img = base64_encode($player['profile']);
            }
        } else {
            // Fallback to default image
            if (file_exists("../icons/ico.png")) {
                $default_img = file_get_contents("../icons/ico.png");
                $player_img = base64_encode($default_img);
            }
        }
    ?>
    <div class="player" player-id="<?php echo $player['player_id'] ?>">
        <img src="data:image/png;base64,<?php echo $player_img ?>" alt="">
        <span><?php echo htmlspecialchars($player['jersey_number']) ?></span>
        <p><?php echo htmlspecialchars($player['fullname']) ?></p>
    </div>
    <?php } ?>
</div>
        </div>
    </div>

    <div id="modal_notify" style="display:none">
        <div class="modal-content">
            <h1 class="modal_head"></h1>
            <span class="modal_icon"></span>
            <p class="modal_msg"></p>
            <div class="btns">
                <button onclick="closeModal()">Okay</button>
            </div>
        </div>
    </div>
<script>
$(document).ready(function(){
    let match_id = <?php echo $match_id ?>;
    let regulation_sets = <?php echo intval($tourna['sets']) ?>;
    let lastTeam1Score = <?php echo $team_a_display ?>;
    let lastTeam2Score = <?php echo $team_b_display ?>;

    // Helper function to display quarter/OT label
    function getQuarterLabel(quarter_num) {
    if (quarter_num <= regulation_sets) {
        if (quarter_num == 1) return "1st Quarter";
        else if (quarter_num == 2) return "2nd Quarter";
        else if (quarter_num == 3) return "3rd Quarter";
        else return "4th Quarter";
    } else {
        let ot_num = quarter_num - regulation_sets;
        return (ot_num === 1) ? "OT" : "OT" + ot_num;
    }
}
    function getlogs(){
        $.post("../list.php?s=logEntries",{id:match_id},function(res){
            if(res.status){
                let p = '';
                for (let log of res.data) p += `<p>${log}</p>`;
                $("#logEntries").html(p);
            }
        });

        $.get("../list.php?s=score_audience",function(res){
    if(res.status){
        let team1Score = parseInt(res.data.scores.team1);
        let team2Score = parseInt(res.data.scores.team2);
        let quarterNum = parseInt(res.data.scores.set_quarter);
        
        // Update displayed scores
        $(".score").text(`${team1Score} - ${team2Score}`);
        
        // Update quarter label
        if(res.data.scoring == 1){
            let quarterLabel = getQuarterLabel(quarterNum);
            $("#quarter_load").text(quarterLabel);
        }else{
            $(".set-score #teamA-set").text(res.data.sets.team1_wins ?? 0);
            $(".set-score #teamB-set").text(res.data.sets.team2_wins ?? 0);
        }
        
        // Update last known scores
        lastTeam1Score = team1Score;
        lastTeam2Score = team2Score;
    }
});

        $.get("../list.php?s=ifConcluded",function(res){
            if(res.status){
                notify(`Game was Concluded!`,"glyphicon glyphicon-exclamation-sign",`Winner: ${res.data}`,"green");
                clearInterval(refresh);
            }
        });
    }

    function playerIngame(){
        $(".player").each(function(){
            let me = $(this);
            let id = me.attr("player-id");
            $.post("../list.php?s=player_ingame",{id:id},function(res){
                if(res.status){
                    if(res.data == 1 && me.parent(".players").length == 0){
                        me.parent().parent().find(".players").append(me);
                    }else if(res.data == 0 && me.parent(".bench").length == 0){
                        me.parent().parent().find(".bench").append(me);
                    }
                }
            });
        });
    }

    getlogs();
    playerIngame();
    let refresh = setInterval(()=>{ getlogs(); playerIngame(); }, 1500);
});

function notify(header,icon,msg,color="red"){
    $("#modal_notify").slideDown(100).find(".modal_head").text(header).css("color",color);
    $(".modal_icon").empty().html(`<span class='${icon} animated wobble'></span>`).css("color",color);
    $(".modal_msg").text(msg);
    $(".btns > button").focus();
}

function closeModal(){ 
    $('#modal_notify').slideUp(100); 
}
</script>
</body>
</html>
