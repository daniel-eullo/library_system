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
    $search_query = "WHERE member_name LIKE '%$search_input%' 
                     OR membership_date LIKE '%$search_input%' 
                     OR membership_type LIKE '%$search_input%'";
}

// Query to fetch member details
$query = "SELECT 
            member_name, 
            membership_date, 
            membership_type 
          FROM Members 
          $search_query";

$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Member List</title>
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
        }

        .search-container button {
            background-color: #818ada;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-container button:hover {
            background-color: #434ea1;
        }

        .search-btn{
            margin: 0px 0px;
            margin-left: 10px;
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
            <a href="book_list.php">Book List</a>
        </div>
        <div class="nav-right">
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    <h1 style="text-align: center;">Member List</h1>
    <div class="container">
        <!-- Search Bar -->
        <form method="POST" class="search-container">
            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($_POST['search'] ?? '') ?>">
            <button class="search-btn" type="submit"><img src="img/search.png"></button>
        </form>

        <!-- Member Table -->
        <table>
            <tr>
                <th>Member Name</th>
                <th>Membership Date</th>
                <th>Membership Type</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['member_name']) ?></td>
                        <td><?= htmlspecialchars($row['membership_date']) ?></td>
                        <td><?= htmlspecialchars($row['membership_type']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No members found</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
