<?php
include_once("../conn.php");
$sql = $con->query("SELECT sport_id, name FROM tbl_sports");
?>

<div class="main">
    <div class="head">
        <h2>Add Tournament</h2>
        <button class='btn back_btn'>Back</button>
    </div>

    <div class="body display-flexed">
        <div class="tourna_card">
            <div class="preview">
                <h3>Select Sport</h3>
                <img src="#" alt="" style="display:none">
            </div>
            <div class="gm_preview"></div>
        </div>

        <form action="#" id="add_tourna_form" class="tourna_card">
            <h3>Tournament</h3>

            <div class="form-group">
                <label>Sport</label>
                <select name="sport" id="sport" class="form-control" required>
                    <option value="" selected disabled>--Select--</option>
                    <?php while($row = mysqli_fetch_assoc($sql)) { ?>
                        <option value="<?php echo $row['sport_id'] ?>"><?php echo $row['name'] ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label>Game Mode</label>
                <select name="game_mode" id="game_mode" class="form-control" required>
                    <option value="" disabled selected>--Select--</option>
                </select>
            </div>

            <!-- <div class="form-group">
                <label>Tournament Type</label>
                <select name="tournament_type" id="tournament_type" class="form-control" required>
                    <option value="" disabled selected>--Select Type--</option>
                    <option value="Elimination">Elimination</option>
                    <option value="Knockout">Knockout</option>
                    <option value="Round Robin">Round Robin</option>
                    <option value="League">League</option>
                </select>
            </div> -->

            <div class="form-group">
                <label>Maximum Players</label>
                <input type="number" name="max_player" class="form-control maximum" required>
            </div>

            <div class="form-group">
                <label>Minimum Players</label>
                <input type="number" name="min_player" class="form-control minimum" required>
            </div>

            <!-- <div class="form-group">
                <label>Auto Disqualify</label>
                <div class="radio-group">
                    <label><input type="radio" name="auto_disqualify" value="1" required> Yes</label>
                    <label><input type="radio" name="auto_disqualify" value="0" required> No</label>
                </div>
            </div> -->

            <button class="btn btn-success save_btn">
                <span class="glyphicon glyphicon-save"></span> Add
            </button>
        </form>
    </div>
</div>

<style>
.head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #272c33;
    padding: 10px 0;
}
.head .btn {
    border: none;
    background-color: coral;
    color: #fff;
    font-weight: bold;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.head .btn:hover {
    background-color: #e06b4d;
}

.display-flexed {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 20px;
    padding: 15px;
}

.tourna_card {
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
.tourna_card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.2);
}

.preview {
    border: 1px solid #272c33;
    border-radius: 5px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    margin-bottom: 10px;
}
.preview img {
    max-width: 100%;
    max-height: 120px;
    margin-top: 10px;
}

.gm_preview {
    padding: 10px;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 10px;
}
.radio-group {
    display: flex;
    gap: 20px;
}

.save_btn {
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
.save_btn:hover {
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
</script>
