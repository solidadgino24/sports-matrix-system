<?php
include_once("../conn.php");
session_start();

$session_user_id = $_SESSION['session_user_id'];

$user = $con->query("SELECT ass_id FROM tbl_association_players WHERE user_id ='$session_user_id'");
$user = mysqli_fetch_assoc($user);
$ass_id = $user['ass_id'];

$user = $con->query("SELECT prof_id FROM tbl_profile WHERE user_id ='$session_user_id'");
$user = mysqli_fetch_assoc($user);
$prof_id = $user['prof_id'];

$id = $_GET['id'];
$_SESSION['tournament_code'] = $id;

$sql = $con->query("SELECT t.maximum, t.minimum, t.tourna_id, s.img, s.name AS sport_name, s.rules, gm.name, c.category, gm.players 
                    FROM tbl_tournament AS t 
                    LEFT JOIN tbl_game_modes AS gm ON t.game_id = gm.game_id
                    LEFT JOIN tbl_sports AS s ON gm.sport_id = s.sport_id
                    LEFT JOIN tbl_game_mode_cat AS c ON gm.gm_cat_id = c.gm_cat_id
                    WHERE t.tourna_id = '$id'");
$row = mysqli_fetch_assoc($sql);

$query = $con->query("SELECT p.profile, p.fullname, p.gender, ta.jersey_number, ta.status, a.name AS association
                      FROM tbl_tourna_application AS ta 
                      LEFT JOIN tbl_profile AS p ON ta.prof_id = p.prof_id
                      LEFT JOIN tbl_association_players AS ap ON p.user_id = ap.user_id
                      LEFT JOIN tbl_association AS a ON ap.ass_id = a.ass_id
                      WHERE ta.tourna_id = '$id' AND ap.ass_id = '$ass_id'");

$check_user = $con->query("SELECT app_id, status 
                           FROM tbl_tourna_application 
                           WHERE prof_id = '$prof_id' 
                           AND tourna_id = '$id' 
                           AND status IN (0,1)");
?>
<div class="main container-fluid">
    <div class="head d-flex flex-wrap justify-content-between align-items-center">
        <div class="d-flex flex-column flex-md-row align-items-center gap-2">
            <h2 class="text-center text-md-start mb-0"><?php echo $row['name'] . " (" . $row['sport_name'] . ")" ?></h2>
            <?php if (!empty($row['rules'])) { ?>
    <a href="download_rules.php?id=<?php echo $id; ?>" 
       class="btn btn-outline-primary btn-sm ms-md-3">
       <i class="bi bi-file-earmark-arrow-down"></i> Download Rules
    </a>
<?php } ?>

        </div>

        <?php if (mysqli_num_rows($check_user) == 0) { ?>
            <button class='btn btn-secondary appy_tourna'>Apply</button>
        <?php } else { 
            $status_check = $con->query("SELECT status FROM tbl_tourna_application WHERE prof_id='$prof_id' AND tourna_id='$id'");
            $status_row = mysqli_fetch_assoc($status_check);
            if ($status_row['status'] == 2) {
                echo "<span class='text-danger fw-bold small text-center'>Application Rejected — You may apply again.</span>";
            }
        } ?>
    </div>

    <div class="body mt-3 table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="thead-dark">
                <tr>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Jersey #</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                <tr>
                    <td><img src="data:image/png;base64,<?php echo $row['profile'] ?>" class="profile-img img-fluid" alt=""></td>
                    <td><?php echo $row['fullname'] ?></td>
                    <td><?php echo ($row['gender'] == 1) ? "Male" : "Female" ?></td>
                    <td><?php echo $row['jersey_number'] ?></td>
                    <td>
                        <?php
                            if ($row['status'] == 0) echo "<span class='badge bg-warning text-dark'>Pending</span>";
                            elseif ($row['status'] == 1) echo "<span class='badge bg-success'>Accepted</span>";
                            elseif ($row['status'] == 2) echo "<span class='badge bg-danger'>Rejected</span>";
                            else echo "Unknown";
                        ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.head h2 {
    font-size: 1.5rem;
    font-weight: bold;
}
.head {
    display: flex;
    justify-content: space-between; /* Push title left, button right */
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.head button {
    background-color: seagreen;
    color: #fff;
    font-weight: 600;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    margin-left: auto; /* Push button to the right */
}

.head button:hover {
    background-color: mediumseagreen;
}

.profile-img {
    max-height: 60px;
    border-radius: 8px;
}
@media (max-width: 768px) {
    .head {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    .head h2 {
        font-size: 1.25rem;
    }
    .profile-img {
        max-height: 50px;
    }
    table {
        font-size: 0.85rem;
    }
    .table-responsive {
        overflow-x: auto;
    }
}
</style>

<script>
$(".table").dataTable();

$(".appy_tourna").click(function(){
    $("#Mymodal_app_form").modal();
    $(".btn_check_qualification").text("Check Qualification").show();
    $(".a_qualification").hide();
    $(".hidden_id").val("<?php echo $id ?>")
});
</script>
