<?php
session_start();
require_once '../src/config.php';
require_once '../src/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_user"])) {
    if(isset($_POST["user_id"])) {
        $user_id = $_POST["user_id"];
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        $sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $password, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "User updated successfully.";
        } else {
            $error = "Error updating user.";
        }
    } else {
        $error = "User ID is missing.";
    }
}

// Redirect back to admin panel with appropriate message
if (isset($success)) {
    $_SESSION['success'] = $success;
    redirect('admin.php');
} elseif (isset($error)) {
    $_SESSION['error'] = $error;
    redirect('admin.php');
} else {
    redirect('admin.php');
}
?>
