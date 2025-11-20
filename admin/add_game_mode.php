<?php
include "../conn.php";
session_start();
?>
<div class="main">
    <div class="head">
        <button class="btn sport_holder" sport_id="<?php echo $_SESSION['game_mode'] ?>">Back</button>
        <h2>Edit Game Mode</h2>
    </div>

    <div class="body">
        <form action="#" id="game_mode_form">
            <div class="game_modes">
                <div class="game_mode_card">
                    <div class="form-group">
                        <label for="name">Game Mode Name</label>
                        <input type="text" name="name_mode" class="form-control" placeholder="Enter game mode name">
                    </div>

                    <div class="form-group">
                        <label for="player">Players</label>
                        <input type="number" name="player" class="form-control" placeholder="Number of players">
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select name="category" class="form-control category" required>
                            <option disabled selected>-- SELECT --</option>
                            <?php 
                                $category = $con->query("SELECT * FROM tbl_game_mode_cat");
                                while($cat = mysqli_fetch_assoc($category)){
                            ?>
                                <option value="<?php echo $cat['gm_cat_id'] ?>"><?php echo $cat['category'] ?></option>
                            <?php }  ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="scoring">Scoring Type</label>
                        <select name="scoring" class="form-control scoring" required>
                            <option disabled selected>-- SELECT --</option>
                            <option value="1">Point based</option>
                            <option value="2">Set based</option>
                        </select>
                    </div>

                    <div class="point_opt" style="display:none;">
                        <div class="form-group">
                            <label for="quarters">Quarter('s)</label>
                            <input type="number" name="quarters" class="form-control quarters" placeholder="Number of quarters">
                        </div>
                    </div>

                    <div class="set_opt" style="display:none;">
                        <div class="form-group">
                            <label for="points">Match point</label>
                            <input type="number" name="points" class="form-control points" placeholder="Points per match">
                        </div>
                        <div class="form-group">
                            <label for="game_set">Sets('s)</label>
                            <input type="number" name="game_set" class="form-control game_set" placeholder="Number of sets">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-navigate">
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

/* Header */
.head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #272c33;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.head > button {
    border: none;
    background-color: coral;
    color: #fff;
    font-weight: bold;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.15s ease;
}
.head > button:hover {
    background-color: #e06b4d;
    transform: translateY(-2px);
}
.head h2 {
    margin: 0;
    font-size: 22px;
}

/* Card for each game mode */
.game_mode_card {
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    background-color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.game_mode_card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

/* Form groups */
.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}
.form-group label {
    font-weight: bold;
    margin-bottom: 5px;
}
.form-group input, 
.form-group select {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.form-group input:focus,
.form-group select:focus {
    border-color: coral;
    box-shadow: 0 0 5px rgba(255,127,80,0.3);
    outline: none;
}

/* Save button */
.form-navigate {
    text-align: right;
    margin-top: 15px;
}
.btn-success {
    background-color: coral;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.15s ease;
}
.btn-success:hover {
    background-color: #e06b4d;
    transform: translateY(-2px);
}

/* Optional: remove/add hover for sections */
.point_opt, .set_opt {
    border-top: 1px dashed #ddd;
    padding-top: 10px;
    margin-top: 10px;
}
</style>
