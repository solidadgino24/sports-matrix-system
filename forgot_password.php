<?php
include "conn.php";
session_start();

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = "Please enter your email.";
    } else {
        // Check if user exists
        $check = $con->prepare("SELECT user_id FROM tbl_user WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Generate temporary password
            $temp_password = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
            $hashed = password_hash($temp_password, PASSWORD_DEFAULT);

            // Update DB
            $update = $con->prepare("UPDATE tbl_user SET password = ?, is_temp = 1 WHERE user_id = ?");
            $update->bind_param("si", $hashed, $user['user_id']);

            if ($update->execute()) {
                $message = "✅ Temporary password generated successfully!";
                $temp_display = "Your temporary password is: <strong>$temp_password</strong><br>Use this to log in, then change your password.";
            } else {
                $error = "Error updating password.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<style>
:root {
  --second-color: #b9b9b9;
  --primary-color: #b5afaf;
  --black-color: #020101;
}

/* Reset and Layout */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box; /* ✅ Prevents overflow issues */
}

body {
  background: url('chmsubg.jpg') center/cover no-repeat fixed;
  font-family: "Poppins", sans-serif;
  color: #fff;
  min-height: 100vh;
}

.container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: rgba(0, 0, 0, 0.4);
  padding: 20px;
}

.box {
  position: relative;
  backdrop-filter: blur(25px);
  background-color: rgba(255, 255, 255, 0.05);
  border: 2px solid var(--primary-color);
  border-radius: 15px;
  padding: 3em 2.5em;
  width: 100%;
  max-width: 400px;
  box-shadow: 0px 0px 10px 2px hsla(127, 98%, 48%, 0.2);
}

/* Title */
h2 {
  text-align: center;
  color: #fff;
  margin-bottom: 1em;
  font-size: 28px;
}

/* Input fields */
input[type="text"],
input[type="submit"] {
  width: 100%;
  display: block;
  padding: 12px 16px; /* ✅ reduced padding */
  margin: 10px 0;
  border-radius: 25px;
  border: 2px solid var(--primary-color);
  outline: none;
  font-size: 16px;
  background: transparent;
  color: #fff;
}

input[type="text"]::placeholder {
  color: #ccc;
}

input[type="submit"] {
  background: #fff6f6;
  color: #000;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.2s;
}

input[type="submit"]:hover {
  background: var(--second-color);
}

/* Messages */
.message {
  color: lime;
  text-align: center;
  font-weight: bold;
  margin-top: 1em;
  word-wrap: break-word;
}

.error {
  color: red;
  text-align: center;
  font-weight: bold;
  margin-top: 1em;
}

/* Back button */
.back-btn {
  background-color: var(--primary-color);
  border: none;
  border-radius: 7px;
  font-size: 16px;
  padding: 6px 12px;
  font-weight: bold;
  position: absolute;
  top: 15px;
  left: 15px;
  cursor: pointer;
  color: var(--black-color);
  transition: background 0.2s;
}
.back-btn:hover {
  background-color: var(--second-color);
}

/* 📱 Responsive Design */
@media (max-width: 768px) {
  .box {
    padding: 2.5em 2em;
  }
  h2 {
    font-size: 24px;
  }
  input[type="text"],
  input[type="submit"] {
    font-size: 15px;
    padding: 10px 14px;
  }
}

@media (max-width: 480px) {
  .box {
    max-width: 340px;
    padding: 2em 1.5em;
    border-radius: 12px;
  }
  h2 {
    font-size: 22px;
  }
  .back-btn {
    font-size: 14px;
    padding: 5px 10px;
  }
  input[type="text"],
  input[type="submit"] {
    font-size: 14px;
    padding: 10px 12px; /* ✅ fits perfectly on small screens */
  }
}

</style>
</head>
<body>
<div class="container">
  <div class="box">
    <button class="back-btn" onclick="window.location.href='login.php'">←</button>
    <h2>Forgot Password</h2>
    <form method="POST">
      <input type="text" name="email" placeholder="Enter your email" required>
      <input type="submit" value="Reset Password">
    </form>
    <?php if (!empty($message)) echo "<div class='message'>$message<br>$temp_display</div>"; ?>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
  </div>
</div>
</body>
</html>
