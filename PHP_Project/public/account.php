<?php
session_start();
require_once '../src/config.php';
require_once '../src/functions.php';

if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini al
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($link, $user_sql);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user_data = mysqli_fetch_assoc($user_result);

$sql = "SELECT books.id, books.title, loans.loan_date, loans.return_date FROM loans JOIN books ON loans.book_id = books.id WHERE loans.user_id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["return_book"])) {
    $book_id = $_POST["book_id"];

    // Kitabın ödünç alma durumunu güncelle
    $update_sql = "UPDATE books SET available = 1 WHERE id = ?";
    $update_stmt = mysqli_prepare($link, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "i", $book_id);
    if (mysqli_stmt_execute($update_stmt)) {
        // Ödünç alma tablosundan kitabı kaldır
        $delete_sql = "DELETE FROM loans WHERE book_id = ? AND user_id = ?";
        $delete_stmt = mysqli_prepare($link, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "ii", $book_id, $user_id);
        mysqli_stmt_execute($delete_stmt);
        $success = "Book returned successfully.";
    } else {
        $error = "Error returning book.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    $new_username = trim($_POST["username"]);
    $new_email = trim($_POST["email"]);
    $new_password = trim($_POST["password"]);

    // Kullanıcı bilgilerini güncelle
    $update_user_sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
    $update_user_stmt = mysqli_prepare($link, $update_user_sql);
    mysqli_stmt_bind_param($update_user_stmt, "sssi", $new_username, $new_email, $new_password, $user_id);
    if (mysqli_stmt_execute($update_user_stmt)) {
        $_SESSION['username'] = $new_username;
        $_SESSION['email'] = $new_email;
        $_SESSION['password'] = $new_password;
        $profile_success = "Profile updated successfully.";
    } else {
        $profile_error = "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
            background-color: #dcffa0 ;
            position: relative;
        }

        li .return-button {
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }

        li .return-button:hover {
            background-color: #0056b3;
        }

        .loan-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .profile-info {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        .profile-info label {
            font-weight: bold;
        }

        .profile-info input[type="text"],
        .profile-info input[type="password"] {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            margin-top: 5px;
        }

        .profile-info input[type="submit"] {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            margin-top: 10px;
        }

        .profile-info input[type="submit"]:hover {
            background-color: #0056b3;
        }
        
        .logout-link {
            float: right; /* Sağa hizala */
            margin-top: -60px; /* Yukarıya hafif bir boşluk bırak */
        }
        
        .logout-link img {
            width: 35px; /* Resmin genişliğini ayarla */
            height: 40px; /* Yüksekliği otomatik olarak ayarla */
        }

        a {
            color: #007bff;
            text-decoration: none;
            margin-right: 10px;
        }

        a:hover {
            text-decoration: underline;
        }

        .Button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .Button:hover {
            background-color: #0056b3;
        }


    </style>
</head>

<script>

        function goToIndex() {
            window.location.href = 'index.php';
        }
        
    </script>

<body>
    <div class="container">
        <div class="profile-info">
            <h2>Profil Bilgileri</h2>
            <a href="logout.php" class="logout-link"  title="Çıkış Yap">
                <img src="https://cdn1.iconfinder.com/data/icons/heroicons-ui/24/logout-512.png" > </a>
                
            <h2><button class="Button" onclick="goToIndex()">Ana Sayfa</button> </h2>

            <?php if(isset($profile_error)): ?>
                <p>Hata: <?php echo $profile_error; ?></p>
            <?php elseif(isset($profile_success)): ?>
                <p><?php echo $profile_success; ?></p>
            <?php endif; ?>
            <form method="post">
                <label for="username">Kullanıcı Adı:</label><br>
                <input type="text" id="username" name="username" value="<?php echo $user_data['username']; ?>"><br>
                <label for="email">Email:</label><br>
                <input type="text" id="email" name="email" value="<?php echo $user_data['email']; ?>"><br>
                <label for="password">Şifre:</label><br>
                <input type="password" id="password" name="password" value="<?php echo $user_data['password']; ?>"><br>
                <input type="submit" name="update_profile" value="Güncelle">
            </form>
        <h2>Ödünç Aldıklarım</h2>
        <?php if(mysqli_num_rows($result) > 0): ?>
    <ul>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <li>
                <div class="loan-info">
                    <strong><?php echo htmlspecialchars($row['title']); ?></strong> - 
                    Ödünç Alınan Gün: <?php echo date('Y-m-d H:i:s', strtotime($row['loan_date'])); ?> - 
                    İade Edilecek Son Gün: <?php echo date('Y-m-d H:i:s', strtotime($row['return_date'])); ?>
                </div>
                <form method="post">
                    <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                    <input type="submit" name="return_book" value="Return" class="return-button">
                </form>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>Henüz bir kitap ödünç almadınız.</p>
<?php endif; ?>


    </div>
</body>
</html>

