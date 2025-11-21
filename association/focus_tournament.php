<?php
include_once("../conn.php");
session_start();

$session_user_id = $_SESSION['session_user_id'];
$user = $con->query("SELECT ass_id FROM tbl_association_staff WHERE user_id ='$session_user_id'");
$user = mysqli_fetch_assoc($user);
$ass_id = $user['ass_id'];

$id = $_GET['id'];
$_SESSION['tournament_code'] = $id;

$sql = $con->query("SELECT s.name AS sport_name,gm.name 
                    FROM tbl_tournament AS t 
                    LEFT JOIN tbl_game_modes AS gm ON t.game_id=gm.game_id
                    LEFT JOIN tbl_sports AS s ON gm.sport_id=s.sport_id
                    WHERE t.tourna_id = '$id'");
$row = mysqli_fetch_assoc($sql);

$query = $con->query("SELECT ta.app_id,p.profile,p.fullname,p.gender,ta.jersey_number,ta.status,a.name AS association
                        FROM tbl_tourna_application AS ta 
                        LEFT JOIN tbl_profile AS p ON ta.prof_id=p.prof_id
                        LEFT JOIN tbl_association_players AS ap ON p.user_id=ap.user_id
                        LEFT JOIN tbl_association AS a ON ap.ass_id=a.ass_id
                        WHERE tourna_id='$id' AND ap.ass_id='$ass_id'");
?>
<div class="main">
    <div class="head">
        <h2><?php echo $row['name']." (".$row['sport_name'].")" ?></h2>
        <div class="head-buttons">
            <button class="btn add_player" data_id='<?php echo $id ?>'>Add Player</button>
            <!-- <button class="btn score_board" data_id='<?php echo $id ?>'>Scoreboard</button> -->
        </div>
    </div>
    <div class="body">
        <div class="table-container">
            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Jersey #</th>
                        <th>Status</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($query)){ ?>
                    <tr>
                        <td><img src="data:image/png;base64,<?php echo $row['profile'] ?>" alt=""></td>
                        <td><?php echo $row['fullname'] ?></td>
                        <td><?php echo ($row['gender'] == 1)? "Male": "Female" ?></td>
                        <td><?php echo $row['jersey_number'] ?></td>
                        <td>
                            <?php 
                                if($row['status'] == 0){
                                    echo "<span class='badge badge-pending'>Applied</span>";
                                } elseif($row['status'] == 2){
                                    echo "<span class='badge badge-denied'>Denied</span>";
                                } else {
                                    echo "<span class='badge badge-joined'>Joined</span>";
                                }
                            ?>
                        </td>
                        <td>
    <?php if($row['status'] == 0){ ?>
        <button class="btn-action accept_applicant" data_id='<?php echo $row['app_id'] ?>' hdata='<?php echo $id ?>'>Accept</button>
        <button class="btn-action deny_applicant" data_id='<?php echo $row['app_id'] ?>' hdata='<?php echo $id ?>'>Deny</button>
    <?php } ?>
    <button class="btn-action delete_applicant" data_id='<?php echo $row['app_id'] ?>' hdata='<?php echo $id ?>' title="Delete">
    <i class="fa fa-trash" style="color:darkred;"></i>
</button>

</td>

                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.main {
    padding: 15px;
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
    font-size: 22px;
    font-weight: 700;
    color: #272c33;
}
.head-buttons button {
    margin-left: 10px;
}

/* Buttons */
.btn {
    border: none;
    background-color: seagreen;
    color: #fff;
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.15s ease;
}
.btn:hover {
    background-color: mediumseagreen;
    transform: translateY(-2px);
}

/* Table */
.table-container {
    overflow-x: auto;
}
.dataTable {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.dataTable th, .dataTable td {
    padding: 12px 15px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
}
.dataTable th {
    background-color: #f5f5f5;
    font-weight: bold;
    color: #333;
}
.dataTable tr:hover {
    background-color: #f0f8ff;
}
.dataTable img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
}

/* Action buttons inside table */
.btn-action {
    padding: 6px 12px;
    margin: 2px;
    border-radius: 5px;
    border: 1px solid #272c33;
    border-bottom: 3px solid #272c33;
    background: #fff;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.2s ease;
}
.btn-action:hover {
    border-color: #9eb2ce;
    border-bottom-color: #7a95b5;
    transform: translateY(-1px);
}
.accept_applicant {
    color: seagreen;
}
.deny_applicant {
    color: crimson;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: bold;
}
.badge-pending { background: #95a5a6; color: #fff; }
.badge-joined { background: #ffd700; color: #000; }
.badge-denied { background: #e74c3c; color: #fff; }

/* Responsive */
@media(max-width: 768px) {
    .head {
        flex-direction: column;
        align-items: flex-start;
    }
    .head-buttons {
        margin-top: 10px;
        width: 100%;
    }
    .head-buttons button {
        margin: 5px 5px 0 0;
        width: calc(50% - 10px);
    }
}
</style>

<script>
$(".dataTable").dataTable();

// Prevent duplicate event bindings
$(document).off("click", ".delete_applicant");

// 🔹 DELETE PLAYER APPLICATION
$(document).on("click", ".delete_applicant", function(){
    let app_id = $(this).attr("data_id");
    let tourna_id = $(this).attr("hdata");

    if(confirm("Are you sure you want to delete this player application?")){
        $.post("../action.php?a=delete_applicant", { app_id: app_id }, function(res){
            if(res.status){
                alert("✅ Player application deleted successfully!");
                // Auto reload updated tournament page
                $(".content-main").remove();
                $(".content").hide();
                $.get(`focus_tournament.php?id=${tourna_id}`, function(html){
                    $(".content").html(`<div class="content-main">${html}</div>`).fadeIn(200);
                });
            } else {
                alert("⚠️ Error deleting player application.");
            }
        }, "json").fail(function(){
            alert("❌ Server error while deleting application.");
        });
    }
});

// Prevent duplicate event bindings
$(document).off("click", ".add_player");

// 🔹 ADD PLAYER BUTTON
$(document).on("click", ".add_player", function(){
    $("#Mymodal_app_form").modal();
    $(".btn_check_qualification").text("Check Qualification").show();
    $(".a_qualification").hide();
    $(".hidden_id").val("<?php echo $id ?>");

    $.get("../list.php?s=player_list", function(res){
        let select = $("#player");
        if(res.status){
            select.empty().append(`<option value="null" disabled selected>--Select--</option>`);
            for (let i = 0; i < res.data.length; i++) {
                const data = res.data[i];
                select.append(`<option value="${data.prof_id}">${data.fullname}</option>`);
            }
        } else {
            select.empty().append(`<option value="null" disabled selected>No more available players</option>`);
        }
    }, "json");
});
</script>

