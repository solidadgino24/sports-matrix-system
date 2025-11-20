<?php
    include_once("../conn.php");
    session_start();
    $session_user_id = $_SESSION['session_user_id'];
    $id = $_SESSION['tournament_code'];
    $team_id = $_GET['id'];

    $sql = $con->query("SELECT s.name AS sport_name,gm.name,gm.scoring FROM tbl_tournament AS t
                    LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id 
                    LEFT JOIN tbl_sports AS s ON gm.sport_id=s.sport_id
                    WHERE t.tourna_id = '$id'");
    $tourna = mysqli_fetch_assoc($sql);

    $recent_match = $con->query("SELECT * FROM tbl_matches AS m WHERE (m.tourna_id ='$id' AND m.status='2' AND team1='$team_id') OR team2='$team_id' ORDER BY match_id DESC");

    $team = $con->query("SELECT t.place,t.team_id,a.name,a.img_logo,t.disqualify FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE team_id='$team_id'");
    $team = mysqli_fetch_assoc($team);

    $player = $con->query("SELECT COUNT(*) AS count FROM tbl_team_players WHERE team_id ='$team_id'");
    $player = mysqli_fetch_assoc($player);

    $record = $con->query("SELECT SUM(winner = $team_id) AS win, SUM(winner != $team_id AND winner IS NOT NULL) AS lose FROM tbl_matches WHERE team1 = $team_id OR team2 = $team_id AND tourna_id='$id'");
    $record = mysqli_fetch_assoc($record);
    if(isset($team['place'])){
        if($team['disqualify'] == 1){
            $place = "Disqualified!";
        }else{
            if($team['place']==0){
                $place = "Champion";
            }else{
                if($team['place']==1){
                    $place = "1st Place";
                }else if($team['place']==2){
                    $place = "2nd Place";
                }else if($team['place']==3){
                    $place = "3rd Place";
                }else if($team['place']==4){
                    $place = "4th Place";
                }else{
                    $place = $team['place']."th Place";
                }
            }
        }
    }else{
        $place = "Match not Started";
    }
?>
<div class="main">
    <div class="head">
        <h2><?php echo $tourna['name']." (".$tourna['sport_name'].")" ?></h2>
        <div>
            <button class="btn back_team">Back</button>
        </div>
    </div>
    <div class="body">
        <!-- Team Info -->
        <div class="team_data">
            <div class="team_header">
                <img src="data:image/png;base64,<?php echo $team['img_logo'] ?>" alt="">
                <div>
                    <h3><?php echo $team['name'] ?></h3>
                    <p><?php echo $player['count'] ?> Player(s)</p>
                    <span class="badge"><?php echo $place ?></span>
                </div>
            </div>
            <div class="team_record">
                <div class="stat win">Win <span><?php echo $record['win'] ?></span></div>
                <div class="stat lose">Lose <span><?php echo $record['lose'] ?></span></div>
            </div>
        </div>

        <!-- Recent Matches -->
        <div class="recent_match">
            <h3>Recent Matches</h3>
            <?php if($team['disqualify'] != 1){ ?>
            <ul>
            <?php 
                while($row = mysqli_fetch_assoc($recent_match)){
                    $team1 = mysqli_fetch_assoc($con->query("SELECT name,img_logo FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE team_id='".$row['team1']."'"));
                    $team2 = mysqli_fetch_assoc($con->query("SELECT name,img_logo FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE team_id='".$row['team2']."'"));

                    $match_id = $row['match_id'];
                    if($tourna['scoring'] == 1){
                        $score = mysqli_fetch_assoc($con->query("SELECT team1,team2 FROM tbl_score_match WHERE match_id='$match_id' ORDER BY score_id DESC"));
                    }else{
                        $score = mysqli_fetch_assoc($con->query("SELECT SUM(winner='team1') AS team1,SUM(winner='team2') AS team2 FROM tbl_score_match WHERE match_id='$match_id'"));
                    }
            ?>
                <li class="match_card">
                    <div class="match_meta">
                        <span>Start: <?php echo ($row['start_date'] ? date('M j, Y h:i A', strtotime($row['start_date'])) : "-") ?></span>
                        <span>End: <?php echo ($row['end_date'] ? date('M j, Y h:i A', strtotime($row['end_date'])) : "-") ?></span>
                    </div>
                    <div class="match_content">
                        <div class="team side left <?php echo ($row['team1']==$row['winner'])?'winner':'' ?>">
                            <img src="data:image/png;base64,<?php echo $team1['img_logo'] ?>" alt="">
                            <h4><?php echo $team1['name'] ?></h4>
                        </div>
                        <div class="scoreboard">
                            <h2><?php echo $score['team1'] ?> - <?php echo $score['team2'] ?></h2>
                            <p>VS</p>
                        </div>
                        <div class="team side right <?php echo ($row['team2']==$row['winner'])?'winner':'' ?>">
                            <img src="data:image/png;base64,<?php echo $team2['img_logo'] ?>" alt="">
                            <h4><?php echo $team2['name'] ?></h4>
                        </div>
                    </div>
                </li>
            <?php } ?>
            </ul>
            <?php } ?>
        </div>
    </div>
</div>

<style>
.main {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 12px;
}
.head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #eee;
    margin-bottom: 15px;
}
.head h2 {
    margin: 0;
    font-size: 22px;
    font-weight: bold;
    color: #222;
}
.head button {
    background: seagreen;
    color: #fff;
    padding: 6px 14px;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
}
.head button:hover {
    background: mediumseagreen;
}

/* Team Info */
.team_data {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    padding: 15px;
    margin-bottom: 20px;
}
.team_header {
    display: flex;
    align-items: center;
    gap: 15px;
}
.team_header img {
    width: 80px;
    height: 80px;
    object-fit: contain;
}
.team_header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: bold;
}
.team_header p {
    margin: 2px 0;
    color: #666;
}
.badge {
    background: #3498db;
    color: #fff;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 13px;
    margin-top: 4px;
    display: inline-block;
}
.team_record {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
}
.team_record .stat {
    font-size: 16px;
    font-weight: bold;
    padding: 8px 20px;
    border-radius: 8px;
    color: #fff;
}
.stat.win { background: seagreen; }
.stat.lose { background: crimson; }

/* Matches */
.recent_match h3 {
    margin: 0 0 10px;
    font-size: 18px;
}
.recent_match ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.match_card {
    background: #fff;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.match_meta {
    font-size: 13px;
    color: #888;
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}
.match_content {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.team.side {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}
.team img {
    width: 60px;
    height: 60px;
    object-fit: contain;
}
.team h4 {
    margin: 0;
    font-size: 16px;
}
.scoreboard {
    flex: 0.5;
    text-align: center;
}
.scoreboard h2 {
    margin: 0;
    font-size: 22px;
    font-weight: bold;
}
.scoreboard p {
    margin: 0;
    font-size: 14px;
    color: #888;
}

/* Highlight Winner */
.winner {
    background: rgba(46, 204, 113, 0.1);
    border-radius: 8px;
    padding: 6px;
}
</style>
