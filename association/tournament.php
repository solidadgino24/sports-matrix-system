<?php 
include_once("../conn.php");
session_start();

$ev_id = $_SESSION['ev_id'];
$ass_id = $_SESSION['ass_id'] ?? null;
$session_user_id = $_SESSION['session_user_id'] ?? null;

// 🔧 If ass_id is not set, try to retrieve it from the logged-in user
if (!$ass_id && $session_user_id) {
    $get_ass = $con->query("SELECT ass_id FROM tbl_association_staff WHERE user_id = '$session_user_id' LIMIT 1");
    if ($get_ass && mysqli_num_rows($get_ass) > 0) {
        $row_ass = mysqli_fetch_assoc($get_ass);
        $_SESSION['ass_id'] = $row_ass['ass_id'];
        $ass_id = $row_ass['ass_id'];
    }
}

// 🚨 If still not found, stop execution
if (!$ass_id) {
    die("<div style='text-align:center; margin-top:50px; color:red; font-weight:bold;'>
        Error: Association ID not found.<br>
        Please <a href='../logout.php' style='color:blue;'>log in again</a>.
    </div>");
}

$sql = $con->query("SELECT 
                        t.maximum,
                        t.minimum,
                        t.tourna_id,
                        t.status,
                        s.img,
                        s.name AS sport_name,
                        gm.name,
                        c.category,
                        gm.players 
                    FROM tbl_tournament AS t 
                    LEFT JOIN tbl_game_modes AS gm ON t.game_id = gm.game_id
                    LEFT JOIN tbl_sports AS s ON gm.sport_id = s.sport_id
                    LEFT JOIN tbl_game_mode_cat AS c ON gm.gm_cat_id = c.gm_cat_id
                    WHERE t.ev_id = '$ev_id'");
?>
<h2 style="text-align:center; color:#272c33; margin-bottom:20px;">Tournaments</h2>
<div class="tournament">
<?php 
if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_assoc($sql)) {
        $tourna_id = $row['tourna_id'];

        // 🟡 Pending applications count
        $app = mysqli_fetch_assoc($con->query("
            SELECT COUNT(*) AS total_count 
            FROM tbl_tourna_application AS ta
            INNER JOIN tbl_profile AS p ON ta.prof_id = p.prof_id
            INNER JOIN tbl_association_players AS ap ON p.user_id = ap.user_id
            WHERE ta.tourna_id = '$tourna_id'
            AND ap.ass_id = '$ass_id'
            AND ta.ev_id = '$ev_id'
            AND ta.status = '0'
        "));

        // 🟢 Accepted applications count
        $acc = mysqli_fetch_assoc($con->query("
            SELECT COUNT(*) AS total_count 
            FROM tbl_tourna_application AS ta
            INNER JOIN tbl_profile AS p ON ta.prof_id = p.prof_id
            INNER JOIN tbl_association_players AS ap ON p.user_id = ap.user_id
            WHERE ta.tourna_id = '$tourna_id'
            AND ap.ass_id = '$ass_id'
            AND ta.ev_id = '$ev_id'
            AND ta.status = '1'
        "));

        // 🔹 Determine if tournament is full
        $is_full = ($acc['total_count'] >= $row['maximum']);

        // ✅ Determine tournament status (numeric or text)
        switch ((int)$row['status']) {
            case 0:
                $tournament_status = "Not Started";
                $status_color = "#888";
                break;
            case 1:
                $tournament_status = "Ongoing";
                $status_color = "orange";
                break;
            case 2:
                $tournament_status = "Ended";
                $status_color = "#555";
                break;
            default:
                $tournament_status = "Not Started";
                $status_color = "#888";
                break;
        }
?>
    <div class="tournament-card">
        <div class="card-img">
            <img src="data:image/png;base64,<?php echo $row['img'] ?>" alt="<?php echo htmlspecialchars($row['sport_name']); ?>">
        </div>
        <div class="card-body">
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            <p><strong>Sport:</strong> <?php echo htmlspecialchars($row['sport_name']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>

            <div class="badge-group">
                <?php if ($is_full): ?>
                    <span class="status-badge full">Limit Reached</span>
                <?php endif; ?>

                <span class="status-badge" style="background-color: <?php echo $status_color; ?>;">
                    <?php echo $tournament_status; ?>
                </span>
            </div>

            <div class="stats">
                <span class="applications">Applications: <?php echo $app['total_count']; ?></span>
                <span class="accepted">Accepted: <?php echo $acc['total_count']; ?></span>
            </div>
            <p class="players"><strong>Players:</strong> <?php echo $row['minimum'].' - '.$row['maximum']; ?></p>
        </div>

        <!-- Disable See More if Ended -->
        <button class="see-more-btn" 
            data-id="<?php echo $row['tourna_id']; ?>" 
            <?php echo ($tournament_status == "Ended" ? "disabled" : ""); ?>>
            <?php echo ($tournament_status == "Ended" ? "Ended" : "See More"); ?>
        </button>
    </div>
<?php 
    } // while
} else { ?>
    <h4 style="text-align:center; width:100%; margin-top:20px; color:#272c33;">No tournaments created.</h4>
<?php } ?>
</div>

<style>
.tournament {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    padding-bottom: 20px;
}
.tournament-card {
    width: 300px;
    background-color: #272c33;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.tournament-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.5);
}
.card-img img {
    width: 100%;
    height: 260px;
    object-fit: cover;
    background-color: #f4f4f4;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.card-body {
    padding: 18px;
    color: #fff;
}
.card-body h3 {
    margin: 0;
    font-size: 20px;
    color: #ff6b4d;
    font-weight: bold;
}
.card-body p {
    margin: 0;
    font-size: 14px;
    color: #ccc;
}
.badge-group {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 5px;
}
.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
    color: #fff;
}
.status-badge.full { background-color: #e74c3c; }
.stats {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
}
.stats span {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    color: #fff;
}
.stats span.applications { background: hsla(10, 100%, 65%, 1.00); }
.stats span.accepted { background: hsla(131, 100%, 65%, 1.00); }
.players { font-size: 14px; color: #fff; }
.see-more-btn {
    margin: 12px 18px 18px 18px;
    padding: 10px;
    border: 2px solid hsla(216, 98%, 35%, 1.00);
    background-color: hsla(216, 100%, 65%, 1.00);
    color: #fff;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}
.see-more-btn:hover {
    border: 2px solid hsla(216, 98%, 45%, 1.00);
    background-color: hsla(216, 100%, 75%, 1.00);
}
.see-more-btn[disabled] {
    background: #555;
    border-color: #444;
    cursor: not-allowed;
    opacity: 0.7;
}
@media(max-width: 768px) {
    .tournament-card { width: 90%; }
}
</style>

<script>
$(".see-more-btn").click(function(){
    if ($(this).is("[disabled]")) return;
    let id = $(this).attr("data-id");
    $.get("focus_tournament.php?id=" + id, function(e){
        $(".content-main").remove();
        $(".content").hide();
        $(".content").html(`<div class='content-main'>${e}</div>`).fadeIn(200);
    });
});
</script>
