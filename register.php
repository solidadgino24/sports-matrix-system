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
    if(isset($user_type)){
        echo"<script>alert(".$user_type.")</script>";
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
                        header("location:admin/");
                        die();
                    }else if($user_type == 2){
                        header("location:association/");
                        die();
                    }else if($user_type == 3){
                        header("location:player/");
                        die();
                    }
                    else if($user_type == 4){
                        header("location:scorer/");
                        die();
                    }

                    echo "<script>alert('Sign up success.')</script>";
                }else{
                    echo "<script>alert('Failed to sign up')</script>";
                }
            }
        }    
    }else{
        $error = "Please select user type";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="login.css">
</head>
<style>
    * {
  padding: 0;
  margin: 0;
  box-sizing: border-box;
}

:root {
  --second-color: #b9b9b9;
  --primary-color: #b5afaf;
  --black-color: #020101;
}

body {
  background: url('chmsubg.jpg') center/cover no-repeat fixed;
  font-family: "Poppins", sans-serif;
}

.container {
  display: flex;
  width: 100%;
  min-height: 100vh;
  justify-content: center;
  align-items: center;
  background-color: rgba(0, 0, 0, 0.4);
  padding: 20px;
}

/* ✨ Registration Card */
.register_container {
  position: relative;
  backdrop-filter: blur(25px);
  background-color: rgba(255, 255, 255, 0.05);
  width: 100%;
  max-width: 420px;
  border: 2px solid var(--primary-color);
  padding: 7em 2.5em 4em 2.5em;
  border-radius: 15px;
  box-shadow: 0px 0px 10px 2px hsla(127, 98%, 48%, 0.2);
  color: var(--second-color);
}

/* Title section */
.register_title {
  position: absolute;
  left: 50%;
  top: 0;
  transform: translateX(-50%);
  display: flex;
  justify-content: center;
  align-items: center;
  width: 140px;
  background-color: var(--primary-color);
  border-radius: 0 0 20px 20px;
  height: 70px;
}

.register_title span {
  color: var(--black-color);
  font-size: 30px;
  font-weight: bold;
}

/* Input Wrappers */
.input_wrapper {
  position: relative;
  display: flex;
  flex-direction: column;
  margin: 20px 0;
}

.input_field {
  width: 100%;
  font-size: 16px;
  height: 55px;
  color: var(--second-color);
  background: transparent;
  padding-inline: 20px 54px;
  border-radius: 30px;
  border: 2px solid var(--primary-color);
  outline: none;
  appearance: none;
}

.label {
  position: absolute;
  top: 15px;
  left: 20px;
  transition: 0.2s;
  font-size: 16px;
}

.input_field:focus ~ .label,
.input_field:not(:placeholder-shown) ~ .label,
select:focus ~ .label {
  font-size: 14px;
  top: -10px;
  left: 20px;
  background-color: var(--primary-color);
  color: var(--black-color);
  border-radius: 30px;
  padding: 0 10px;
}

/* Dropdown icon */
.icon {
  position: absolute;
  font-size: 20px;
  top: 18px;
  right: 25px;
  color: var(--second-color);
}

/* Buttons and Text */
.input-submit {
  background: #fff6f6;
  width: 100%;
  height: 50px;
  font-size: 16px;
  border-radius: 30px;
  cursor: pointer;
  font-weight: 500;
  border: none;
  transition: background 0.2s;
}

.input-submit:hover {
  background: var(--second-color);
}

.signup {
  text-align: center;
  margin-top: 10px;
  font-size: 15px;
}
.signup a {
  font-weight: 500;
  color: var(--second-color);
}
.signup a:hover {
  text-decoration: underline;
}

/* Error message */
.error {
  color: red;
  text-align: center;
  display: block;
  font-size: 18px;
  font-weight: bold;
  font-family: Arial, sans-serif;
}

/* Select dropdown styles */
option {
  color: black;
  background: #fff;
  font-size: 16px;
}

/* 📱 Responsive Design */
@media (max-width: 768px) {
  .register_container {
    padding: 6em 2em 3em 2em;
  }
  .register_title span {
    font-size: 26px;
  }
  .input_field {
    height: 50px;
    font-size: 15px;
  }
}

@media (max-width: 480px) {
  .register_container {
    max-width: 340px;
    padding: 5em 1.5em 2em 1.5em;
    border-radius: 12px;
  }
  .register_title {
    width: 120px;
    height: 60px;
  }
  .register_title span {
    font-size: 22px;
  }
  .label {
    font-size: 14px;
  }
  .input_field {
    height: 45px;
    font-size: 14px;
    padding-inline: 15px 44px;
  }
  .icon {
    font-size: 18px;
    top: 15px;
    right: 20px;
  }
  .input-submit {
    font-size: 15px;
    height: 45px;
  }
  .error {
    font-size: 16px;
  }
}

</style>
<body>
    <div class="container">
        <div class="register_container">
            <div class="register_title">
                <span>Register</span>
            </div>
            <!-- Login Form -->
            <form action="#" method="POST">
                <label class="error"><?php echo $error ?></label>
                <div class="input_wrapper">
                    <input type="text" id="user" name="uname" value="<?php echo $uname ?>" class="input_field" required aria-label="Username">
                    <label for="user" class="label">Username</label>
                    <i class="fa-solid fa-user icon"></i>
                </div>
                <div class="input_wrapper">
                    <input type="password" id="pass" name="password" value="<?php echo $password ?>" class="input_field" required aria-label="Password">
                    <label for="pass" class="label">Password</label>
                    <i class="fa-solid fa-lock icon"></i>
                </div>
                <div class="input_wrapper">
                    <input type="password" id="pass" name="confirm_password" value="<?php echo $confirm_password ?>" class="input_field" required aria-label="Password">
                    <label for="pass" class="label">Confirm Password</label>
                    <i class="fa-solid fa-lock icon"></i>
                </div>
                <div class="input_wrapper">
                    <select name="user_type" id="user_type" class="input_field" required aria-label="Type">
                        <option selected disabled>--Select--</option>
                        <?php 
                            $query = $con->query("SELECT * FROM tbl_user_type WHERE type_id != '1'");
                            while($row = mysqli_fetch_assoc($query)){
                        ?>
                        <option value="<?php echo $row['type_id'] ?>"><?php echo $row['type'] ?></option>
                        <?php } ?>
                    </select>
                    <label for="pass" class="label">Select User Type</label>
                    <i class="fa-solid fa-lock icon"></i>
                </div>
                <input type="submit" class="input-submit" name="signup">
                <div class="signup">
                    <span>Have an account? <a href="login.php">Log in</a></span>
                </div>
            </form>
        </div>
    </div>
<style>
option{
    color: black;
    background: #fff;
    font-size: 20px;
}    
</style>
</body>
</html>
