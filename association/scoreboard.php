<?php
    include_once("../conn.php");
    session_start();

    $session_user_id = $_SESSION['session_user_id'];

    $id = (isset($_GET['id']))? $_GET['id'] : $_SESSION['tournament_code'];
    $_SESSION['tournament_code'] = $id;
    $sql = $con->query("SELECT s.name AS sport_name,gm.name FROM tbl_tournament AS t
                    LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id 
                    LEFT JOIN tbl_sports AS s ON gm.sport_id=s.sport_id
                    WHERE t.tourna_id = '$id'");
                    
    $row = mysqli_fetch_assoc($sql);

    $sql = $con->query("SELECT t.place,t.team_id,a.name,a.img_logo,t.disqualify 
                        FROM tbl_team AS t 
                        LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id 
                        WHERE tourna_id='$id'");
?>
<div class="main">
    <div class="head">
        <h2><?php echo $row['name']." (".$row['sport_name'].")" ?></h2>
    </div>
    <div class="body">
        <div class="team_list">
        <?php 
            while($team = mysqli_fetch_assoc($sql)){
                $teamId = $team['team_id'];
                $player = $con->query("SELECT COUNT(*) AS count FROM tbl_team_players WHERE team_id ='$teamId'");
                $player = mysqli_fetch_assoc($player);

                $record = $con->query("SELECT SUM(winner = $teamId) AS win, SUM(winner != $teamId AND winner IS NOT NULL) AS lose 
                                        FROM tbl_matches 
                                        WHERE (team1 = $teamId OR team2 = $teamId) AND tourna_id='$id'");
                $record = mysqli_fetch_assoc($record);

                // Placement & Status
                if(isset($team['place'])){
                    if($team['disqualify'] == 1){
                        $place = "<span class='badge badge-disqualified'>Disqualified</span>";
                    }else{
                        if($team['place']==0){
                            $place = "<span class='badge badge-champion'>Champion</span>";
                        }else if($team['place']==1){
                            $place = "<span class='badge badge-gold'>1st Place</span>";
                        }else if($team['place']==2){
                            $place = "<span class='badge badge-silver'>2nd Place</span>";
                        }else if($team['place']==3){
                            $place = "<span class='badge badge-bronze'>3rd Place</span>";
                        }else{
                            $place = "<span class='badge badge-normal'>".$team['place']."th Place</span>";
                        }
                    }
                }else{
                    $place = "<span class='badge badge-pending'>Match not Started</span>";
                }
        ?>
            <div class="team_holder team_btn" title="Association: <?php echo $team['name'] ?>" team_id="<?php echo $team['team_id'] ?>">
                <div class="team_header">
                    <img src="data:image/png;base64,<?php echo $team['img_logo'] ?>" alt="">
                    <div>
                        <h3><?php echo $team['name'] ?></h3>
                        <p><?php echo $player['count'] ?> Player(s)</p>
                    </div>
                </div>
                <div class="team_stats">
                    <h4>Performance</h4>
                    <ul>
                        <li class="win">Win: <span><?php echo $record['win'] ?></span></li>
                        <li class="lose">Lose: <span><?php echo $record['lose'] ?></span></li>
                        <li class="status"><?php echo $place ?></li>
                    </ul>
                </div>
            </div>
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
    align-items: center;
    justify-content: space-between;
    padding-bottom: 10px;
    margin-bottom: 15px;
    border-bottom: 3px solid #eee;
}
.head h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #222;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.team_list {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
    height: 78vh;
    overflow-y: auto;
    padding-right: 10px;
}
.team_holder {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    width: 280px;
    padding: 15px;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}
.team_holder:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}
.team_header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}
.team_header img {
    width: 70px;
    height: 70px;
    object-fit: contain;
    border-radius: 8px;
    background: #f8f8f8;
}
.team_header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}
.team_header p {
    margin: 2px 0 0;
    font-size: 14px;
    color: #777;
}
.team_stats h4 {
    margin: 0 0 6px;
    font-size: 15px;
    font-weight: bold;
    color: #444;
    border-bottom: 1px solid #eee;
    padding-bottom: 3px;
}
.team_stats ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.team_stats li {
    font-size: 14px;
    margin: 6px 0;
    font-weight: 500;
}
.team_stats li.win {
    color: seagreen;
}
.team_stats li.lose {
    color: crimson;
}
.team_stats li.status {
    color: #222;
    font-weight: bold;
}

/* Badges for placement */
.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: bold;
    color: #fff;
}
.badge-champion { background: linear-gradient(90deg,#f39c12,#f1c40f); color: #000; }
.badge-gold { background: #ffd700; color: #000; }
.badge-silver { background: #c0c0c0; color: #000; }
.badge-bronze { background: #cd7f32; }
.badge-disqualified { background: #e74c3c; }
.badge-normal { background: #3498db; }
.badge-pending { background: #95a5a6; }
</style>
