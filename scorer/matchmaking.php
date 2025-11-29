<?php
include_once("../conn.php");
session_start();
$session_user_id = $_SESSION['session_user_id'];
$id = $_SESSION['tournament_code'];

// Fetch tournament info including STATUS
$sql = $con->query("
    SELECT t.status, s.name AS sport_name, gm.name AS game_mode, gm.scoring 
    FROM tbl_tournament AS t
    LEFT JOIN tbl_game_modes AS gm ON t.game_id = gm.game_id 
    LEFT JOIN tbl_sports AS s ON gm.sport_id = s.sport_id
    WHERE t.tourna_id = '$id'
");
$tourna = mysqli_fetch_assoc($sql);

// Fetch matches
$recent_match = $con->query("
    SELECT m.*, 
           a1.name AS team1_name, a1.img_logo AS team1_logo,
           a2.name AS team2_name, a2.img_logo AS team2_logo
    FROM tbl_matches AS m
    LEFT JOIN tbl_team AS t1 ON m.team1 = t1.team_id
    LEFT JOIN tbl_team AS t2 ON m.team2 = t2.team_id
    LEFT JOIN tbl_association AS a1 ON t1.ass_id = a1.ass_id
    LEFT JOIN tbl_association AS a2 ON t2.ass_id = a2.ass_id
    WHERE m.tourna_id = '$id'
    ORDER BY m.match_id DESC
");
?>
<div class="main">
    <div class="head">
        <h2><?php echo $tourna['game_mode'] . " (" . $tourna['sport_name'] . ")"; ?></h2>

        <div>
            <button class="btn refresh_match">Refresh</button>

            <?php if ($tourna['status'] != 2) { ?>
                <button class="btn set_match">Set Match</button>
                <button class="btn btn-danger end_tourna">End Tournament</button>
            <?php } else { ?>
                <button class="btn btn-secondary" disabled>Tournament Ended</button>
            <?php } ?>
        </div>
    </div>

    <div class="body">
        <table class="table">
            <thead>
                <tr>
                    <th>Match ID</th>
                    <th>Team 1</th>
                    <th>Team 2</th>
                    <th>Start Date/Time</th>
                    <th>End Date/Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($recent_match)) { 
                    $match_id = $row['match_id'];

                    // scoring logic
                    if ($tourna['scoring'] == 1) {
                        $sql_score = $con->query("SELECT team1, team2 FROM tbl_score_match WHERE match_id='$match_id' ORDER BY score_id DESC");
                    } else {
                        $sql_score = $con->query("SELECT SUM(winner='team1') AS team1, SUM(winner='team2') AS team2 FROM tbl_score_match WHERE match_id='$match_id'");
                    }

                    $score = mysqli_fetch_assoc($sql_score);
                    $team1_score = $score['team1'] ?? 0;
                    $team2_score = $score['team2'] ?? 0;
                ?>
                <tr>
                    <td><?php echo $row['match_id']; ?></td>
                    <td>
                        <img src="data:image/png;base64,<?php echo base64_encode($row['team1_logo']); ?>" width="50">
                        <?php echo $row['team1_name']; ?>
                    </td>
                    <td>
                        <img src="data:image/png;base64,<?php echo base64_encode($row['team2_logo']); ?>" width="50">
                        <?php echo $row['team2_name']; ?>
                    </td>
                    <td><?php echo ($row['start_date']) ? date('M j, Y h:i A', strtotime($row['start_date'])) : ""; ?></td>
                    <td><?php echo ($row['end_date']) ? date('M j, Y h:i A', strtotime($row['end_date'])) : ""; ?></td>
                    <td>
                        <?php 
                            if ($row['status'] == 0) echo "Scheduled";
                            elseif ($row['status'] == 1) echo "Ongoing";
                            else echo "Ended";
                        ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <h3>Recent Matches</h3>
        <div class="recent_match">
            <ul>
            <?php 
                mysqli_data_seek($recent_match, 0);
                while ($row = mysqli_fetch_assoc($recent_match)) {
                    $match_id = $row['match_id'];

                    if ($tourna['scoring'] == 1) {
                        $sql_score = $con->query("SELECT team1, team2 FROM tbl_score_match WHERE match_id='$match_id' ORDER BY score_id DESC");
                    } else {
                        $sql_score = $con->query("SELECT SUM(winner='team1') AS team1, SUM(winner='team2') AS team2 FROM tbl_score_match WHERE match_id='$match_id'");
                    }

                    $score = mysqli_fetch_assoc($sql_score);
                    $team1_score = $score['team1'] ?? 0;
                    $team2_score = $score['team2'] ?? 0;
            ?>
                <li>
    <div class="col-md-12">
        <p class="ptag">
            Start: <span><?php echo ($row['start_date']) ? date('M j, Y h:i A', strtotime($row['start_date'])) : ""; ?></span> 
            End: <span><?php echo ($row['end_date']) ? date('M j, Y h:i A', strtotime($row['end_date'])) : ""; ?></span>
        </p>

        <section class="col-md-5 team_holder left">
            <h3>
                <img src="data:image/png;base64,<?php echo base64_encode($row['team1_logo']); ?>" width="100">
                <?php echo $row['team1_name']; ?>
                <?php if ($row['team1'] == $row['winner']) { ?><span class="win">Win</span><?php } ?>
            </h3>
        </section>

        <section class="col-md-2">
            <h1>VS</h1>
            <h3><?php echo $team1_score . " - " . $team2_score; ?></h3>

            <?php if ($row['status'] == 2) { ?>
            <button class="btn btn-danger deleteMatch"
                data-id="<?php echo $row['match_id']; ?>"
                style="margin-top:10px; padding:5px 10px;">
                Delete
            </button>
            <?php } ?>
        </section>

        <section class="col-md-5 team_holder right">
            <h3>
                <img src="data:image/png;base64,<?php echo base64_encode($row['team2_logo']); ?>" width="100">
                <?php echo $row['team2_name']; ?>
                <?php if ($row['team2'] == $row['winner']) { ?><span class="win">Win</span><?php } ?>
            </h3>
        </section>
    </div>
    <div class="clearfix"></div>
</li>

            <?php } ?>
            </ul>
        </div>
    </div>
</div>

<!-- MANUAL MATCH MODAL -->
<div id="manualModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;padding-top:5%;">
    <div style="background:#fff;width:400px;margin:auto;padding:20px;border-radius:10px;">
        <h3>Set Manual Match</h3>
        <label>Team 1</label>
        <select id="team1" class="form-control"><option value="">Select Team</option></select>

        <label>Team 2</label>
        <select id="team2" class="form-control"><option value="">Select Team</option></select>

        <br>
        <button class="btn btn-success" id="saveMatch">Create Match</button>
        <button class="btn btn-danger" id="closeModal">Cancel</button>
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
</style>
<script>
// OPEN MODAL
document.querySelector(".set_match")?.addEventListener("click", function() {
    loadTeams();
    document.getElementById("manualModal").style.display = "block";
});

// CLOSE MODAL
document.getElementById("closeModal").onclick = function() {
    document.getElementById("manualModal").style.display = "none";
};

// LOAD TEAMS
function loadTeams() {
    fetch("back_end_match.php?a=team_list")
    .then(res => res.json())
    .then(data => {
        let t1 = document.getElementById("team1");
        let t2 = document.getElementById("team2");

        t1.innerHTML = "<option value=''>Select Team</option>";
        t2.innerHTML = "<option value=''>Select Team</option>";

        data.forEach(team => {
            t1.innerHTML += `<option value="${team.team_id}">${team.ass_name}</option>`;
            t2.innerHTML += `<option value="${team.team_id}">${team.ass_name}</option>`;
        });
    });
}

// SAVE MATCH
document.getElementById("saveMatch").onclick = function() {
    let t1 = document.getElementById("team1").value;
    let t2 = document.getElementById("team2").value;

    if (!t1 || !t2 || t1 === t2) {
        alert("Invalid team selection.");
        return;
    }

    fetch("back_end_match.php?a=create_manual", {
        method: "POST",
        body: new URLSearchParams({ team1: t1, team2: t2 })
    })
    .then(res => res.json())
    .then(d => {
        alert(d.message);
        if (d.status) location.reload();
    });
};
// DELETE MATCH
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("deleteMatch")) {
        let id = e.target.dataset.id;

        if (!confirm("Delete this concluded match? This action cannot be undone.")) return;

        fetch("back_end_match.php?a=delete_match&match_id=" + id)
        .then(res => res.json())
        .then(d => {
            alert(d.message);
            if (d.status) location.reload();
        });
    }
});

// END TOURNAMENT
document.querySelector(".end_tourna")?.addEventListener("click", function () {
    if (!confirm("Are you sure you want to END this tournament? This cannot be undone.")) return;

    fetch("back_end_match.php?a=end_tournament")
    .then(res => res.json())
    .then(d => {
        alert(d.message);
        if (d.status) location.reload();
    });
});
</script>
