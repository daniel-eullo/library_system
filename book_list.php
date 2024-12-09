<?php
session_start();
if (!isset($_SESSION['admin']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Connect to the database
$mysqli = new mysqli("localhost", "root", "", "library_system");

// Handle search query
$search_query = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_input = $_POST['search'] ?? '';
    $search_query = "WHERE Books.title LIKE '%$search_input%' 
                     OR Books.genre LIKE '%$search_input%' 
                     OR Books.availability LIKE '%$search_input%' 
                     OR Authors.author_name LIKE '%$search_input%'";
}

// Query to fetch book details (with or without search query)
$query = "SELECT 
            Books.title, 
            Authors.author_name, 
            Books.genre, 
            Books.availability 
          FROM Books 
          JOIN Authors ON Books.author_id = Authors.author_id 
          $search_query";

$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Book List</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .search-container {
            margin-bottom: 10px;
            display: flex;
        }

        .search-container input {
            width: 20%;
            padding: 8px;
            border: 1px solid #b0c4de;
            border-radius: 4px;
            margin-right: 10px;
        }

        .search-container button {
            background-color: #818ada;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin: 0;
        }

        .search-container button:hover {
            background-color: #434ea1;
        }

        .search-btn {
            height: 50%;
            padding: 0;
        }

        .search-btn img {
            height: 40%;
            width: 40%;
            padding: 5px 0;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #5a67d8;
            padding: 10px 20px;
        }
        .navbar .nav-left, .navbar .nav-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .navbar a {
            color: #ffffff;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .navbar a:hover {
            background-color: #434ea1;
        }
        .navbar .nav-right {
            margin-left: auto;
        }

    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="dashboard.php">Dashboard</a>
            <a href="borrow_form.php">Borrow Form</a>
            <a href="members_list.php">Member List</a>
        </div>
        <div class="nav-right">
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <h1 style="text-align: center;">Book List</h1>
    <div class="container">
        <!-- Search Bar -->
        <form method="POST" class="search-container">
            <input type="text" name="search" placeholder="Search ..." value="<?= htmlspecialchars($_POST['search'] ?? '') ?>">
            <button class="search-btn" type="submit"><img src="img/search.png"></button>
        </form>

        <!-- Book Table -->
        <table>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Genre</th>
                <th>Availability</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['author_name']) ?></td>
                        <td><?= htmlspecialchars($row['genre']) ?></td>
                        <td><?= $row['availability'] ? 'True' : 'False' ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No books found</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
