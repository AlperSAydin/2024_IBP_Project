<?php
session_start();
require_once '../src/auth.php';
require_once '../src/functions.php';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $email = trim($_POST["email"]);

    if(register($username, $password, $email)) {
        redirect('login.php');
    } else {
        $error = "Registration failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Oluştur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .signup-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 500px;
        }

        .signup-container h2 {
            margin-bottom: 20px;
            text-align: center;
            width: 500px;
        }

        .signup-form input[type="text"],
        .signup-form input[type="email"],
        .signup-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .signup-form input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .signup-form input[type="submit"]:hover {
            background-color: #0056b3;          
        }

        .error-message {
            color: #ff0000;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Kayıt Ol</h2>
        <?php if(isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>
        <form class="signup-form" method="post">
            <label for="username">Kullanıcı Adı:</label><br>
            <input type="text" id="username" name="username"><br>
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email"><br>
            <label for="password">Şifre:</label><br>
            <input type="password" id="password" name="password"><br>
            <input type="submit" value="Kayıt Ol">
        </form>
        <h3>Zaten hesabın var mı?<a href="login.php"> Giriş Yap!</a></h3> 
    </div>
</body>
</html>
