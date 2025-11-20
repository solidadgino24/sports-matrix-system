<?php 
include "conn.php";
ob_start();
session_start();
session_destroy();
session_start();

$error = (isset($_GET['e'])) ? $_GET['e'] : "";
$name = "";
$password = "";

if(isset($_POST['login'])){
    $name = trim($_POST['username']);
    $password = trim($_POST['password']);

    $userQry = $con->prepare("SELECT * FROM tbl_user WHERE username = ? LIMIT 1");
    $userQry->bind_param("s", $name);
    $userQry->execute();
    $result = $userQry->get_result();

    if($result->num_rows == 1){
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {

            $_SESSION['session_user_id'] = $row['user_id'];
            $_SESSION['session_username'] = $row['username'];
            $_SESSION['session_type'] = $row['user_type'];

            // ✅ If user is using a temporary password, force password change
            if (isset($row['is_temp']) && $row['is_temp'] == 1) {
                header("Location: change_password.php");
                exit;
            }

            // ✅ Redirect based on user type
            switch ($row['user_type']) {
                case 1:
                    header("location:admin/");
                    break;
                case 2:
                    header("location:association/");
                    break;
                case 3:
                    header("location:player/");
                    break;
                case 4:
                    header("location:scorer/");
                    break;
                default:
                    $error = "Unknown user type.";
                    break;
            }
            exit;

        } else {
            $error = "Invalid Password";
        }
    } else {
        $error = "Username not found";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <link rel="icon" href="icons/ico.png">
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

.login_container {
  position: relative;
  backdrop-filter: blur(25px);
  width: 100%;
  max-width: 420px;
  border: 2px solid var(--primary-color);
  padding: 7em 2.5em 4em 2.5em;
  border-radius: 15px;
  box-shadow: 0px 0px 10px 2px hsla(127, 98%, 48%, 0.2);
  color: var(--second-color);
  background-color: rgba(255, 255, 255, 0.05);
}

/* Title section */
.login_title {
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
.login_title span {
  color: var(--black-color);
  font-size: 30px;
  font-weight: bold;
}

/* Inputs */
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
}

.label {
  position: absolute;
  top: 15px;
  left: 20px;
  transition: 0.2s;
  font-size: 16px;
}

.input_field:focus ~ .label,
.input_field:not(:placeholder-shown) ~ .label {
  font-size: 14px;
  top: -10px;
  left: 20px;
  background-color: var(--primary-color);
  color: var(--black-color);
  border-radius: 30px;
  padding: 0 10px;
}

.icon {
  position: absolute;
  font-size: 20px;
  top: 18px;
  right: 25px;
  color: var(--second-color);
}

/* Other elements */
.remember-forgot {
  display: flex;
  justify-content: flex-start;
  font-size: 15px;
}

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
.signup a:hover,
.forgot a:hover {
  text-decoration: underline;
}

.error {
  color: red;
  text-align: center;
  display: block;
  font-size: 18px;
  font-weight: bold;
  font-family: Arial, sans-serif;
}

/* Back button */
.back-btn {
  background-color: var(--primary-color);
  border-radius: 7px;
  font-size: 15px;
  padding: 2px 10px;
  font-weight: bold;
  position: absolute;
  top: 10px;
  left: 10px;
  cursor: pointer;
  border: none;
  color: var(--black-color);
  transition: 0.2s;
}
.back-btn:hover {
  background-color: #c5bfbf;
}

/* 📱 Responsive adjustments */
@media (max-width: 768px) {
  .login_container {
    padding: 6em 2em 3em 2em;
  }
  .login_title span {
    font-size: 26px;
  }
  .input_field {
    height: 50px;
    font-size: 15px;
  }
}

@media (max-width: 480px) {
  .login_container {
    padding: 5em 1.5em 2em 1.5em;
    max-width: 340px;
  }
  .login_title {
    width: 120px;
    height: 60px;
  }
  .login_title span {
    font-size: 22px;
  }
  .label {
    font-size: 14px;
  }
  .input_field {
    font-size: 14px;
    height: 45px;
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
        <div class="login_container">
            <div class="login_title"><span>Login</span></div>
            <form action="#" method="POST">
                <label class="error"><?php echo $error ?></label>
                <div class="input_wrapper">
                    <input type="text" id="user" name="username" value="<?php echo htmlspecialchars($name); ?>" class="input_field" required placeholder=" ">
                    <label for="user" class="label">Username</label>
                    <i class="fa-solid fa-user icon"></i>
                </div>
                <div class="input_wrapper">
                    <input type="password" id="pass" name="password" value="<?php echo htmlspecialchars($password); ?>" class="input_field" required placeholder=" ">
                    <label for="pass" class="label">Password</label>
                    <i class="fa-solid fa-lock icon"></i>
                </div>
                <div class="remember-forgot">
                    <div class="forgot">
                        <a href="forgot_password.php">Forgot Password?</a>
                    </div>
                </div>
                <input type="submit" class="input-submit" name="login" value="Login">
                <div class="signup">
                    <span>Don't have an account? <a href="register.php">Sign Up</a></span>
                </div>
            </form>
            <button class="back-btn" onclick="window.location.href='index.php'">←</button>
        </div>
    </div>
</body>
</html>
