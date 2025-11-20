<?php  
include "../conn.php";
session_start(); 

$session_username = $_SESSION['session_username'] ?? '';
$session_user_type = $_SESSION['session_type'] ?? '';

if($session_user_type != 3){
    header("location:../login.php?e=Restricted Area");
    exit;
}

$user_result = $con->query("SELECT * FROM tbl_user WHERE username='$session_username' LIMIT 1");

if(!$user_result || mysqli_num_rows($user_result) === 0){
    // No user found, redirect to login
    session_destroy();
    header("Location: ../login.php?e=User not found");
    exit;
}

$user = mysqli_fetch_assoc($user_result);
$user_id = $user['user_id'];


// Initialize variables
$first_name = $middle_name = $last_name = $suffix = $birthday = $contact = $email = $association = $program = $year_level = "";
$opt1 = $opt2 = "";
$img = "#";
$error = "";

// Fetch existing profile info
$sql = $con->query("SELECT p.*, u.status, u.email AS user_email, S.ass_id AS association, S.prog_id AS program, S.year_level 
                    FROM tbl_profile AS p
                    LEFT JOIN tbl_user AS u ON p.user_id=u.user_id
                    LEFT JOIN tbl_association_players AS S ON u.user_id=S.user_id
                    WHERE u.username='$session_username'");

$status = "secondary";
$char_status = "Not Verified";

if(mysqli_num_rows($sql) > 0){
    $row = mysqli_fetch_assoc($sql);
    $first_name = $row['first_name'] ?? '';
    $middle_name = $row['middle_name'] ?? '';
    $last_name = $row['last_name'] ?? '';
    $suffix = $row['suffix'] ?? '';
    $birthday = $row['birthday'] ?? '';
    $contact = $row['contact'] ?? '';
    $email = $row['user_email'] ?? '';
    $association = intval($row['association'] ?? 0);
    $program = intval($row['program'] ?? 0);
    $year_level = $row['year_level'] ?? '';
    $img = isset($row['profile']) ? "data:image/png;base64,".$row['profile'] : "#";

    $opt1 = ($row['gender']==1) ? "selected" : "";
    $opt2 = ($row['gender']==2) ? "selected" : "";

    if($row['status'] == 0){
        $status = "warning"; $char_status = "Pending";
    } elseif($row['status'] == 2){
        $status = "danger"; $char_status = "Rejected, Try changing your details..";
    } else {
        $status = "success"; $char_status = "Verified, Redirecting..";
    }
}

// Handle form submission
// Handle form submission
if(isset($_POST['save'])){
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $association = intval($_POST['association'] ?? 0);
    $program = intval($_POST['program'] ?? 0);
    $year_level = trim($_POST['year_level'] ?? '');

    // Validation
    if(!$first_name || !$last_name || !$birthday || !$gender || !$contact || !$email || !$association || !$program || !$year_level){
        $error = "All required fields must be filled.";
    } else {
        // Profile image
        $profile = null;
        if(isset($_FILES['profile']) && $_FILES['profile']['size'] > 0){
            $profile = base64_encode(file_get_contents($_FILES['profile']['tmp_name']));
        }

        // Update/Insert tbl_profile
        $check_profile = $con->query("SELECT prof_id FROM tbl_profile WHERE user_id='$user_id' LIMIT 1");
        if(mysqli_num_rows($check_profile) > 0){
            // UPDATE existing profile
            if($profile){
                $stmt = $con->prepare("UPDATE tbl_profile SET first_name=?, middle_name=?, last_name=?, suffix=?, birthday=?, gender=?, contact=?, email=?, prog_id=?, year_level=?, profile=? WHERE user_id=?");
                $stmt->bind_param("ssssssssisss", $first_name, $middle_name, $last_name, $suffix, $birthday, $gender, $contact, $email, $program, $year_level, $profile, $user_id);
            } else {
                $stmt = $con->prepare("UPDATE tbl_profile SET first_name=?, middle_name=?, last_name=?, suffix=?, birthday=?, gender=?, contact=?, email=?, prog_id=?, year_level=? WHERE user_id=?");
                $stmt->bind_param("ssssssssiss", $first_name, $middle_name, $last_name, $suffix, $birthday, $gender, $contact, $email, $program, $year_level, $user_id);
            }
            $stmt->execute();
            $stmt->close();
        } else {
            // INSERT new profile
            $stmt = $con->prepare("INSERT INTO tbl_profile (user_id, first_name, middle_name, last_name, suffix, birthday, gender, contact, email, prog_id, year_level, profile) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssissss", $user_id, $first_name, $middle_name, $last_name, $suffix, $birthday, $gender, $contact, $email, $program, $year_level, $profile);
            $stmt->execute();
            $stmt->close();
        }

        // Sync email to tbl_user
        $stmt_email = $con->prepare("UPDATE tbl_user SET email=? WHERE user_id=?");
        $stmt_email->bind_param("si", $email, $user_id);
        $stmt_email->execute();
        $stmt_email->close();

        // Update/Insert tbl_association_players (optional, keep syncing)
        $check_assoc = $con->query("SELECT user_id FROM tbl_association_players WHERE user_id='$user_id' LIMIT 1");
        if(mysqli_num_rows($check_assoc) > 0){
            $stmt2 = $con->prepare("UPDATE tbl_association_players SET ass_id=?, prog_id=?, year_level=? WHERE user_id=?");
            $stmt2->bind_param("iiis", $association, $program, $year_level, $user_id);
            $stmt2->execute();
            $stmt2->close();
        } else {
            $stmt2 = $con->prepare("INSERT INTO tbl_association_players (user_id, ass_id, prog_id, year_level) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("iiis", $user_id, $association, $program, $year_level);
            $stmt2->execute();
            $stmt2->close();
        }

        header("Location: verify.php?success=1");
        exit;
    }
}

?>

<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Player Verification</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: #f4f6f9; }
    .verification-card { margin: 40px auto; max-width: 850px; background: #fff; border-radius: 12px; padding: 25px; box-shadow:0 4px 15px rgba(0,0,0,0.08);}
    .profile-preview { width:180px;height:180px;border-radius:50%;overflow:hidden;border:4px solid #e9ecef;margin:auto;display:flex;align-items:center;justify-content:center;background:#f8f9fa;}
    .profile-preview img { width:100%;height:100%;object-fit:cover;}
    .status-badge { font-size:14px;padding:8px 14px;border-radius:50px;}
</style>
</head>
<body>
<div class="container">
<div class="verification-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">Player Profile Verification</h4>
        <span class="badge bg-<?php echo $status ?> status-badge"><?php echo $char_status ?></span>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger text-center py-2"><?php echo $error ?></div>
    <?php endif; ?>

    <form action="#" method="POST" enctype="multipart/form-data" class="row g-4">
        <div class="col-md-4 text-center">
            <div class="profile-preview mb-3">
                <img src="<?php echo $img ?>" alt="Profile">
            </div>
            <input type="file" class="form-control" id="logo" name="profile" accept=".png,.jpg,.jpeg">
        </div>

        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-md-6"><label>First Name</label><input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required></div>
                <div class="col-md-6"><label>Middle Name / Initial</label><input type="text" class="form-control" name="middle_name" value="<?php echo htmlspecialchars($middle_name); ?>"></div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-6"><label>Last Name / Surname</label><input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required></div>
                <div class="col-md-6"><label>Suffix</label><input type="text" class="form-control" name="suffix" value="<?php echo htmlspecialchars($suffix); ?>" placeholder="Optional"></div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label>Gender</label>
                    <select name="gender" class="form-select" required>
                        <option selected disabled>--SELECT--</option>
                        <option value="1" <?php echo $opt1 ?>>Male</option>
                        <option value="2" <?php echo $opt2 ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Birthdate</label>
                    <input type="date" class="form-control" name="birthday" value="<?php echo htmlspecialchars($birthday); ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" required>
                </div>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label>Contact Number</label>
                    <input type="text" 
       class="form-control" 
       name="contact" 
       id="contact" 
       value="<?php echo htmlspecialchars($contact); ?>" 
       placeholder="09XXXXXXXXX" 
       maxlength="11" 
       pattern="^09\d{9}$" 
       title="Enter a valid 11-digit number starting with 09" 
       required>

                <div class="col-md-12">
    <label>Email</label>
    <input type="email" class="form-control w-100" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
</div>

            </div>
            <div class="mt-3">
                <label>Association</label>
                <select name="association" id="association" class="form-select" required>
                    <option selected disabled>--SELECT--</option>
                    <?php
                        $assocs = $con->query("SELECT ass_id, name FROM tbl_association");
                        while($a = mysqli_fetch_assoc($assocs)){
                            $sel = ($association==$a['ass_id'])?"selected":"";
                            echo "<option value='{$a['ass_id']}' $sel>{$a['name']}</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="mt-3">
                <label>Program</label>
                <select name="program" id="program" class="form-select" required>
                    <option selected disabled>--SELECT PROGRAM--</option>
                    <?php
                        if($association){
                            $progs = $con->query("SELECT prog_id, prog_name FROM tbl_programs WHERE ass_id='$association'");
                            while($p = mysqli_fetch_assoc($progs)){
                                $sel = ($program==$p['prog_id'])?"selected":"";
                                echo "<option value='{$p['prog_id']}' $sel>{$p['prog_name']}</option>";
                            }
                        }
                    ?>
                </select>
            </div>
            <div class="mt-3">
                <label>Year Level</label>
                <select name="year_level" class="form-select" required>
                    <option selected disabled>--SELECT YEAR LEVEL--</option>
                    <?php
                        $years = ["1st Year","2nd Year","3rd Year","4th Year","5th Year"];
                        foreach($years as $y){
                            $sel = ($year_level==$y)?"selected":"";
                            echo "<option value='$y' $sel>$y</option>";
                        }
                    ?>
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            <button class="btn btn-success" name="save">Save</button>
        </div>
    </form>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$("#logo").change(function(){
    var image = this.files[0];
    let logo = $(".profile-preview img");
    logo.hide();
    var reader = new FileReader();
    reader.onload = function(e){
        logo.show();
        logo.attr("src", e.target.result);
    }
    reader.readAsDataURL(image);
});

$("#association").on("change", function(){
    var ass_id = $(this).val();
    if(!ass_id) return;
    $.ajax({
        url: "load_programs.php",
        method: "GET",
        data: { ass_id: ass_id },
        success: function(response){
            $("#program").html(response);
        },
        error: function(){
            $("#program").html('<option value="">Error loading programs</option>');
        }
    });
});
$("#contact").on("input", function() {
    // Allow only numbers
    this.value = this.value.replace(/[^0-9]/g, "");
    
    // Limit to 11 digits
    if (this.value.length > 11) {
        this.value = this.value.slice(0, 11);
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
