<?php
include_once("../conn.php");
session_start();

$session_user_id = $_SESSION['session_user_id'];

$id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['tournament_code'];
$_SESSION['tournament_code'] = $id;

// Tournament info (includes status: 0 = ongoing, 2 = ended)
$tour_sql = $con->query("
    SELECT t.status, s.name AS sport_name, gm.name AS game_mode
    FROM tbl_tournament AS t
    LEFT JOIN tbl_game_modes AS gm ON t.game_id = gm.game_id 
    LEFT JOIN tbl_sports AS s ON gm.sport_id = s.sport_id
    WHERE t.tourna_id = '$id'
");
$tournament = mysqli_fetch_assoc($tour_sql);

// Fetch all teams
$teams = [];
$sql_teams = $con->query("
    SELECT t.place, t.team_id, a.name, a.img_logo, t.disqualify
    FROM tbl_team AS t 
    LEFT JOIN tbl_association AS a ON t.ass_id = a.ass_id 
    WHERE t.tourna_id = '$id'
");

while ($team = mysqli_fetch_assoc($sql_teams)) {
    $teamId = $team['team_id'];

    // Players count
    $p_sql = $con->query("SELECT COUNT(*) AS c FROM tbl_team_players WHERE team_id='$teamId'");
    $players = mysqli_fetch_assoc($p_sql)['c'];

    // Win/Loss record
    $r_sql = $con->query("
        SELECT 
            SUM(winner = $teamId) AS win, 
            SUM(winner != $teamId AND winner IS NOT NULL) AS lose
        FROM tbl_matches 
        WHERE (team1 = $teamId OR team2 = $teamId) 
        AND tourna_id='$id'
    ");
    $record = mysqli_fetch_assoc($r_sql);

    // Match progress
    $m_sql = $con->query("
        SELECT COUNT(*) AS total, 
               SUM(status = 'Finished') AS finished
        FROM tbl_matches
        WHERE (team1 = $teamId OR team2 = $teamId)
        AND tourna_id='$id'
    ");
    $match = mysqli_fetch_assoc($m_sql);

    // Append team data
    $teams[] = [
        'team_id' => $teamId,
        'name' => $team['name'],
        'img_logo' => base64_encode($team['img_logo']),
        'disqualify' => $team['disqualify'],
        'place' => $team['place'],
        'players' => $players,
        'win' => (int)$record['win'],
        'lose' => (int)$record['lose'],
        'total' => (int)$match['total'],
        'finished' => (int)$match['finished']
    ];
}

// ------------------------------
// SORT FOR CURRENT STANDING
// ------------------------------
usort($teams, function($a, $b) {
    if ($b['win'] == $a['win']) return $a['lose'] <=> $b['lose'];
    return $b['win'] <=> $a['win'];
});

// ------------------------------
// ASSIGN DISPLAY RANKING
// ------------------------------
$rank = 1;
foreach ($teams as &$team) {

    // Disqualified teams
    if ($team['disqualify'] == 1) {
        $team['standing'] = "<span style='color:red;'>Disqualified!</span>";
        continue;
    }

    // No matches played
    if ($team['total'] == 0) {
        $team['standing'] = "<span style='color:gray;'>Match not Started</span>";
        continue;
    }

    // Tournament is ONGOING → show CURRENT RANKING
    if ($tournament['status'] != 2) {
        $suffix = "th";
        if (!in_array(($rank % 100), [11,12,13])) {
            switch ($rank % 10) {
                case 1: $suffix="st"; break;
                case 2: $suffix="nd"; break;
                case 3: $suffix="rd"; break;
            }
        }
        $team['standing'] = "<span style='color:blue;font-weight:bold;'>{$rank}{$suffix} Place</span>";
        $rank++;
        continue;
    }

    // Tournament is ENDED → FINAL RANKINGS
    if ($rank == 1) {
        $team['standing'] = "<span style='color:green;font-weight:bold;'>Champion</span>";
    } else {
        $suffix = "th";
        if (!in_array(($rank % 100), [11,12,13])) {
            switch ($rank % 10) {
                case 1: $suffix="st"; break;
                case 2: $suffix="nd"; break;
                case 3: $suffix="rd"; break;
            }
        }
        $team['standing'] = "<span style='color:blue;font-weight:bold;'>{$rank}{$suffix} Place</span>";
    }

    $rank++;
}
unset($team);
?>

<!-- HTML OUTPUT -->
<div class="main">
    <div class="head">
        <h2><?php echo $tournament['game_mode']." (".$tournament['sport_name'].")"; ?></h2>
    </div>

    <div class="body">
        <div class="team_list">
        <?php foreach ($teams as $team): ?>
            <div class="team_holder team_btn" team_id="<?php echo $team['team_id']; ?>">
                <div>
                    <img src="data:image/png;base64,<?php echo $team['img_logo']; ?>" alt="">
                    <div>
                        <h3><?php echo $team['name']; ?></h3>
                        <p><?php echo $team['players']; ?> player's</p>
                    </div>
                </div>

                <h4>Record</h4>
                <ul>
                    <li style="color:seagreen;font-weight:bold;">Win: <span><?php echo $team['win']; ?></span></li>
                    <li style="color:red;font-weight:bold;">Lose: <span><?php echo $team['lose']; ?></span></li>
                    <li style="font-weight:bold;">Standing: <?php echo $team['standing']; ?></li>
                </ul>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
/* (same CSS) */
.head {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #272c33;
}
.team_list {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-evenly;
    height: 78vh;
    overflow-y: auto;
}
.team_list > div {
    width: 24%;
    border: 1px solid;
    border-radius: 10px;
    padding: 8px;
    margin-top: 10px;
}
.team_holder > div {
    display: flex;
    align-items: center;
    justify-content: space-evenly;
}
.team_holder img {
    width: 100px;
}
.team_holder {
    cursor: pointer;
}
.team_holder ul {
    list-style-type: disclosure-closed;
    margin-left: 30px;
}
</style>
