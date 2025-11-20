<?php
include "../conn.php";

$id = intval($_GET['acc_id'] ?? 0);

// Fetch profile + user status + program + year level + association
$sql = $con->query("
    SELECT p.*, u.status, u.email AS user_email,
           ast.year_level, a.name AS association_name,
           pr.prog_name AS program_name
    FROM tbl_profile AS p
    LEFT JOIN tbl_user AS u ON p.user_id = u.user_id
    LEFT JOIN tbl_association_players AS ast ON p.user_id = ast.user_id
    LEFT JOIN tbl_association AS a ON ast.ass_id = a.ass_id
    LEFT JOIN tbl_programs AS pr ON ast.prog_id = pr.prog_id
    WHERE u.user_id = '$id'
");
$profile = mysqli_fetch_assoc($sql);

// Profile image
$img = !empty($profile['profile']) ? "data:image/png;base64,".$profile['profile'] 
                                    : "https://via.placeholder.com/180x180.png?text=No+Image";

// Gender display
$gender_text = ($profile['gender'] ?? 0) == 1 ? "Male" : "Female";

// Fullname formatting
$fullname = trim(($profile['first_name'] ?? '') . ' ' . 
                 ($profile['middle_name'] ?? '') . ' ' . 
                 ($profile['last_name'] ?? '') . ' ' . 
                 ($profile['suffix'] ?? ''));
$fullname = preg_replace('/\s+/', ' ', $fullname);
$email = $profile['user_email'] ?? 'N/A';
$contact = $profile['contact'] ?? 'N/A';
$association_name = $profile['association_name'] ?? 'N/A';
$program_name = $profile['program_name'] ?? 'N/A';
$year_level = $profile['year_level'] ?? 'N/A';
?>

<div class="main">
    <div class="head">
        <h2>Review Account</h2>
        <div class="btns">
            <button class='btn btn-secondary back_to_account'>Back</button>
            <?php if(($profile['status'] ?? 0) == 0): ?>
                <button class='btn btn-success verify_this_account'>Verify</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="body row">
        <div class="col-md-4">
            <div class="preview">
                <img src="<?= $img ?>" alt="">
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <label>Fullname</label>
                <p class='form-control'><?= htmlspecialchars($fullname); ?></p>
            </div>
            <div class="form-group">
                <label>Association</label>
                <p class='form-control'><?= htmlspecialchars($association_name); ?></p>
            </div>
            <div class="form-group">
                <label>Program</label>
                <p class='form-control'><?= htmlspecialchars($program_name); ?></p>
            </div>
            <div class="form-group">
                <label>Year Level</label>
                <p class='form-control'><?= htmlspecialchars($year_level); ?></p>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <p class='form-control'><?= htmlspecialchars($gender_text); ?></p>
            </div>
            <div class="form-group">
                <label>Birthdate</label>
                <p class='form-control'>
                    <?= isset($profile['birthday']) ? date("F j, Y", strtotime($profile['birthday'])) : 'N/A'; ?>
                </p>
            </div>
            <div class="form-group">
                <label>Contact / Email</label>
                <p class='form-control'><?= htmlspecialchars($contact.' / '.$email); ?></p>
            </div>
        </div>
    </div>
</div>

<style>
.head{
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #272c33;
    margin-bottom:15px;
}
.preview{
    border: 1px solid #cccccc;
    border-radius: 5px;
    height: 200px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.preview > img{
    min-width: inherit;
    height: 100%;
}
.place {
    color: #0f3bcf;
    font-weight: bold;
}
.events > div > ul{
    list-style-type:none;
    padding:0;
    display:flex;
    flex-wrap:wrap;
    gap:20px;
}
.events > div > ul li{
    border:1px solid #ccc;
    border-radius:5px;
    padding:10px;
    width:250px;
}
</style>
