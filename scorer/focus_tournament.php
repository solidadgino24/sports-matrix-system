<?php
include_once("../conn.php");
session_start();

$session_user_id = $_SESSION['session_user_id'];

$id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['tournament_code'];
$_SESSION['tournament_code'] = $id;

// Get tournament info
$sql = $con->query("
    SELECT s.name AS sport_name, gm.name AS game_mode, t.status
    FROM tbl_tournament AS t
    LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id 
    LEFT JOIN tbl_sports AS s ON gm.sport_id=s.sport_id
    WHERE t.tourna_id='$id'
");
$tournament = mysqli_fetch_assoc($sql);

// Fetch all teams
$allTeams = [];
$teams_sql = $con->query("
    SELECT t.team_id, t.disqualify, a.name, a.img_logo
    FROM tbl_team AS t
    LEFT JOIN tbl_association AS a ON t.ass_id=a.ass_id
    WHERE t.tourna_id='$id'
");

while ($team = mysqli_fetch_assoc($teams_sql)) {
    $teamId = $team['team_id'];

    // Team logo
    $team_logo = !empty($team['img_logo']) ? base64_encode($team['img_logo']) : base64_encode(file_get_contents("../assets/default-logo.png"));

    // Count players
    $player_sql = $con->query("SELECT COUNT(*) AS count FROM tbl_team_players WHERE team_id='$teamId'");
    $player = mysqli_fetch_assoc($player_sql);

    // Win/Loss record
    $record_sql = $con->query("
        SELECT 
            SUM(winner = $teamId) AS win,
            SUM(winner != $teamId AND winner IS NOT NULL) AS lose
        FROM tbl_matches
        WHERE (team1 = $teamId OR team2 = $teamId) AND tourna_id='$id'
    ");
    $record = mysqli_fetch_assoc($record_sql);

    $allTeams[] = [
        'team_id' => $teamId,
        'name' => $team['name'],
        'img_logo' => $team_logo,
        'players' => $player['count'],
        'win' => (int)$record['win'],
        'lose' => (int)$record['lose'],
        'disqualify' => $team['disqualify']
    ];
}

// Sort teams by wins descending, then losses ascending
usort($allTeams, function($a, $b) {
    if ($b['win'] == $a['win']) return $a['lose'] <=> $b['lose'];
    return $b['win'] <=> $a['win'];
});

// Assign current standing / place
$rank = 1;
foreach ($allTeams as &$team) {
    if ($team['disqualify']) {
        $team['place_text'] = "<span style='color:red;'>Disqualified!</span>";
    } elseif ($team['win'] + $team['lose'] == 0) {
        // Team hasn't played yet
        $team['place_text'] = "<span style='color:gray;'>Match not Started</span>";
    } else {
        // Tournament ended => show final rankings
        if ($tournament['status'] == 2) { // ended
            if ($rank === 1) {
                $team['place_text'] = "<span style='color:green;font-weight:bold;'>Champion</span>";
            } else {
                $suffix = "th";
                if (!in_array(($rank % 100), [11,12,13])) {
                    switch ($rank % 10) {
                        case 1: $suffix="st"; break;
                        case 2: $suffix="nd"; break;
                        case 3: $suffix="rd"; break;
                    }
                }
                $team['place_text'] = "<span style='color:blue;'>{$rank}{$suffix} Place</span>";
            }
            $rank++;
        } else {
            // Tournament ongoing => show current standing
            if ($rank === 1) {
                $team['place_text'] = "<span style='color:green;font-weight:bold;'>1st Place</span>";
            } else {
                $suffix = "th";
                if (!in_array(($rank % 100), [11,12,13])) {
                    switch ($rank % 10) {
                        case 1: $suffix="st"; break;
                        case 2: $suffix="nd"; break;
                        case 3: $suffix="rd"; break;
                    }
                }
                $team['place_text'] = "<span style='color:blue;'>{$rank}{$suffix} Place</span>";
            }
            $rank++;
        }
    }
}
unset($team);
?>


<div class="main">
    <div class="head">
        <h2><?php echo $tournament['game_mode']." (".$tournament['sport_name'].")"; ?></h2>
        <div>
            <button class="btn matchmaking_btn">matchmaking</button>
        </div>
    </div>
    <div class="body">
        <div class="team_list">
            <?php foreach ($allTeams as $team) { ?>
            <div class="team_holder team_btn" title="Association: <?php echo $team['name'] ?>" team_id="<?php echo $team['team_id'] ?>">
                <div>
                    <img src="data:image/png;base64,<?php echo $team['img_logo'] ?>" alt="">
                    <div>
                        <h3><?php echo $team['name'] ?></h3>
                        <p><?php echo $team['players'] ?> player's</p>
                    </div>
                </div>
                <h4>Record</h4>
                <ul>
                    <li style='color: seagreen;font-weight:bold;'>Win: <span><?php echo $team['win'] ?></span></li>
                    <li style='color: red;font-weight:bold;'>Lose: <span><?php echo $team['lose'] ?></span></li>
                    <li style='color: blue;font-weight:bold;'>Running: <?php echo $team['place_text'] ?></li>
                </ul>
            </div>
            <?php } ?>
        </div>
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
    background-color: seagreen;
    color: #fff;
    font-weight: bold;
}
.team_list{
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: space-evenly;
    height: 78vh;
    overflow: auto;
    align-content: flex-start;
}
.team_list > div{
    width: 24%;
    border: 1px solid;
    display: flex;
    flex-direction: column;
    border-radius: 10px;
    padding: 8px;
    margin-top: 10px;
    flex-wrap: wrap;
}
.team_holder > div{
    display: flex;
    flex-direction: row;
    align-items: center;
    width: 100%;
    justify-content: space-evenly;
    margin-bottom:10px;
}
.team_holder > div > img{
    height: inherit;
    width: 100px;
}
.team_holder{
    cursor: pointer;
}
.team_holder > ul{
    list-style-type: disclosure-closed;
    margin-left: 30px;
}
</style>
<script>
$(".table").dataTable();
</script>
