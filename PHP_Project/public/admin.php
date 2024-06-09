<?php
session_start();
require_once '../src/config.php';
require_once '../src/functions.php';

if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

// Kitap ekleme formu gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_book"])) {
    $title = trim($_POST["title"]);
    $author = trim($_POST["author"]);

    // Kitap bilgileri ekleniyor
    $sql = "INSERT INTO books (title, author) VALUES (?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $title, $author);
    if (mysqli_stmt_execute($stmt)) {
        $success = "Kitap başarıyla eklendi.";
    } else {
        $error = "Kitap eklenirken bir hata oluştu.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_book"])) {
    if(isset($_POST["book_id"])) {
        $book_id = $_POST["book_id"];
        
        // Ödünç alınmış kayıtları sil
        $sql_delete_loans = "DELETE FROM loans WHERE book_id = ?";
        $stmt_delete_loans = mysqli_prepare($link, $sql_delete_loans);
        mysqli_stmt_bind_param($stmt_delete_loans, "i", $book_id);
        if (mysqli_stmt_execute($stmt_delete_loans)) {
            // Kitabı sil
            $sql_delete_book = "DELETE FROM books WHERE id = ?";
            $stmt_delete_book = mysqli_prepare($link, $sql_delete_book);
            mysqli_stmt_bind_param($stmt_delete_book, "i", $book_id);
            if (mysqli_stmt_execute($stmt_delete_book)) {
                $success = "Kitap başarıyla silindi.";
            } else {
                $error = "Kitap silinirken bir hata oluştu.";
            }
        } else {
            $error = "Kullanıcıya ait ödünç alınmış kitaplar silinirken bir hata oluştu.";
        }
    } else {
        $error = "Kitap ID eksik.";
    }
}


// Kullanıcı değiştirme formu gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_user"])) {
    if(isset($_POST["user_id"])) {
        $user_id = $_POST["user_id"];
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        // Kullanıcı adı, e-posta ve şifre güncelleniyor
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

// Kullanıcı silme formu gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_user"])) {
    if(isset($_POST["user_id"])) {
        $user_id = $_POST["user_id"];

        // Kullanıcıya ait ödünç alınmış kitapları sil
        $sql_delete_loans = "DELETE FROM loans WHERE user_id = ?";
        $stmt_delete_loans = mysqli_prepare($link, $sql_delete_loans);
        mysqli_stmt_bind_param($stmt_delete_loans, "i", $user_id);
        if (mysqli_stmt_execute($stmt_delete_loans)) {
            // Kullanıcıyı sil
            $sql_delete_user = "DELETE FROM users WHERE id = ?";
            $stmt_delete_user = mysqli_prepare($link, $sql_delete_user);
            mysqli_stmt_bind_param($stmt_delete_user, "i", $user_id);
            if (mysqli_stmt_execute($stmt_delete_user)) {
                $success = "Kullanıcı Başarıyla";
            } else {
                $error = "HATA Kullanıcı Silinemedi.";
            }
        } else {
            $error = "Error deleting user's loans.";
        }
    } else {
        $error = "Kullanıcı ID Kayıp.";
    }
}

// Kitap güncelleme formu gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_book"])) {
    if(isset($_POST["book_id"]) && isset($_POST["new_title"]) && isset($_POST["new_author"])) {
        $book_id = $_POST["book_id"];
        $new_title = trim($_POST["new_title"]);
        $new_author = trim($_POST["new_author"]);

        // Kitap bilgileri güncelleniyor
        $sql = "UPDATE books SET title = ?, author = ? WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $new_title, $new_author, $book_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Kitap başarıyla güncellendi.";
        } else {
            $error = "Kitap güncellenirken bir hata oluştu.";
        }
    } else {
        $error = "Güncelleme için gerekli parametreler eksik.";
    }
}

