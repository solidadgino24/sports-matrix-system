<?php
include "conn.php";
session_start();

// ✅ Make sure user is logged in
if (!isset($_SESSION['session_user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['session_user_id'];
$user_type = $_SESSION['session_type'] ?? null;

$message = "";
$error = "";

if (isset($_POST['update'])) {
    $new_pass = trim($_POST['new_password']);
    $confirm_pass = trim($_POST['confirm_password']);

    if ($new_pass == "" || $confirm_pass == "") {
        $error = "Please fill in all fields.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Passwords do not match!";
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);

        $update = $con->prepare("UPDATE tbl_user SET password = ?, is_temp = 0 WHERE user_id = ?");
        $update->bind_param("si", $hashed, $user_id);

        if ($update->execute()) {
            $message = "✅ Password successfully updated! Redirecting...";

            // Redirect user based on their user_type after 2 seconds
            switch ($user_type) {
                case 1:
                    header("refresh:2;url=admin/");
                    break;
                case 2:
                    header("refresh:2;url=association/");
                    break;
                case 3:
                    header("refresh:2;url=player/");
                    break;
                case 4:
                    header("refresh:2;url=scorer/");
                    break;
                default:
                    header("refresh:2;url=index.php");
                    break;
            }
        } else {
            $error = "Something went wrong. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <style>
        body {
            background: url('chmsubg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: rgba(0,0,0,0.3);
        }
        .box {
            backdrop-filter: blur(25px);
            padding: 3em 2em;
            border: 2px solid #b5afaf;
            border-radius: 15px;
            width: 400px;
            color: #fff;
            box-shadow: 0 0 10px rgba(255,255,255,0.2);
            position: relative;
        }
        h2 {
            text-align: center;
            margin-bottom: 1em;
        }
        input[type=password], input[type=submit] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 25px;
            border: 2px solid #b5afaf;
            outline: none;
            font-size: 16px;
        }
        input[type=submit] {
            background: #fff6f6;
            color: #000;
            cursor: pointer;
            font-weight: 600;
        }
        input[type=submit]:hover {
            background: #b9b9b9;
        }
        .message { color: lime; text-align:center; font-weight:bold; margin-top: 10px; }
        .error { color: red; text-align:center; font-weight:bold; margin-top: 10px; }
        .back-btn {
            background-color: #b5afaf;
            border-radius: 7px;
            font-size: 15px;
            padding: 3px 10px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            left: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="box">
        <button class="back-btn" onclick="window.location.href='index.php'"><<</button>
        <h2>Change Password</h2>
        <form method="POST">
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            <input type="submit" name="update" value="Update Password">
        </form>
        <?php if ($message) echo "<div class='message'>$message</div>"; ?>
        <?php if ($error) echo "<div class='error'>$error</div>"; ?>
    </div>
</div>
</body>
</html>
