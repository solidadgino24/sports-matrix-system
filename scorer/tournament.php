<?php 
include_once("../conn.php");
session_start();
$ev_id = $_SESSION['ev_id'];

$sql = $con->query("
    SELECT 
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
    WHERE t.ev_id = '$ev_id'
");
?>
<h2>Tournament</h2>
<div class="tournament">
    <?php 
    if (mysqli_num_rows($sql) > 0) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $tourna_id = $row['tourna_id'];

            // Count pending and accepted applications
            $app = mysqli_fetch_assoc($con->query("
                SELECT COUNT(*) AS total_count 
                FROM tbl_tourna_application 
                WHERE tourna_id = '$tourna_id' AND status = '0'
            "));
            $acc = mysqli_fetch_assoc($con->query("
                SELECT COUNT(*) AS total_count 
                FROM tbl_tourna_application 
                WHERE tourna_id = '$tourna_id' AND status = '1'
            "));

            // ✅ Interpret numeric status
            $status_map = [
                0 => ['label' => 'Not started', 'color' => 'gray'],
                1 => ['label' => 'Ongoing', 'color' => 'orange'],
                2 => ['label' => 'Ended', 'color' => 'red']
            ];

            $status_value = (int)$row['status'];
            $status_label = $status_map[$status_value]['label'] ?? 'Unknown';
            $status_color = $status_map[$status_value]['color'] ?? 'gray';
    ?>
    <div class="tournament-card">
        <img src="data:image/png;base64,<?php echo $row['img'] ?>" alt="<?php echo $row['sport_name'] ?>">
        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
        <p><strong>Sport:</strong> <?php echo htmlspecialchars($row['sport_name']); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
        <p><strong>Applications:</strong> <?php echo $app['total_count']; ?></p>
        <p><strong>Accepted:</strong> <?php echo $acc['total_count']; ?></p>
        <p><strong>Min Players:</strong> <?php echo $row['minimum']; ?></p>
        <p><strong>Max Players:</strong> <?php echo $row['maximum']; ?></p>
        <p><strong>Status:</strong> 
            <span style="color:<?php echo $status_color; ?>; font-weight:bold;">
                <?php echo $status_label; ?>
            </span>
        </p>
        <button class="see-more-btn" data_id='<?php echo $row['tourna_id']; ?>'>See More</button>
    </div>
    <?php 
        } 
    } else { 
    ?>
        <h4>No tournament created.</h4>
    <?php } ?>
</div>

<style>
.tournament {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
}
.tournament-card {
    width: 300px;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    text-align: left;
    position: relative;
    background-color: #fff;
}
.tournament-card img {
    width: 100%;
    height: 200px;
    object-fit: contain;
    border-radius: 8px;
    display: block;
    margin: 0 auto;
    background-color: #f4f4f4;
}
.see-more-btn {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 20px;
    border: 2px solid #1e3a8a;
    background-color: #3b82f6;
    color: white;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s ease, color 0.3s ease;
    cursor: pointer;
}
.see-more-btn:hover {
    border-color: #2563eb;
    background-color: #60a5fa;
    color: black;
}
</style>

<script>
$(".see-more-btn").click(function(){
    const id = $(this).attr("data_id");
    if(!id) return;
    $.get("focus_tournament.php?id=" + id, function(e){
        $(".content-main").remove();
        $(".content").hide();
        $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
    });
});
</script>
