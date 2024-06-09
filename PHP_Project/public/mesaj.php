<?php
session_start();

// Veritabanı bağlantısı ve diğer gerekli dosyaların dahil edilmesi
require_once '../src/auth.php';
require_once '../src/functions.php';

// Oturum açmış kullanıcının kullanıcı adını al
$sender_username = $_SESSION['username'];

// Mesaj silme işlemi
if (isset($_POST['delete_message_id'])) {
    $delete_message_id = $_POST['delete_message_id'];

    // Mesajı silmek için SQL sorgusunu hazırla
    $delete_sql = "DELETE FROM messages WHERE id = ?";
    $delete_stmt = mysqli_prepare($link, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "i", $delete_message_id);

    // SQL sorgusunu çalıştır
    if (mysqli_stmt_execute($delete_stmt)) {
        echo '<p class="success-message">Mesaj başarıyla silindi.</p>';
    } else {
        echo '<p class="error-message">Hata: Mesaj silinemedi.</p>';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['receiver_username']) && isset($_POST['message'])) {
        $receiver_username = $_POST['receiver_username']; // Alıcı kullanıcı adı
        $message = $_POST['message']; // Mesaj

        // Veri girişlerini doğrula
        if (empty($receiver_username) || empty($message)) {
            echo '<p class="error-message">Hata: Alıcı kullanıcı adı ve mesaj alanları boş olamaz.</p>';
        } else if ($receiver_username === $sender_username) {
            echo '<p class="error-message">Hata: Kendinize mesaj gönderemezsiniz.</p>';
        } else {
            // Alıcı kullanıcı adını veritabanında kontrol et
            $check_sql = "SELECT COUNT(*) AS count FROM users WHERE username = ?";
            $check_stmt = mysqli_prepare($link, $check_sql);
            mysqli_stmt_bind_param($check_stmt, "s", $receiver_username);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            $row = mysqli_fetch_assoc($check_result);
            $user_exists = $row['count'] > 0;

            if (!$user_exists) {
                echo '<p class="error-message">Hata: Belirtilen alıcı kullanıcı adı mevcut değil.</p>';
            } else {
                // Mesajı veritabanına eklemek için SQL sorgusunu hazırla
                $insert_sql = "INSERT INTO messages (sender_username, receiver_username, message, sent_at) 
                               VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
                
                // SQL sorgusunu hazırla ve parametreleri bağla
                $insert_stmt = mysqli_prepare($link, $insert_sql);
                mysqli_stmt_bind_param($insert_stmt, "sss", $sender_username, $receiver_username, $message);
                
                // SQL sorgusunu çalıştır
                if (mysqli_stmt_execute($insert_stmt)) {
                    echo '<p class="success-message">Mesaj başarıyla gönderildi.</p>';
                } else {
                    echo '<p class="error-message">Hata: Mesaj gönderilemedi.</p>';
                }
            }
        }
    }
}


// Kullanıcının aldığı mesajları veritabanından al
$select_sql = "SELECT id, sender_username, message FROM messages WHERE receiver_username = ?";
$select_stmt = mysqli_prepare($link, $select_sql);
mysqli_stmt_bind_param($select_stmt, "s", $sender_username);
mysqli_stmt_execute($select_stmt);
$result = mysqli_stmt_get_result($select_stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Mesaj Gönder</title>
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
            margin-top: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
            color: #555;
        }

        input[type="text"],
        textarea {
            width: calc(100% - 24px);
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
        }

        input[type="submit"],
        button {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        input[type="submit"]:hover,
        button:hover {
            background-color: #0056b3;
        }

        button {
            margin-top: 20px;
            margin-left: 10px;
        }

        .error-message, .success-message {
            color: #fff;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
        }

        .error-message {
            background-color: #f44336;
        }

        .success-message {
            background-color: #4CAF50;
        }
    </style>

    <script>
        function goToIndex() {
            window.location.href = 'index.php';
        }
    </script>
    </head>

<body>
    <div class="container">
        <h2>Mesaj Gönder</h2>
        <button onclick="goToIndex()">Ana Sayfa</button>
        <form method="post">
            <label for="receiver_username">Alıcı Kullanıcı Adı:</label>
            <input type="text" id="receiver_username" name="receiver_username" required><br>
            <label for="message">Mesajınız:</label><br>
            <textarea id="message" name="message" rows="4" cols="50" required></textarea><br>
            <input type="submit" value="Mesajı Gönder">
        </form>

        <h2>Mesajlarınız</h2>
        <ul>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <li>
                Gönderen: <strong><?php echo htmlspecialchars($row['sender_username']); ?></strong> Mesaj: <em><?php echo htmlspecialchars($row['message']); ?></em>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="delete_message_id" value="<?php echo $row['id']; ?>">
                    <button type="submit">Mesajı Sil</button>
                </form>
            </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>

</html>

