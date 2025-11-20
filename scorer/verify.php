<?php 
include "../conn.php";

session_start(); 
$session_username = $_SESSION['session_username'];
$session_user_type =  $_SESSION['session_type'];
if($session_user_type != 4){
    header("location:../login.php?e=Restricted Area");
}else{
    $user = $con->query("SELECT * FROM tbl_user WHERE username ='$session_username'");
    $user = mysqli_fetch_assoc($user);
}

$user_id = $user['user_id'];
$fullname ="";
$birthday ="";
$contact ="";
$email ="";
$opt1 = "";
$opt2 = "";
$img = "#";
$error = "";
$required = "required";

if(isset($_POST['save'])){
    extract($_POST);
    $sql = $con->query("SELECT prof_id FROM tbl_profile WHERE user_id='$user_id' LIMIT 1");
    if(mysqli_num_rows($sql) > 0){
        if($_FILES['profile']['size']>0){
            $profile = base64_encode(file_get_contents($_FILES['profile']['tmp_name']));
            $con->query("UPDATE tbl_profile SET profile='$profile' WHERE user_id='$user_id'");
        }
        $con->query("UPDATE tbl_profile SET fullname='$fullname',birthday='$birthday',gender='$gender',contact='$contact',email='$email' WHERE user_id ='$user_id'");
    }else{
        if($_FILES['profile']['size']==0){
            $error ="Please upload image";
        }else if(!isset($_POST['gender'])){
            $error ="Please select gender";
        }else{
            $profile = base64_encode(file_get_contents($_FILES['profile']['tmp_name']));
            $con->query("INSERT INTO tbl_profile(user_id,fullname,profile,birthday,gender,contact,email) VALUES('$user_id','$fullname','$profile','$birthday','$gender','$contact','$email')");
        }
    }
}
$sql = $con->query("SELECT p.*,u.status FROM tbl_profile AS p LEFT JOIN tbl_user AS u ON p.user_id=u.user_id WHERE u.username = '$session_username'");

$status = "default";
$char_status = "Verify";
if(mysqli_num_rows($sql) > 0){
    extract($row = mysqli_fetch_assoc($sql));
    $img = "data:image/png;base64,".$profile;
    $required = "";
    if($gender == "1"){
        $opt1 = "selected";
    }else if($gender == "2"){
        $opt2 = "selected";
    }
    if($row['status'] == 0 ){
        $status = "warning";
        $char_status = "Pending";
    }else{
        $status = "success";
        $char_status = "Verified, Redirecting..";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYSTEM | Verify</title>
    <?php include "header.html";?>
    <style>
        .body{
            padding: 10px;
            margin-top:10px;
        }
        .preview{
            border: 1px solid #cccccc;
            border-radius: 5px;
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .error{
            color: red;
            margin-bottom: 10px;
            text-align: center;
            font-size: 16px;
        }
        .preview > img{
            min-width: inherit;
            height: 100%;
        }
    </style>
</head>
<body>
    <div class="col-md-6 col-md-offset-3 body">
        <form action="#" method="POST" enctype="multipart/form-data">
            <legend>Profile Verification</legend>
            <p class="label label-<?php echo $status ?>">Status: <?php echo $char_status ?></p>
            <div class="clearfix"></div>
            <br>
            <div class="error"><?php echo $error ?></div>
            <div class="col-md-4">
                <div class="preview">
                    <img src="<?php echo $img ?>" alt="">
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label>Fullname</label>
                    <input type="text" class="form-control" name="fullname" value="<?php echo $fullname ?>" required>
                </div>
                <div class="form-group">
                    <label>Profile Image</label>
                    <input type="file" id="logo" class="form-control" name="profile" <?php echo $required ?> accept=".png,.jpg,.jpeg">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" id="" class="form-control" required>
                        <option selected disabled>--SELECT--</option>
                        <option value ='1' <?php echo $opt1 ?> >Male</option>
                        <option value ='2' <?php echo $opt2 ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Birthdate</label>
                    <input type="date" class="form-control" name="birthday" value="<?php echo $birthday ?>" required>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="number" class="form-control" name="contact" value="<?php echo $contact ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo $email ?>" required>
                </div>
            </div>
            <div class="btns pull-right">
            <a href="logout.php" class="btn btn-danger">log out</a>
            <button class="btn btn-success" name="save">
                <span class="glyphicon glyphicon-floppy-disk"></span>
                Save
            </button>
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
<script>
    $("#logo").change(function(){
            var image = this.files[0];
            let logo =$(".preview > img");
            logo.hide();
            var reader = new FileReader();

            reader.onload = function(e){
                logo.show("100");
                logo.attr("src",e.target.result);
            }
            reader.readAsDataURL(image);
        });
    </script>
</body>
</html>
