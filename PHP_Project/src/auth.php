<?php
require_once 'config.php';

function login($username, $password) {
    global $link;
    $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $param_username);
        $param_username = $username;

        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);

            if(mysqli_stmt_num_rows($stmt) == 1){
                mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);
                if(mysqli_stmt_fetch($stmt)){
                    if(password_verify($password, $hashed_password)){
                        session_start();
                        $_SESSION['user_id'] = $id;
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $role;
                        return true;
                    }
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}

function register($username, $password, $email) {
    global $link;
    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_password, $param_email);
        $param_username = $username;
        $param_password = password_hash($password, PASSWORD_DEFAULT);
        $param_email = $email;

        if(mysqli_stmt_execute($stmt)){
            return true;
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}

?>
