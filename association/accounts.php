<?php 
include "../conn.php";
session_start();

$ass = $_SESSION['association_id'] ?? 0;
if(!$ass){
    echo "Restricted Area";
    exit;
}
?>
<div class="main">
    <div class="head">
        <h2>Accounts</h2>
    </div>
    <div class="body row">
        <!-- Active Players -->
        <div class="col-md-7 table_body">
            <div class="card">
                <div class="card-header">
                    <h4>Active Players</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Fullname</th>
                                <th class="text-center">Option</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Active players with prepared statement
                            $stmt = $con->prepare("
                                SELECT u.user_id, u.username,
                                       CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name, p.suffix) AS fullname
                                FROM tbl_association_players AS pl
                                LEFT JOIN tbl_user AS u ON pl.user_id = u.user_id
                                LEFT JOIN tbl_profile AS p ON pl.user_id = p.user_id
                                WHERE u.status = ? AND u.user_type = 3 AND pl.ass_id = ?
                            ");
                            $status = 1;
                            $stmt->bind_param("ii", $status, $ass);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while($row = mysqli_fetch_assoc($result)){
                            ?>
                            <tr row_id='<?php echo $row['user_id'] ?>'>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                <td class="text-center" data_id="<?php echo $row['user_id'] ?>">
                                    <button class="btn btn-sm btn-info review_request">View</button>
                                </td>
                            </tr>
                            <?php } 
                            $stmt->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Verifications -->
        <div class="col-md-5 verify">
            <div class="card">
                <div class="card-header">
                    <h4>Verifications</h4>
                </div>
                <div class="card-body">
                    <?php 
                    // Pending players (prepared statement for consistency)
                    $stmt2 = $con->prepare("
                        SELECT u.user_id,
                               CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name, p.suffix) AS fullname
                        FROM tbl_association_players AS pl
                        LEFT JOIN tbl_user AS u ON pl.user_id = u.user_id
                        LEFT JOIN tbl_profile AS p ON pl.user_id = p.user_id
                        WHERE u.status = ? AND u.user_type = 3 AND pl.ass_id = ?
                    ");
                    $pending = 0;
                    $stmt2->bind_param("ii", $pending, $ass);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();

                    if(mysqli_num_rows($result2) > 0){
                    ?>
                    <table class="table table-sm table-hover verification-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($row = mysqli_fetch_assoc($result2)){ ?>
                            <tr>
                                <td><?= htmlspecialchars($row['fullname']); ?></td>
                                <td class="text-center" data_id="<?= $row['user_id'] ?>">
                                    <button class='btn btn-sm btn-primary review_request'>Review</button>
                                    <button class='btn btn-sm btn-success verify_request'>Verify</button>
                                    <button class='btn btn-sm btn-danger reject_request'>Reject</button>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <?php 
                    } else {
                        echo "<p class='no_v text-muted'>No verifications.</p>";
                    }
                    $stmt2->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>


<style>
.main {
    padding: 15px;
}
.head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 15px;
}
.head h2 {
    font-size: 22px;
    font-weight: 600;
    color: #333;
    margin: 0;
}
.card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    margin-bottom: 15px;
}
.card-header {
    padding: 10px 15px;
    background: #f9f9f9;
    border-bottom: 1px solid #e0e0e0;
}
.card-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}
.card-body {
    padding: 15px;
}
.table th {
    background: #f5f5f5;
    font-weight: 600;
    color: #555;
}
.table td {
    vertical-align: middle;
}
.verification-table td {
    text-align: center;
    white-space: nowrap; /* keeps all buttons in one line */
}

.verification-table td .btn {
    margin: 0 4px;       /* adds space between buttons */
    display: inline-block;
    vertical-align: middle;
}

.verification-table tr:hover {
    background: #fafafa;
}
.btn-sm {
    padding: 4px 10px;
    font-size: 13px;
    border-radius: 4px;
    font-weight: 500;
}
.no_v {
    margin: 10px 0;
    font-style: italic;
}
</style>

<script>
$(".table").dataTable();
</script>
