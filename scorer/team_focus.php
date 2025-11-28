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
        <h2><?php echo $tourna['name']."(".$tourna['sport_name'].")" ?></h2>
        <div>
            <button class="btn back_team">Back</button>
        </div>
    </div>
    <div class="body">
        <div class="record">
            <div class="team_data">
                    <div>
                        <img src="data:image/png;base64,<?php echo base64_encode($team['img_logo']) ?>" alt="">
                        <div>
                            <h3><?php echo $team['name'] ?></h3>
                            <p><?php echo $player['count'] ?> player's</p>
                        </div>
                    </div>
                    <h4>Record</h4>
                    <ul>
                        <li style='color: seagreen;font-weight:bold;'>Win: <span><?php echo $record['win'] ?></span></li>
                        <li style='color: red;font-weight:bold;'>Lose: <span><?php echo $record['lose'] ?></span></li>
                        <li style='color: blue;font-weight:bold;'>Running:  <?php echo $place ?></li>
                    </ul>
                </div>
        </div>
        <div class="recent_match">
            <?php if($team['disqualify'] != 1){ ?>
            <ul>
            <?php 
                while($row = mysqli_fetch_assoc($recent_match)){
                    $team1 = $row['team1'];
                    $team1 = $con->query("SELECT name,ass_desc,img_logo FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE team_id='$team1'");
                    $team1 = mysqli_fetch_assoc($team1);

                    $team2 = $row['team2'];
                    $team2 = $con->query("SELECT name,ass_desc,img_logo FROM tbl_team AS t LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id WHERE team_id='$team2'");
                    $team2 = mysqli_fetch_assoc($team2);

                    $match_id = $row['match_id'];
                    if($tourna['scoring'] == 1){
                        $sql_score = $con->query("SELECT team1,team2 FROM tbl_score_match WHERE match_id='$match_id' ORDER BY score_id DESC");
                    }else{
                        $sql_score = $con->query("SELECT SUM(winner='team1') AS team1,SUM(winner='team2') AS team2 FROM tbl_score_match WHERE match_id='$match_id'");
                    }

                    $score = mysqli_fetch_assoc($sql_score);
            ?>
                <li>
                    <div class="col-md-12">
                        <p class="ptag">Start: <span><?php echo ($row['start_date'] != null) ? date('M j, Y h:i A', strtotime($row['start_date'])) : "" ?> </span>End: <span><?php echo ($row['end_date'] != null) ? date('M j, Y h:i A', strtotime($row['end_date'])) : "" ?></span></p>
                        <section class="col-md-5 team_holder left">
                            <h3>
                                <img src="data:image/png;base64,<?php echo base64_encode($team1['img_logo']) ?>" alt="" width="100px"> <?php echo $team1['name'] ?>
                                <?php if($row['team1']==$row['winner']){ ?>
                                    <span class="win">Win</span>
                                <?php } ?>
                            </h3>
                        </section>
                        <section class="col-md-2">
                            <h1>VS</h1>
                            <h3><span><?php echo $score['team1'] ?></span> - <span><?php echo $score['team2'] ?></span></h3>
                        </section>
                        <section class="col-md-5 team_holder right">
                            <h3>
                                <img src="data:image/png;base64,<?php echo base64_encode($team2['img_logo']) ?>" alt="" width="100px"> <?php echo $team2['name'] ?>
                                <?php if($row['team2']==$row['winner']){ ?>
                                    <span class="win">Win</span>
                                <?php } ?>
                            </h3>
                        </section>
                    </div>
                    <div class="clearfix"></div>
                </li>
            <?php } ?>
            </ul>
            <?php } ?>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<style>
.head{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #272c33;
}
.head > div > button {
    border: 1px solid #272c33;
    background-color: coral;
    color: #fff;
    font-weight: bold;
}

tr > td > button{
    padding: 3px 10px;
    border: 1px solid #272c33;
    border-bottom: 4px solid #272c33;
    border-radius: 5px;
    margin-top: 10px;
}
tr > td > button:hover{
    padding-top:3px;
    color:grey;
    border-bottom: 1px solid rgb(66, 94, 133);
}
.recent_match > ul{
    list-style-type:none;
}
.ptag > span{
    color: #5d00ff;
    font-weight: bold;
}
.win{
    position: absolute;
    font-size: 16px;
    border: 1px solid green;
    width: 100px;
    background-color: darkseagreen;
    color: #fff;
    font-weight: bold;
    text-align: center;
    box-shadow: 1px 1px 1px 1px seagreen;
    border-radius: 5px;
}
.team_holder > h3{
    position: relative;
    overflow: hidden;
    display: flex;    
    align-items: center;
    gap: 20px;
}
.left > h3{
    flex-direction: row-reverse;
}
.left > h3 > .win{
    flex-direction: row-reverse;
    right: 0;
    bottom: 10px;
}
.right > h3 > .win{
    flex-direction: row-reverse;
    left: 0;
    bottom: 10px;
}
li > .col-md-12{
    box-shadow: 2px 2px 3px 2px #c2c0c0;
    border-radius: 10px;
    padding: 10px;
}
li > .col-md-12 > .col-md-2{
    text-align:center;
}
.recent_match > ul >li{
    padding:10px;
    margin-top:10px;
}
.team_data{
    padding: 15px;
    border-bottom:1px solid;
}
.team_data > div{
    display: flex;
    flex-direction: row;
    align-items: center;
    width: 100%;
    gap: 10px;
    justify-content: flex-start;
    margin-bottom: 10px;
}
.team_data > div > img{
    height: inherit;
    width: 100px;
}
.team_data > ul{
    list-style-type: disclosure-closed;
    margin-left: 30px;
}
</style>
<script>
    
</script>
