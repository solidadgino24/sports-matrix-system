<?php
include "conn.php";
ob_start();
session_start(); 
$error = "";
$uname = "";
$password = "";
$confirm_password = "";

if(isset($_POST['signup'])) {
    extract($_POST);
    $dateToday = date('Y-m-d H:i:s');

    if($password!=$confirm_password){
        $error = "password does not match";
    }else{
        $u = $con->query("SELECT username FROM `tbl_user` where username = '$uname'");

        if (mysqli_num_rows($u) > 0) {
            $error = "Username already exist";
        }else{
            $password =  password_hash($password, PASSWORD_DEFAULT); 
            $query = "INSERT INTO `tbl_user`(`username`, `password`, `user_type`,`date_added`) VALUES('$uname','$password','$user_type','$dateToday')";
            if($con->query($query)){
                $_SESSION['session_username'] = $uname;
                $_SESSION['session_type'] = $user_type;

                if($user_type == 1){
                    header("location:admin/index.php");
                    die();
                }

                echo "<script>alert('Sign up success.')</script>";
            }else{
                echo "<script>alert('Failed to sign up')</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="icons/ico.png"/>
    <title>Sign up</title>
    <link href="css/pages/login.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <h2>Gate Management System</h2>
        <form action="#" method="POST">
            <label class="error"><?php echo $error ?></label>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="uname" placeholder="Enter your username" required autofocus="" value="<?php echo $uname ?>">
            </div>
            <div class="form-group">
                <label for="user_type">Type</label>
                <select name="user_type" id="user_type" required autofocus="">
                    <option value="" disabled>Select Option</option>
                    <option value="1">Admin</option>
                    <option value="2">Coach</option>
                    <option value="3">Scorer</option>
                </select>    
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required autofocus="" value="<?php echo $password ?>">
            </div>
            <div class="form-group">
                <label for="password">Confirm Password</label>
                <input type="password" id="password" name="confirm_password" placeholder="Confirm your password" required autofocus="" value="<?php echo $confirm_password ?>">
            </div>
            <button type="submit" class="btn" name="signup">Sign Up</button>
        </form>
        <div class="form-footer">
            <p>Already have account? <a href="login.php">log in here</a></p>
        </div>
    </div>
</body>
</html>
