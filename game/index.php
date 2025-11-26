<?php
    include("../conn.php");
    session_start();
    $match_id = $_COOKIE['match_id'];
    $sql = $con->query("SELECT M.team1,M.team2,M.user_id,M.status,M.tourna_id,T.game_id FROM tbl_matches AS M LEFT JOIN tbl_tournament AS T ON M.tourna_id=T.tourna_id WHERE match_id='$match_id'");
    $row = mysqli_fetch_assoc($sql);

    if(!isset($_SESSION['session_user_id']) || $_SESSION['session_user_id'] != $row['user_id']){
        require_once("audience.php");
        die();
    }
    
    if($row['status'] == 2){
        echo "/Match Concluded!";
        die();
    }
    
    $score_id = $con->query("SELECT set_quarter,score_id,team1,team2 FROM tbl_score_match WHERE match_id='$match_id' ORDER BY score_id DESC");
    $score_id = mysqli_fetch_assoc($score_id);

    $set =$con->query("SELECT SUM(winner='team1') AS team1_wins,SUM(winner='team2') AS team2_wins FROM tbl_score_match WHERE match_id='$match_id' ORDER BY score_id DESC");
    $set = mysqli_fetch_assoc($set);

    $tourna = $row['tourna_id'];
    $tourna = $con->query("SELECT gm.name AS gamemode,s.name,s.img,gm.scoring,gm.sets FROM tbl_tournament AS t 
                            LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id 
                            LEFT JOIN tbl_sports AS s ON gm.sport_id=s.sport_id
                            WHERE tourna_id='$tourna'");
    $tourna = mysqli_fetch_assoc($tourna);

    $team1 = $row['team1'];
    $team1 = $con->query("SELECT name,ass_desc,img_logo FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE team_id='$team1'");
    $team1 = mysqli_fetch_assoc($team1);

    $team2 = $row['team2'];
    $team2 = $con->query("SELECT name,ass_desc,img_logo FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE team_id='$team2'");
    $team2 = mysqli_fetch_assoc($team2);

$display = "none";
$quarter = "";
$team_a_display = 0;
$team_b_display = 0;

if(isset($score_id) && $score_id !== null && isset($score_id['set_quarter']) && $score_id['set_quarter'] != null){
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
    
    // Check if in overtime with 0-0 score
    if ($quarter_num > $regulation_sets && intval($score_id['team1']) == 0 && intval($score_id['team2']) == 0) {
        // Get previous non-zero score from regulation quarters
        $prev_sql = $con->query("SELECT team1, team2 FROM tbl_score_match 
                                WHERE match_id='$match_id' 
                                AND (team1 > 0 OR team2 > 0)
                                ORDER BY score_id DESC LIMIT 1");
        if ($prev_row = mysqli_fetch_assoc($prev_sql)) {
            $team_a_display = intval($prev_row['team1']);
            $team_b_display = intval($prev_row['team2']);
        } else {
            $team_a_display = 0;
            $team_b_display = 0;
        }
    } else {
        // Display current score
        $team_a_display = intval($score_id['team1']);
        $team_b_display = intval($score_id['team2']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport"	content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="data:image/png;base64,<?php echo $tourna['img'] ?>"/>
    <title><?php echo $tourna['name'] ?></title>
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
    <h1>🏆 <?php echo $tourna['gamemode'] ?> 🏆</h1>
    <div class="dashboard">
        <div class="team" id="<?php echo $row['team1'] ?>">
            <h3><img src="data:image/png;base64,<?php echo base64_encode($team1['img_logo']) ?>" alt="" width="100px"> <?php echo $team1['name'] ?></h3>
            <div class="players" data-team="teamA"></div>
            <div class="bench"></div>
        </div>

        <div class="scoreboard">
            <h3>🏆 Scoreboard</h3>
            <div>
            <?php if($row['status'] == 0){ ?>
                <button id="startGame">🎮 Start Game</button>
            <?php }else{ ?>
                <button id="redoScore">🔄 Redo Score</button>
            <?php } ?>
            </div>
            <div class="score"><?php echo $team_a_display ?> - <?php echo $team_b_display ?></div>
            <div class="set-counter" style="display:<?php echo $display ?>">
    <?php if($tourna['scoring'] == 2){ ?>
        <h3>🏅 Sets</h3>
        <div class="set-score">
            <span id="teamA-set"><?php echo ($set['team1_wins'] != null) ? $set['team1_wins'] : 0; ?></span> - <span id="teamB-set"><?php echo ($set['team2_wins'] != null) ? $set['team2_wins'] : 0; ?></span>
        </div>
    <?php }else{ ?>
        <h3>🏅 <span id="quarter_load"><?php echo $quarter ?></span><?php echo (isset($score_id['set_quarter']) && intval($score_id['set_quarter']) <= intval($tourna['sets'])) ? "" : ""; ?></h3>
        <a href="#" id="quarter_next">Next Quarter</a>
    <?php } ?>
</div>
            <div class="logs">
                <strong>📜 Game Logs</strong>
                <div id="logEntries">
                    <?php
                        $log = $con->query("SELECT log_message FROM tbl_match_log WHERE match_id='$match_id' ORDER BY log_id DESC");
                        while($logs = mysqli_fetch_assoc($log)){
                            echo "<p>".$logs['log_message']."</p>";
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="team" id="<?php echo $row['team2'] ?>">
            <h3><img src="data:image/png;base64,<?php echo base64_encode($team2['img_logo']) ?>" alt="" width="100px"> <?php echo $team2['name'] ?></h3>
            <div class="players" data-team="teamB"></div>
            <div class="bench"></div>
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
    <div id="score_popup" style="display:none; position:fixed; top:0; left:0; height:100vh; width:100vw; backdrop-filter:blur(5px); display:flex; justify-content:center; align-items:center; z-index:1000;">
        <div style="background:#fff; padding:20px; border-radius:10px; min-width:300px; text-align:center;">
            <h2 style="color:#000;">A Player Scored!</h2>
            <p id="popup_player_name" style="color:#333;"></p>
            <div id="score_table"></div>
            <br>
            <button onclick="closeScorePopup()">Cancel</button>
        </div>
    </div>
    <script>
        let scoreHistory = [];
        let teamAScore = <?php echo $team_a_display ?>;
        let teamBScore = <?php echo $team_b_display ?>;
        let player = 0;
        let playing = [];
        let starts = <?php echo $row['status'] ?>;
        let scores_id = <?php echo (isset($score_id['score_id'])) ? $score_id['score_id']: 0 ?>;
        let scoring = <?php echo $tourna['scoring'] ?>;
        let regulation_sets = <?php echo intval($tourna['sets']) ?>;

        // Ensure modals are hidden on page load
        $(document).ready(function(){
            $("#score_popup").hide();
            $("#modal_notify").hide();
            window.currentScoringPlayer = null;
        });

        // Quarter label function
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

        $.get("../list.php?s=max_playing",function(res){
            if(res.status){
                player = res.data;
            }
        })

        $.post("../list.php?s=scoreHistory",{score_id:scores_id},function(res){
            if(res.status){
                scoreHistory = res.data;
            }
        })
        let draw = 0;
        $("#startGame").click(function(){
    if(check_game()){
        let element = $(this);
        $.post("../action.php?a=start_match",{draw:draw},function(res){
            if(res.status){
                $(".set-counter").show();

                <?php if(!isset($score_id['set_quarter'])){ ?>
                    $("#quarter_load").text("1st Quarter");
                <?php } ?>

                notify("The Game is started!","glyphicon glyphicon-exclamation-sign","",color="green")
                logAction(`The Game is started!`);
                element.parent().append(`<button id="redoScore">🔄 Redo Score</button>`);
                element.remove();
                starts=1;
                scores_id = res.message;
            }else{
                draw++;
                notify(res.message,"glyphicon glyphicon-exclamation-sign","",color="red")
                $.get("../list.php?s=concluded",function(res){
                    if(res.status){
                        logAction(`Game was Concluded`);
                        starts = 2;
                        scores_id = 0;
                        
                        setTimeout(() => {
                            // Reload the parent window (matchmaking.php) before closing this tab
                            if(window.opener && !window.opener.closed){
                                window.opener.location.reload();
                            }
                            // Close the current scoreboard tab
                            window.close();
                        }, 2000);
                    }
                })
            }
        })
    }else{
        notify("Line up the first players","glyphicon glyphicon-warning-sign",`Fix the players InGame`,color="red")     
    }
});

        // Add this after the quarter_next click handler
$("#quarter_next").click(function(){
    let scoreDummy = {
        'team1':teamAScore,
        'team2':teamBScore
    }
    $.post("../action.php?a=quarter",{score:scoreDummy,score_id:scores_id},function(res){
        console.log(res);
        if(res.status){
            if(res.message == true){
                handleGameConcluded();
            }else{
                scores_id = res.message.id;
                let quarter = res.message.quarter;
                let quarterLabel = getQuarterLabel(quarter);
                
                if(res.message.end){
                    $("#quarter_next").text("End Game");
                }else{
                    $("#quarter_next").text("Next Quarter");
                }
                
                notify(`${quarterLabel} Started!`,"glyphicon glyphicon-exclamation-sign",`Scores: ${teamAScore} - ${teamBScore}`,color="green")
                logAction(`${quarterLabel} Started!`);
                $("#quarter_load").text(quarterLabel);
                scoreHistory = [];
            }
        }
    })
});

// Add this new function to handle game conclusion
function handleGameConcluded() {
    notify(`Game is Concluded!`,"glyphicon glyphicon-exclamation-sign",`Scores: ${teamAScore} - ${teamBScore}`,color="green")
    logAction(`Game was Concluded`);
    starts = 2;
    scores_id = 0;
    
    // Show modal with proper button handling
    $("#modal_notify").slideDown(100);
    $("#modal_notify button").off("click").on("click", function(){
        closeModal();
        setTimeout(() => {
            // Reload the parent window (matchmaking.php) before closing this tab
            if(window.opener && !window.opener.closed){
                window.opener.location.reload();
            }
            // Close the current scoreboard tab
            window.close();
        }, 500);
    });
}
// Add periodic check for game conclusion (every 2 seconds)
let conclusionCheckInterval = setInterval(function(){
    if(starts == 1 && scores_id > 0){
        $.get("../list.php?s=concluded",function(res){
            if(res.status){
                clearInterval(conclusionCheckInterval);
                handleGameConcluded();
            }
        });
    }
}, 2000);

        function check_game(){
            const values = Object.values(playing);
            if (values.every(val => val === values[0]) && values.every(val => val == player)) {
                return true;
            }else{
                return false;
            }
        }

        function logAction(message) {
            let timestamp = new Date().toLocaleTimeString();
            let entry = `🕒 [${timestamp}] ${message}`;
            $.post("../action.php?a=game_log",{log_message:entry},function(res){
                if(res.status){
                    $("#logEntries").prepend(`<p>${entry}</p>`);
                }
            })
        }

        function updateScore() {
            $(".scoreboard .score").text(`${teamAScore} - ${teamBScore}`);
        }

        function addPlayer(teamId, imageUrl, jerseyNumber,Ingame,player_id,fullname) {
            const playerDiv = $(`<div class="player" player-id="${player_id}"></div>`)
                .append(`<img src="data:image/png;base64,${imageUrl}" alt="Player">`)
                .append(`<span>${jerseyNumber}</span>`)
                .append(`<p>${fullname}</p>`)
                .append('<button class="remove-player">❌</button>')
                .append('<button class="add-score-btn">➕ Score</button>')
                .hide();

            playerDiv.data('team', teamId);
            $(`#${teamId} .players`).append(playerDiv);

            if(Ingame==0){
                moveToBench(playerDiv, teamId)
            }else{
                playing[teamId]++;
                playerDiv.slideDown(300).fadeIn(300);
                attachRemoveClick(playerDiv, teamId);
                attachAddScoreClick(playerDiv, teamId);
            }
        }

        function moveToBench(playerDiv, teamId) {
            playerDiv.fadeOut(300, function() {
                $(this).slideUp(300, function() {
                    $(this).find("button").remove();
                    const addButton = $('<button class="add-player">➕</button>');
                    $(this).append(addButton).hide();
                    $(`#${teamId} .bench`).append(this);
                    $(this).slideDown(300).fadeIn(300);
                    attachAddClick($(this), teamId);
                });
            });
        }

        function moveToPlayingField(playerDiv, teamId) {
            if(playing[teamId] >= player){
                notify("Player is loaded!","glyphicon glyphicon-warning-sign",`Cannot add more player`,color="red");
            }else{
                $.post("../action.php?a=player_stats",{id:playerDiv.attr("player-id"),stats:1},function(res){
                    if(res.status){
                        playing[teamId]++;
                        playerDiv.fadeOut(300, function() {
                            $(this).slideUp(300, function() {
                                $(this).find("button").remove();
                                const removeButton = $('<button class="remove-player">❌</button>');
                                const addScoreButton = $('<button class="add-score-btn">➕ Score</button>');
                                $(this).append(removeButton).append(addScoreButton).hide();
                                $(`#${teamId} .players`).append(this);
                                $(this).slideDown(300).fadeIn(300);
                                attachRemoveClick($(this), teamId);
                                attachAddScoreClick($(this), teamId);
                                if(starts==1){
                                    logAction(`Moved [${playerDiv.find("span").text()}] ${playerDiv.find("p").text()}, back to play`);
                                }else{
                                    logAction(`Moved [${playerDiv.find("span").text()}] ${playerDiv.find("p").text()}, as starting player`);
                                }
                            });
                        });    
                    }
                })
            }
        }

        function attachRemoveClick(playerDiv, teamId) {
            playerDiv.find('.remove-player').off("click").on("click", function() {
                $.post("../action.php?a=player_stats",{id:playerDiv.attr("player-id"),stats:0},function(res){
                    if(res.status){
                        playing[teamId]--;
                        if(starts==1){
                            logAction(`Moved ${playerDiv.find("p").text()} to bench`);
                        }else{
                            logAction(`Removed ${playerDiv.find("p").text()} as starting player`);
                        }
                        moveToBench(playerDiv, teamId);
                    }
                });
            });
        }

        function attachAddScoreClick(playerDiv, teamId) {
                playerDiv.find('.add-score-btn').off("click").on("click", function() {
                    if(starts == 1){
                        if(check_game()){
                            scoreHistory.push({ teamA: teamAScore, teamB: teamBScore });
                            window.currentScoringPlayer = {
                                div: playerDiv,
                                team: teamId
                            };
                            $("#popup_player_name").text(
                                `[${playerDiv.find("span").text()}] ${playerDiv.find("p").text()}`
                            );
                            $("#score_popup").fadeIn(200);
                            $.post("../list.php?s=scoring_points",{game_id:<?= $row['game_id']?>},function(res){
                                if(res.status){
                                    let div = $("#score_table");
                                    let buttons = "";
                                    for(let i=0; i < res.data.length; i++){
                                        buttons += `<button class="choose-score" data-score="${res.data[i]}">${res.data[i]}Pts</button>`;
                                    }
                                    div.html(buttons);
                                }
                            })
                        }else{
                            notify("Line up the first players","glyphicon glyphicon-warning-sign",`Fix the players InGame`,color="red")     
                        }
                    }else{
                        let sentence = "Match not Started!";
                        if(starts == 2){
                            sentence = "Match Ended!";
                        }
                        notify(sentence,"glyphicon glyphicon-warning-sign",``,color="red")                   
                    }
            });
        }

        function attachAddClick(playerDiv, teamId) {
            playerDiv.find('.add-player').off("click").on("click", function() {
                moveToPlayingField(playerDiv, teamId);
            });
        }
        
        $(document).on("click","#redoScore", function() {
            if (scoreHistory.length > 0) {
                let lastScore = scoreHistory.pop();
                teamAScore = lastScore.teamA;
                teamBScore = lastScore.teamB;
                let scoreDummy = {
                    'team1':teamAScore,
                    'team2':teamBScore
                }
                $.post("../action.php?a=redo_score",{score:scoreDummy,score_id:scores_id,history:scoreHistory},function(res){
                    if(res.status){
                        updateScore();
                        logAction("Redid the last score change.");
                    }
                });
            } else {
                logAction("No score to redo.");
            }
        });

        $.get("../list.php?s=get_players",function(res){
            if(res.status){
                playing = {
                    [res.data.teamId_A]: 0,
                    [res.data.teamId_B]: 0
                };
                for(let i=0; i < res.data.teamB.length; i++){
                    let data = res.data.teamB[i];
                    addPlayer(res.data.teamId_B, data.profile, data.jersey_number, data.ingame, data.player_id, data.fullname);
                }
                for(let i=0; i < res.data.teamA.length; i++){
                    let data = res.data.teamA[i];
                    addPlayer(res.data.teamId_A, data.profile, data.jersey_number, data.ingame, data.player_id, data.fullname);
                }
            }
        });

        function notify(header,icon,msg,color="red"){
            $("#modal_notify").slideDown(100).find(".modal_head").text(header).css("color",color);
            $(".modal_icon").empty().html(`<span class='${icon} animated wobble'></span>`).css("color",color);
            $(".modal_msg").text(msg);
        }
        function closeModal(){
            $('#modal_notify').slideUp(100);
        }
        function closeScorePopup(){
            $("#score_popup").fadeOut(100);
            window.currentScoringPlayer = null;
        }

       $(document).on("click",".choose-score", function(){
    let chosenScore = parseInt($(this).data("score"));
    let playerInfo = window.currentScoringPlayer;

    if(playerInfo){
        let playerDiv = playerInfo.div;
        let teamId = playerInfo.team;

        scoreHistory.push({ teamA: teamAScore, teamB: teamBScore });

        let whatTeam;
        if (teamId === "<?php echo $row['team1'] ?>") {
            teamAScore += chosenScore;
            whatTeam = "team1";
        } else {
            teamBScore += chosenScore;
            whatTeam = "team2";
        }
        
        // Send only the points added (chosenScore), not the total score
        $.post("../action.php?a=scoring",{
            score: chosenScore,
            team: whatTeam,
            score_id: scores_id,
            history: scoreHistory
        },function(res){
            if(res.status){
                updateScore();
                $.post("../list.php?s=get_assoc",{teamId:teamId},function(res){
                    if(res.status){
                        logAction(`[${playerDiv.find("span").text()}] ${playerDiv.find("p").text()}, Scored ${chosenScore} point for ${res.data}`);
                    }
                });
                if(scoring==2){
                    $.post("../action.php?a=chk",{
                        score: (whatTeam === "team1") ? teamAScore : teamBScore,
                        team: whatTeam,
                        score_id: scores_id
                    },function(res){
                        if(res.status){
                            if(res.message.status == true){
                                handleGameConcluded();
                            }else{
                                logAction(`New set Started!`);
                                scores_id = res.message.id;
                                scoreHistory = [];
                                teamAScore = 0;
                                teamBScore = 0;
                                updateScore();
                                $.get("../list.php?s=fetch_set",function(res){
                                    if(res.status){
                                        $("#teamA-set").text(res.data.team1_wins);
                                        $("#teamB-set").text(res.data.team2_wins);
                                        notify("New set Started!","glyphicon glyphicon-exclamation-sign",`${res.data.team1_wins} - ${res.data.team2_wins}`,color="green")
                                    }
                                });
                            }
                        }
                    })
                }
            }
        });
    }
    closeScorePopup();
});
    </script>
</body>
</html>
