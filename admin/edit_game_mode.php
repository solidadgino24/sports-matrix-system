<?php
$id = $_GET['id'];
include "../conn.php";
session_start();

$sql = $con->query("SELECT * FROM tbl_game_modes WHERE game_id='$id'");
$row = mysqli_fetch_assoc($sql);
$match_point = ($row['scoring'] == 2) ?  $row['point_base'] : 0;
$quarter = ($row['scoring'] == 1) ?  $row['sets'] : 0;
$set = ($row['scoring'] == 2) ?  $row['sets'] : 0;
?>
<div class="main">
    <div class="head">
        <button class="btn sport_holder" sport_id="<?php echo $_SESSION['game_mode'] ?>">Back</button>
        <h2>Edit Game Mode</h2>
    </div>

    <div class="body">
        <form action="#" id="game_mode_form" class="game_mode_card">
            <input type="hidden" name="game_id" value="<?php echo $id ?>">

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name_mode" class="form-control" value="<?php echo $row['name'] ?>">
            </div>

            <div class="form-group">
                <label for="player">Players</label>
                <input type="number" name="player" class="form-control" value="<?php echo $row['players'] ?>">
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" class="form-control category" required>
                    <option disabled>-- SELECT --</option>
                    <?php 
                        $category = $con->query("SELECT * FROM tbl_game_mode_cat");
                        while($cat = mysqli_fetch_assoc($category)){
                            $selected = ($row['gm_cat_id'] == $cat['gm_cat_id']) ? "selected" : "";
                            echo "<option value='{$cat['gm_cat_id']}' $selected>{$cat['category']}</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="scoring">Scoring</label>
                <select name="scoring" class="form-control scoring" required>
                    <option disabled>-- SELECT --</option>
                    <option value="1" <?php echo ($row['scoring']==1)? "selected" : "" ?>>Point based</option>
                    <option value="2" <?php echo ($row['scoring']==2)? "selected" : "" ?>>Set based</option>
                </select>
            </div>

            <div class="point_opt" style="display:<?php echo ($row['scoring']==1)? "block" : "none"; ?>;">
                <div class="form-group">
                    <label for="quarters">Quarter('s)</label>
                    <input type="number" name="quarters" class="form-control" value="<?php echo $quarter ?>">
                </div>
            </div>

            <div class="set_opt" style="display:<?php echo ($row['scoring']==1)? "none" : "block"; ?>;">
                <div class="form-group">
                    <label for="points">Match point</label>
                    <input type="number" name="points" class="form-control" value="<?php echo $match_point ?>">
                </div>
                <div class="form-group">
                    <label for="game_set">Sets('s)</label>
                    <input type="number" name="game_set" class="form-control" value="<?php echo $set ?>">
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-success">Save</button>
            </div>
        </form>
    </div>
</div>

<style>
.main {
    padding: 20px;
    font-family: Arial, sans-serif;
}
.head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #272c33;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.head h2 {
    margin: 0;
}
.head > button {
    border: none;
    background-color: coral;
    color: #fff;
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.head > button:hover {
    transform: translateY(-2px);
}

/* Card-style form */
.game_mode_card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    max-width: 600px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.form-group {
    display: flex;
    flex-direction: column;
}
.form-group label {
    font-weight: bold;
    margin-bottom: 6px;
}
.form-control {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.form-control:focus {
    border-color: coral;
    box-shadow: 0 0 5px rgba(255,127,80,0.3);
    outline: none;
}

/* Save button */
.form-actions {
    text-align: right;
}
.btn-success {
    background-color: coral;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.btn-success:hover {
    transform: translateY(-2px);
}
</style>
