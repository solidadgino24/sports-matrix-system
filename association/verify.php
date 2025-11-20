<?php 
include "../conn.php";

session_start(); 
$session_username = $_SESSION['session_username'] ?? '';
$session_user_type = $_SESSION['session_type'] ?? '';

if($session_user_type != 2){
    header("location:../login.php?e=Restricted Area");
    exit;
}

// Fetch user
$stmt = $con->prepare("SELECT * FROM tbl_user WHERE username=? LIMIT 1");
$stmt->bind_param("s", $session_username);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0){
    session_destroy();
    header("Location: ../login.php?e=User not found");
    exit;
}
$user = $result->fetch_assoc();
$user_id = $user['user_id'];
$stmt->close();

// Initialize variables
$fullname = $birthday = $contact = $email = "";
$gender = "";
$opt1 = $opt2 = "";
$img = "#";
$error = "";
$association = "";
$required = "required";

// Fetch existing profile + association info
$stmt = $con->prepare("
    SELECT p.*, u.status, u.email AS user_email, s.ass_id AS association
    FROM tbl_profile_ass AS p
    LEFT JOIN tbl_user AS u ON p.user_id=u.user_id
    LEFT JOIN tbl_association_staff AS s ON u.user_id=s.user_id
    WHERE u.user_id=? LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$status = "default";
$char_status = "Verify";

if($result->num_rows > 0){
    $row = $result->fetch_assoc();
    $fullname = $row['fullname'] ?? '';
    $birthday = $row['birthday'] ?? '';
    $contact = $row['contact'] ?? '';
    $email = $row['user_email'] ?? '';
    $gender = $row['gender'] ?? '';
    $association = $row['association'] ?? '';
    $img = isset($row['profile']) ? "data:image/png;base64,".$row['profile'] : "#";
    $required = "";

    $opt1 = ($gender==1) ? "selected" : "";
    $opt2 = ($gender==2) ? "selected" : "";

    if($row['status'] == 0){
        $status = "warning"; $char_status = "Pending";
    } elseif($row['status'] == 2){
        $status = "danger"; $char_status = "Rejected, Try changing your details..";
    } else {
        $status = "success"; $char_status = "Verified, Redirecting..";
    }
}
$stmt->close();

// Handle form submission
if(isset($_POST['save'])){
    $fullname = trim($_POST['fullname'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $association = $_POST['association'] ?? '';

    if(!$fullname || !$birthday || !$contact || !$email || !$gender || !$association){
        $error = "All required fields must be filled.";
    } else {
        $profile = null;
        if(isset($_FILES['profile']) && $_FILES['profile']['size'] > 0){
            $profile = base64_encode(file_get_contents($_FILES['profile']['tmp_name']));
        }

        // Check if profile exists
        $stmt = $con->prepare("SELECT prof_id FROM tbl_profile_ass WHERE user_id=? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $check = $stmt->get_result();
        $stmt->close();

        if($check->num_rows > 0){
            if($profile){
                $stmt = $con->prepare("UPDATE tbl_profile_ass SET fullname=?, birthday=?, gender=?, contact=?, email=?, profile=? WHERE user_id=?");
                $stmt->bind_param("ssssssi", $fullname, $birthday, $gender, $contact, $email, $profile, $user_id);
            } else {
                $stmt = $con->prepare("UPDATE tbl_profile_ass SET fullname=?, birthday=?, gender=?, contact=?, email=? WHERE user_id=?");
                $stmt->bind_param("sssssi", $fullname, $birthday, $gender, $contact, $email, $user_id);
            }
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $con->prepare("INSERT INTO tbl_profile_ass (user_id, fullname, profile, birthday, gender, contact, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $user_id, $fullname, $profile, $birthday, $gender, $contact, $email);
            $stmt->execute();
            $stmt->close();
        }

        // Update or insert association staff
        $stmt = $con->prepare("SELECT user_id FROM tbl_association_staff WHERE user_id=? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $check_assoc = $stmt->get_result();
        $stmt->close();

        if($check_assoc->num_rows > 0){
            $stmt = $con->prepare("UPDATE tbl_association_staff SET ass_id=? WHERE user_id=?");
            $stmt->bind_param("ii", $association, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $con->prepare("INSERT INTO tbl_association_staff (ass_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $association, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        // Update email in tbl_user and reset status
        $stmt = $con->prepare("UPDATE tbl_user SET email=?, status='0' WHERE user_id=?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->close();

        header("Location: verify.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SYSTEM | Verify</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: #f4f6f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .verification-card {
      margin: 40px auto;
      max-width: 850px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      padding: 25px;
    }
    .profile-preview {
      width: 180px;
      height: 180px;
      border-radius: 50%;
      overflow: hidden;
      border: 4px solid #e9ecef;
      margin: auto;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f8f9fa;
    }
    .profile-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .status-badge {
      font-size: 14px;
      padding: 8px 14px;
      border-radius: 50px;
    }
    .form-section label {
      font-weight: 600;
      margin-bottom: 5px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="verification-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Profile Verification</h4>
      <span class="badge bg-<?php echo $status ?> status-badge">
        <?php echo $char_status ?>
      </span>
    </div>

    <?php if($error): ?>
      <div class="alert alert-danger text-center py-2"><?php echo $error ?></div>
    <?php endif; ?>

    <form action="#" method="POST" enctype="multipart/form-data" class="row g-4">
      <div class="col-md-4 text-center">
        <div class="profile-preview mb-3">
          <img src="<?php echo $img ?>" alt="Profile">
        </div>
        <input type="file" class="form-control" id="logo" name="profile" <?php echo $required ?> accept=".png,.jpg,.jpeg">
      </div>

      <div class="col-md-8 form-section">
        <div class="mb-3">
          <label>Full Name</label>
          <input type="text" class="form-control" name="fullname" value="<?php echo $fullname ?>" required>
        </div>

        <div class="row g-3">
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
            <input type="date" class="form-control" name="birthday" value="<?php echo $birthday ?>" required>
          </div>
        </div>

        <div class="row g-3 mt-1">
          <div class="col-md-6">
            <label>Contact Number</label>
            <input type="number" class="form-control" name="contact" value="<?php echo $contact ?>" required>
          </div>
          <div class="col-md-6">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo $email ?>" required>
          </div>
        </div>

        <div class="mt-3">
          <label>Association</label>
          <select name="association" class="form-select" required>
            <option selected disabled>--SELECT--</option>
            <?php 
              $sql = $con->query("SELECT ass_id,name FROM tbl_association");
              while($row = mysqli_fetch_assoc($sql)){
            ?>
            <option value ="<?php echo $row['ass_id'] ?>" <?= ($association==$row['ass_id'])? "selected":"" ?>>
              <?php echo $row['name'] ?>
            </option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        <button class="btn btn-success" name="save">
          <i class="bi bi-save"></i> Save
        </button>
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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
