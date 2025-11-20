<?php
include_once("../conn.php");
$id = $_GET['id'];
session_start();

$get_tourna = $con->query("
    SELECT 
        c.category,
        gm.players,
        gm.scoring,
        gm.name AS game_mode,
        s.name AS sport_name,
        s.img,
        t.maximum,
        t.minimum,
        t.tournament_type
    FROM tbl_tournament AS t 
    LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id
    LEFT JOIN tbl_sports AS s ON gm.sport_id = s.sport_id
    LEFT JOIN tbl_game_mode_cat AS c ON gm.gm_cat_id = c.gm_cat_id
    WHERE tourna_id='$id'
");
$tourna = mysqli_fetch_assoc($get_tourna);
?>

<div class="main">
    <div class="head">
        <h2>Modify Tournament</h2>
        <div class="head-buttons">
            <button class="btn back_btn">Back</button>
            <button class="btn delete_btn" onclick="deleteTourna(<?php echo $id ?>)">Delete</button>
        </div>
    </div>

    <div class="body display-flexed">
        <div class="tourna_card">
            <img src="data:image/png;base64,<?php echo $tourna['img'] ?>" alt="">
            <ul class="tourna_info">
                <li><label>Name:</label> <p><?php echo $tourna['game_mode'] ?></p></li>
                <li><label>Category:</label> <p><?php echo $tourna['category'] ?></p></li>
                <li><label>Scoring:</label> <p><?php echo ($tourna['scoring']==1)? "Quarter's":"Sets" ?></p></li>
                <li><label>Players:</label> <p><?php echo $tourna['players'] ?> Player(s)</p></li>
            </ul>
        </div>

        <form action="#" id="modify_tourna_form" class="tourna_card">
            <input type="hidden" name="tourna_id" value="<?php echo $id; ?>">
            <input type="hidden" class="player" value="<?php echo $tourna['players'] ?>">

            <div class="form-group">
                <label>Sport</label>
                <input type="text" value="<?php echo $tourna['sport_name'] ?>" class="form-control" disabled>
            </div>

            <div class="form-group">
                <label>Game Mode</label>
                <input type="text" value="<?php echo $tourna['game_mode'] ?>" class="form-control" disabled>
            </div>

            <!-- 🔹 Tournament Type Field -->
            <div class="form-group">
                <label>Tournament Type</label>
                <select name="tournament_type" class="form-control" required>
                    <option value="" disabled>Select Type</option>
                    <option value="Elimination" <?php echo ($tourna['tournament_type']=='Elimination')?'selected':''; ?>>Elimination</option>
                    <option value="Knockout" <?php echo ($tourna['tournament_type']=='Knockout')?'selected':''; ?>>Knockout</option>
                    <option value="Round Robin" <?php echo ($tourna['tournament_type']=='Round Robin')?'selected':''; ?>>Round Robin</option>
                    <option value="League" <?php echo ($tourna['tournament_type']=='League')?'selected':''; ?>>League</option>
                </select>
            </div>

            <div class="form-group">
                <label>Maximum Players</label>
                <input type="number" name="max_player" class="form-control maximum" value="<?php echo $tourna['maximum'] ?>" required>
            </div>

            <div class="form-group">
                <label>Minimum Players</label>
                <input type="number" name="min_player" class="form-control minimum" value="<?php echo $tourna['minimum'] ?>" required>
            </div>

            <button class="btn btn-success save_btn">Save</button>
        </form>
    </div>
</div>

<style>
.head{
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #272c33;
    padding: 10px 0;
}
.head-buttons{
    display: flex;
    gap: 10px;
}
.head-buttons .btn{
    border: none;
    background-color: coral;
    color: #fff;
    font-weight: bold;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}
.head-buttons .btn:hover{
    background-color: #e06b4d;
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.display-flexed{
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 20px;
    padding: 15px;
    overflow-y: auto;
    max-height: 78vh;
}

.tourna_card{
    flex: 1 1 calc(50% - 20px);
    max-width: calc(50% - 20px);
    min-width: 250px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    padding: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.tourna_card:hover{
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

.tourna_card img{
    width: 100%;
    max-height: 150px;
    object-fit: contain;
    margin-bottom: 10px;
    border: 1px solid #272c33;
    border-radius: 5px;
}

.tourna_info{
    list-style: none;
    padding: 0;
    margin: 0;
}
.tourna_info li{
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
}

.form-group{
    display: flex;
    flex-direction: column;
    margin-bottom: 10px;
}

.radio-group{
    display: flex;
    gap: 20px;
}

.save_btn{
    margin-top: 10px;
    background-color: coral;
    border: none;
    color: #fff;
    padding: 8px 15px;
    font-weight: bold;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.save_btn:hover{
    background-color: #e06b4d;
}
</style>

<script>
$(".back_btn").click(function(){
    $.get("tournament.php", function(e){
        $(".content-main").remove();
        $(".content").hide();
        $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
    });
});

function deleteTourna(id){
    if(confirm("Are you sure you want to delete this?")){
        $.post("../action.php?a=delete_tourna",{id:id},function(res){
            if(res.status){
                $.get("tournament.php",function(e){
                    $(".content-main").remove();
                    $(".content").hide();
                    $(".content").html(`<div class="content-main">${e}</div>`).fadeIn(200);
                });
            }else{
                alert(res.message);
            }
        });
    }
}
</script>
