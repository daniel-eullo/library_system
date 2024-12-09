<?php
session_start(); // Start the session
if (!isset($_SESSION['admin']) || !isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "library_system");

// Handle Borrow Book Form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['borrow_submit'])) {
    $member_name = $_POST['member_name'];
    $book_title = $_POST['book_title'];
    $borrow_date = date('Y-m-d'); // Automatically set to the current date
    $due_date = date('Y-m-d', strtotime('+2 weeks')); // Set to two weeks from the borrow date
    $admin_id = $_SESSION['admin_id']; // Dynamically get the logged-in admin's ID

    // Validate Member Name
    $member_query = $mysqli->prepare("SELECT member_id FROM Members WHERE member_name = ?");
    $member_query->bind_param("s", $member_name);
    $member_query->execute();
    $member_result = $member_query->get_result();
    if ($member_result->num_rows == 0) {
        echo "<script>alert('Member not found! Please enter a valid member name.');</script>";
    } else {
        $member_id = $member_result->fetch_assoc()['member_id'];

        // Validate Book Title
        $book_query = $mysqli->prepare("SELECT book_id FROM Books WHERE title = ? AND availability = 1");
        $book_query->bind_param("s", $book_title);
        $book_query->execute();
        $book_result = $book_query->get_result();
        if ($book_result->num_rows == 0) {
            echo "<script>alert('Book not available or not found! Please enter a valid book title.');</script>";
        } else {
            $book_id = $book_result->fetch_assoc()['book_id'];

            // Insert Transaction
            $query = "INSERT INTO transaction (member_id, book_id, borrow_date, due_date, admin_id) 
                      VALUES ('$member_id', '$book_id', '$borrow_date', '$due_date', '$admin_id')";
            if ($mysqli->query($query)) {
                // Update book availability
                $mysqli->query("UPDATE Books SET availability = 0 WHERE book_id = '$book_id'");
                echo "<script>alert('Transaction saved successfully!');</script>";
            } else {
                echo "Error: " . $mysqli->error;
            }
        }
    }
}

// Handle Return Book Form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_submit'])) {
    $member_name = $_POST['member_name'];
    $book_title = $_POST['book_title'];
    $return_date = date('Y-m-d'); // Automatically set to the current date

    // Validate Book and Member Name
    $book_query = $mysqli->prepare("SELECT book_id, availability FROM Books WHERE title = ?");
    $book_query->bind_param("s", $book_title);
    $book_query->execute();
    $book_result = $book_query->get_result();
    if ($book_result->num_rows == 0) {
        echo "<script>alert('Book not found! Please enter a valid book title.');</script>";
    } else {
        $book_data = $book_result->fetch_assoc();
        if ($book_data['availability'] == 1) {
            echo "<script>alert('Book is already available and cannot be returned!');</script>";
        } else {
            $book_id = $book_data['book_id'];

            // Validate Member Name and Book ID in Transactions
            $transaction_query = $mysqli->prepare("SELECT transaction_id FROM transaction WHERE book_id = ? AND member_id = (SELECT member_id FROM Members WHERE member_name = ?) AND return_date IS NULL");
            $transaction_query->bind_param("is", $book_id, $member_name);
            $transaction_query->execute();
            $transaction_result = $transaction_query->get_result();

            if ($transaction_result->num_rows == 0) {
                echo "<script>alert('Mismatch between member and borrowed book! Please check your input.');</script>";
            } else {
                $transaction_id = $transaction_result->fetch_assoc()['transaction_id'];

                // Update Transaction and Book availability
                $mysqli->query("UPDATE transaction SET return_date = '$return_date' WHERE transaction_id = '$transaction_id'");
                $mysqli->query("UPDATE Books SET availability = 1 WHERE book_id = '$book_id'");
                echo "<script>alert('Book returned successfully!');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Borrow & Return Book</title>
    <link rel="stylesheet" href="styles.css">
    <style>
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
                <a href="members_list.php">Members List</a>
                <a href="book_list.php">Book List</a>
            </div>
            <div class="nav-right">
                <a href="logout.php">Logout</a>
            </div>
    </nav>
    <h1 style="text-align: center;">Borrow or Return Book</h1>
<div class="flex-container" style="display:flex; justify-content:center;">
        <div class= "login-container" style="margin-right: 40px">
        <h1>Borrow Book</h1>
            <form method="POST">
                <label for="member_name">Member Name:</label>
                <input type="text" name="member_name" placeholder="Enter Member Name" required>

                <label for="book_title">Book Title:</label>
                <input type="text" name="book_title" placeholder="Enter Book Title" required>

                <label for="borrow_date">Borrow Date:</label>
                <input type="text" name="borrow_date" value="<?= date('Y-m-d') ?>" readonly>

                <label for="due_date">Due Date:</label>
                <input type="text" name="due_date" value="<?= date('Y-m-d', strtotime('+2 weeks')) ?>" readonly>

                <button type="submit" name="borrow_submit">Borrow</button>
            </form>
        </div>
    <br>
    <div class= "login-container">
        <h1>Return Book</h1>
        <form method="POST">
            <label for="member_name">Member Name:</label>
            <input type="text" name="member_name" placeholder="Enter Member Name" required>

            <label for="book_title">Book Title:</label>
            <input type="text" name="book_title" placeholder="Enter Book Title" required>

            <button type="submit" name="return_submit">Return</button>
        </form>
    </div>
</div>
    <form action="dashboard.php" method="get" style="margin-top: 20px;">
        <button type="submit">Go to Dashboard</button>
    </form>
</body>
</html>