// Kullanıcıları al
$sql = "SELECT * FROM users";
$users_result = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
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
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="password"] {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 100%;
            margin-bottom: 10px;
        }

        input[type="submit"], button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            margin-right: 5px;
        }

        input[type="submit"]:hover, button:hover {
            background-color: #0056b3;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        a {
            color: #007bff;
            text-decoration: none;
            margin-right: 10px;
        }

        a:hover {
            text-decoration: underline;
        }
        .success-message {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
            padding: .75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: .25rem;
        }

        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: .75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: .25rem;
        }
        .logout-link {
            float: right; /* Sağa hizala */
            margin-top: 5px; /* Yukarıya hafif bir boşluk bırak */
        }
        .logout-link img {
            width: 35px; /* Resmin genişliğini ayarla */
            height: auto; /* Yüksekliği otomatik olarak ayarla */
        }
    </style>
    <script>
        function togglePassword(id) {
            var x = document.getElementById(id);
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }

        function goToIndex() {
            window.location.href = 'index.php';
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Admin Paneli</h2>
        <div class="header-links">
            <ul>
            <button onclick="goToIndex()">Ana Sayfa</button>
            <a href="books.php">Kitap Listesi</a>
            <a href="logout.php" class="logout-link" title="Çıkış Yap"><img src="https://cdn1.iconfinder.com/data/icons/heroicons-ui/24/logout-512.png" alt="Logout"></a>
            <a href="mesaj.php">Mesajlaşma</a>
            </ul>
        </div>
        <br>

        <form method="post">
            <label for="title">Kitap Ismi:</label>
            <input type="text" name="title" id="title" required><br>
            <label for="author">Yazar:</label>
            <input type="text" name="author" id="author" required><br>
            <input type="submit" name="add_book" value="Kitap Ekle">
        </form>
        <?php if(isset($error)): ?>
            <p>HATA: <?php echo $error; ?></p>
        <?php elseif(isset($success)): ?>
            <p><?php echo $success; ?></p>
        <?php endif; ?>

        <h3>Kitap Ara</h3>
        <form method="get">
            <label for="search_title">İsime Göre Ara:</label>
            <input type="text" name="title" id="search_title" placeholder="İsim Gir">
            <input type="submit" value="Ara">
        </form>
        
        <?php if(isset($_GET['title']) && !empty($_GET['title'])): ?>
            <?php
            $searched_title = trim($_GET['title']);
            $sql = "SELECT * FROM books WHERE title LIKE ?";
            $stmt = mysqli_prepare($link, $sql);
            $search_param = "%{$searched_title}%";
            mysqli_stmt_bind_param($stmt, "s", $search_param);
            mysqli_stmt_execute($stmt);
            $books_result = mysqli_stmt_get_result($stmt);
            ?>
            <?php if(mysqli_num_rows($books_result) > 0): ?>
                <h3>Arama Sonuçları</h3>
                <ul>
                <?php while($book = mysqli_fetch_assoc($books_result)): ?>
                        <li>
                            <form method="post" action="">
                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                <label for="new_title">Yeni İsim:</label>
                                <input type="text" name="new_title" value="<?php echo $book['title']; ?>"><br>
                                <label for="new_author">Yeni Yazar:</label>
                                <input type="text" name="new_author" value="<?php echo $book['author']; ?>"><br>
                                <input type="submit" name="update_book" value="Güncelle">
                                <input type="submit" name="delete_book" value="Sil">
                            </form>
                        </li>
                    <?php endwhile; ?>
                </ul>
                
            <?php endif; ?>
        <?php endif; ?>

        <h3>Kullanıcı Ara</h3>
        <form method="get">
            <label for="search_username">Kullanıcı Adına Göre Ara:</label>
            <input type="text" name="username" id="search_username" placeholder="Kullanıcı Adı Gir">
            <input type="submit" value="Ara">
        </form>
        
        <?php if(isset($_GET['username']) && !empty($_GET['username'])): ?>
            <?php
            $searched_username = trim($_GET['username']);
            $sql = "SELECT * FROM users WHERE username LIKE ?";
            $stmt = mysqli_prepare($link, $sql);
            $search_param = "%{$searched_username}%";
            mysqli_stmt_bind_param($stmt, "s", $search_param);
            mysqli_stmt_execute($stmt);
            $users_result = mysqli_stmt_get_result($stmt);
            ?>
            <?php if(mysqli_num_rows($users_result) > 0): ?>
                <h3>Arama Sonucu</h3>
                <ul>
                    <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                        <?php if ($user['username'] !== $_SESSION['username']): // Adminin kendi bilgilerini göstermemek için ?>
                            <li>
                                <form method="post" action="change_user.php">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <label for="username">Kullanıcı Adı:</label>
                                    <input type="text" name="username" value="<?php echo $user['username']; ?>"><br>
                                    <label for="email">Email:</label>
                                    <input type="text" name="email" value="<?php echo $user['email']; ?>"><br>
                                    <label for="password">Şifre:</label>
                                    <input type="password" id="password-<?php echo $user['id']; ?>" name="password" value="<?php echo $user['password']; ?>">
                                    <button type="button" onclick="togglePassword('password-<?php echo $user['id']; ?>')">Şifreyi Göster</button><br>
                                    <input type="submit" name="change_user" value="Güncelle">
                                </form>
                                <form method="post" action="">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="submit" name="delete_user" value="Sil">
                                </form>
                                <?php
                                // Kullanıcının ödünç aldığı kitapları al
                                $sql = "SELECT books.title, books.author, loans.loan_date, loans.return_date FROM loans JOIN books ON loans.book_id = books.id WHERE loans.user_id = ?";
                                $stmt = mysqli_prepare($link, $sql);
                                mysqli_stmt_bind_param($stmt, "i", $user['id']);
                                mysqli_stmt_execute($stmt);
                                $loans_result = mysqli_stmt_get_result($stmt);
                                ?>
                                <?php if(mysqli_num_rows($loans_result) > 0): ?>
                                    <h4>Ödünç Alınan Kitaplar</h4>
                                    <ul>
                                        <?php while($loan = mysqli_fetch_assoc($loans_result)): ?>
                                            <li><?php echo htmlspecialchars($loan['title']); ?> /Yazar:  <?php echo htmlspecialchars($loan['author']); ?> -  Ödünç Alınan Gün: <?php echo htmlspecialchars($loan['loan_date']); ?>, İade Edilecek Son Gün: <?php echo htmlspecialchars($loan['return_date']); ?></li>
                                        <?php endwhile; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>Ödünç kitap yok.</p>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>Sağlanan kullanıcı adına sahip kullanıcı bulunamadı.</p>
            <?php endif; ?>
        <?php endif; ?>

        
        
    </div>
</body>
</html>


