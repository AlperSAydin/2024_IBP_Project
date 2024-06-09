<?php
session_start();
require_once '../src/config.php';
require_once '../src/functions.php';

// Kullanıcı giriş yapmamışsa veya admin ise kitap ödünç alamaz, yönlendir
if (!is_logged_in()) {
    redirect('login.php');
}

// Tüm kitapları getir
$sql = "SELECT books.*, users.username AS borrowed_by FROM books LEFT JOIN loans ON books.id = loans.book_id LEFT JOIN users ON loans.user_id = users.id";
$result = mysqli_query($link, $sql);

// Kitap arama
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_term = trim($_POST["search"]);
    // Aranan kelimeye göre kitapları filtrele
    $sql = "SELECT books.*, users.username AS borrowed_by FROM books LEFT JOIN loans ON books.id = loans.book_id LEFT JOIN users ON loans.user_id = users.id WHERE books.title LIKE '%$search_term%' OR books.author LIKE '%$search_term%'";
    $result = mysqli_query($link, $sql);
}

// Kitap ödünç alma işlemi
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["borrow"]) && isset($_SESSION['user_id'])) {
    $book_id = $_GET["borrow"];
    $user_id = $_SESSION['user_id'];

    // Kitabın durumunu kontrol et (available = 1 ise ödünç alınabilir)
    $check_available_sql = "SELECT available FROM books WHERE id = $book_id";
    $check_available_result = mysqli_query($link, $check_available_sql);
    $book = mysqli_fetch_assoc($check_available_result);
    if ($book['available'] == 1) {
        // Kitabı ödünç al ve durumunu güncelle
        $borrow_sql = "INSERT INTO loans (user_id, book_id, loan_date, return_date) VALUES ($user_id, $book_id, CURRENT_TIMESTAMP, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 15 DAY))";
        if (mysqli_query($link, $borrow_sql)) {
            // Kitabın available durumunu güncelle
            $update_sql = "UPDATE books SET available = 0 WHERE id = $book_id";
            mysqli_query($link, $update_sql);
            $success = "Book borrowed successfully.";
        } else {
            $error = "Error borrowing book.";
        }
    } else {
        $error = "Book is not available for borrowing.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kitap Listesi</title>
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
        form {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"] {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 4px;
            background-color: #47eabc;
            display: flex;
            justify-content: space-between; /* Butonu sağa yaslamak için */
        }
        li a {
            color: #007bff;
            text-decoration: none;
        }
        li a:hover {
            text-decoration: underline;
        }
        .borrowed {
            background-color: #ea4848;
        }
        .admin {
            display: none;
        }
        .borrow-button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .borrow-button:hover {
            background-color: #0056b3;
        }
        .book-details {
            display: flex;
            flex-direction: column;
        }
        .borrow-info {
            color: #555;
            font-size: 0.9em;
            margin-top: 5px;
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

        
        .logout-link {
            float: right; /* Sağa hizala */
            margin-top: -90px; /* Yukarıya hafif bir boşluk bırak */
        }
        .logout-link img {
            width: 35px; /* Resmin genişliğini ayarla */
            height: auto; /* Yüksekliği otomatik olarak ayarla */
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
        <h2>Kitap Listesi</h2>
        <h2><button class="Button" onclick="goToIndex()">Ana Sayfa</button></h2>
        <a href="logout.php" class="logout-link"  title="Logout">
                    <img src="https://cdn1.iconfinder.com/data/icons/heroicons-ui/24/logout-512.png" >
                </a>
        <form method="post">
            <label for="search">Ara:</label>
            <input type="text" name="search" id="search">
            <input type="submit" value="Ara">
        </form>
        <?php if(isset($error)): ?>
            <p>Error: <?php echo $error; ?></p>
        <?php elseif(isset($success)): ?>
            <p><?php echo $success; ?></p>
        <?php endif; ?>
        <ul>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <li<?php echo $row['available'] ? '' : ' class="borrowed"'; ?>>
                    <div class="book-details">
                        <?php echo htmlspecialchars($row['title']); ?> /Yazar: <?php echo htmlspecialchars($row['author']); ?>
                        <?php if(!$row['available']): ?>
                            <span class="borrow-info">Tarafından Alındı: <?php echo htmlspecialchars($row['borrowed_by']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if($row['available'] && !is_admin()): ?>
                        <a href="?borrow=<?php echo $row['id']; ?>" class="borrow-button">Ödünç Al</a>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
        
    </div>
</body>
</html>
