<?php
include "../conn.php";
session_start();

if (!isset($_GET['prog_id'])) {
    echo "<p class='no_data'>Missing program ID.</p>";
    exit;
}

$prog_id = intval($_GET['prog_id']);

// ✅ Fetch program and association info
$prog_sql = $con->query("
    SELECT p.prog_name, a.ass_id, a.name AS ass_name
    FROM tbl_programs AS p
    LEFT JOIN tbl_association AS a ON p.ass_id = a.ass_id
    WHERE p.prog_id = '$prog_id'
");

$prog = mysqli_fetch_assoc($prog_sql);

if (!$prog) {
    echo "<p class='no_data'>Program not found.</p>";
    exit;
}

$prog_name = htmlspecialchars($prog['prog_name']);
$ass_name  = htmlspecialchars($prog['ass_name']);
$ass_id    = intval($prog['ass_id']);
?>

<div class="main">
    <div class="head">
        <h2><?php echo $prog_name; ?> — Players</h2>
        <div class="buttons">
            <button class="btn btn-secondary back_to_ass" data-ass_id="<?php echo $ass_id; ?>">← Back to <?php echo $ass_name; ?></button>
        </div>
    </div>

    <div class="body">
        <div class="display-flexed">
            <?php
            // ✅ Get players where tbl_profile.prog_id matches this program
            $player_sql = $con->query("
                SELECT pr.fullname, pr.year_level, pr.profile
                FROM tbl_profile AS pr
                WHERE pr.prog_id = '$prog_id'
            ");

            if ($player_sql && $player_sql->num_rows > 0) {
                while ($player = mysqli_fetch_assoc($player_sql)) {
                    $fullname = htmlspecialchars($player['fullname'] ?? 'Unnamed Player');
                    $year = htmlspecialchars($player['year_level'] ?? '');
                    $profile_img = !empty($player['profile'])
                        ? 'data:image/png;base64,' . base64_encode($player['profile'])
                        : '../assets/default-profile.png';
            ?>
                <div class="player_holder" title="<?php echo $fullname; ?>">
                    <img src="<?php echo $profile_img; ?>" alt="Player Photo">
                    <h4><?php echo $fullname; ?></h4>
                    <p><?php echo $year; ?></p>
                </div>
            <?php
                }
            } else {
                echo "<p class='no_data'>No players found for this program.</p>";
            }
            ?>
        </div>
    </div>
</div>

<style>
.main { padding: 20px; background: #f9f9f9; }
.head { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
.head h2 { color: #222; font-weight: 700; }
.buttons { display: flex; gap: 10px; }
.btn-secondary { background: gray; color: #fff; border: none; border-radius: 6px; padding: 8px 15px; font-weight: 600; cursor: pointer; transition: background 0.2s ease; }
.btn-secondary:hover { background: dimgray; }

.display-flexed { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; overflow-y: auto; padding: 10px; }
.player_holder { flex: 1 1 calc(25% - 20px); background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 10px; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); transition: transform 0.2s ease; }
.player_holder:hover { transform: translateY(-5px); }
.player_holder img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 8px; }
.player_holder h4 { font-size: 15px; color: #333; margin: 0; }
.player_holder p { font-size: 13px; color: #666; }
.no_data { width: 100%; text-align: center; color: #666; padding: 20px; }
</style>

<script>
$(document).ready(function(){
    $(document).on("click", ".back_to_ass", function(){
        const ass_id = $(this).data("ass_id");
        $.get(`focus_association.php?ass_id=${ass_id}`, function(html){
            $(".content-main").remove();
            $(".content").hide();
            $(".content").html(`<div class="content-main">${html}</div>`).fadeIn(200);
        });
    });
});
</script>
