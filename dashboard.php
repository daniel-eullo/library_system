<?php
session_start();
if (!isset($_SESSION['admin']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
$mysqli = new mysqli("localhost", "root", "", "library_system");

// Fetch counts for the dashboard summary
$total_books = $mysqli->query("SELECT COUNT(*) AS count FROM Books")->fetch_assoc()['count'];
$total_members = $mysqli->query("SELECT COUNT(*) AS count FROM Members")->fetch_assoc()['count'];
$returned_today = $mysqli->query("SELECT COUNT(*) AS count FROM transaction WHERE return_date = CURDATE()")->fetch_assoc()['count'];
$borrowed_today = $mysqli->query("SELECT COUNT(*) AS count FROM transaction WHERE borrow_date = CURDATE()")->fetch_assoc()['count'];
$books_summary = $mysqli->query("
    SELECT 
        COUNT(*) AS total_books, 
        SUM(CASE WHEN availability = 1 THEN 1 ELSE 0 END) AS available_books, 
        SUM(CASE WHEN availability = 0 THEN 1 ELSE 0 END) AS borrowed_books 
    FROM Books
")->fetch_assoc();

$total_books = $books_summary['total_books'];
$available_books = $books_summary['available_books'];
$borrowed_books = $books_summary['borrowed_books'];

// Handle search query
$search_query = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_input = $_POST['search'] ?? '';
    $search_query = "WHERE transaction.transaction_id LIKE '%$search_input%' 
                     OR Members.member_name LIKE '%$search_input%' 
                     OR Books.title LIKE '%$search_input%' 
                     OR transaction.borrow_date LIKE '%$search_input%' 
                     OR transaction.due_date LIKE '%$search_input%' 
                     OR transaction.return_date LIKE '%$search_input%'";
}

// Query to fetch transaction records
$query = "SELECT transaction.transaction_id, Members.member_name AS member_name, Books.title AS book_title, 
          transaction.borrow_date, transaction.due_date, transaction.return_date 
          FROM transaction 
          JOIN Members ON transaction.member_id = Members.member_id 
          JOIN Books ON transaction.book_id = Books.book_id 
          $search_query
          ORDER BY transaction.transaction_id ASC";

$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Styling for search bar */
        .search-container {
            margin-bottom: 10px;
            display: flex;
            justify-content: initial;
        }

        .search-container input {
            width: 20%;
            padding: 8px;
            border: 1px solid #b0c4de;
            border-radius: 4px;
            margin-right: 10px;
            margin-left: 460px;
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

        .container {
            text-align: center;
        }

        /* Make the table scrollable */
        .scrollable-table {
            max-height: 600px; /* Adjust height as needed */
            overflow-y: auto;
            margin: auto;
            width: 90%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-left">
            <a href="borrow_form.php">Borrow Form</a>
            <a href="book_list.php">Book List</a>
            <a href="members_list.php">Member List</a>
        </div>
        <div class="nav-right">
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <h1>Dashboard</h1>

    <!-- Dashboard Summary -->
    <div class="dashboard-summary">
        <div class="dashboard-box">
            <div>
                <h3>Total Members</h3>
                <p><?= $total_members ?></p>
            </div>
            <img src="img/members.png" alt="Member Icon">
        </div>
        <div class="dashboard-box">
            <div>
                <h3>Total Books</h3>
                <p><?= $total_books ?></p>
            </div>
            <img src="img/book.png" alt="Book Icon">
        </div>
        <div class="dashboard-box">
            <div>
                <h3>Available Books</h3>
                <p><?= $available_books ?></p>
            </div>
            <img src="img/book-available.png" alt="Book Icon">
        </div>
        <div class="dashboard-box">
            <div>
                <h3>Returned Today</h3>
                <p><?= $returned_today ?></p>
            </div>
            <img src="img/back-arrow.png" alt="Return Icon">
        </div>
        <div class="dashboard-box">
            <div>
                <h3>Borrowed Today</h3>
                <p><?= $borrowed_today ?></p>
            </div>
            <img id="borrow" src="img/back-arrow.png" alt="Borrow Icon">
        </div>
    </div>

    <!-- Search Bar -->
    <form method="POST" class="search-container">
        <input type="text" name="search" placeholder="Search ..." value="<?= htmlspecialchars($_POST['search'] ?? '') ?>">
        <button class="search-btn" type="submit"><img src="img/search.png"></button>
    </form>

    <!-- Transaction Records -->
    <div class="container scrollable-table">
        <table>
            <tr>
                <th>Transaction ID</th>
                <th>Member Name</th>
                <th>Book Title</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['transaction_id'] ?></td>
                        <td><?= $row['member_name'] ?></td>
                        <td><?= $row['book_title'] ?></td>
                        <td><?= $row['borrow_date'] ?></td>
                        <td><?= $row['due_date'] ?></td>
                        <td><?= $row['return_date'] ?: 'Not Returned' ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No transactions found</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
