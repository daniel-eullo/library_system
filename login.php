<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "library_system");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM Admins WHERE username = '$username' AND password = '$password'";
    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $_SESSION['admin'] = $admin['username'];  // Store admin username
        $_SESSION['admin_id'] = $admin['admin_id'];  // Store admin ID
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<script>alert('Invalid login credentials!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f7; /* Light blue-gray for subtle background */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #ffffff;
            border: 1px solid #dde3ea;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 320px;
            text-align: center;
        }

        .login-container h1 {
            margin-bottom: 20px;
            font-size: 26px;
            color: #2b3a5b; /* Dark blue for text */
        }

        .login-container input {
            width: 85%;
            padding: 12px;
            margin: 12px 0;
            border: 1px solid #b0c4de;
            border-radius: 6px;
            font-size: 15px;
            color: #2b3a5b; /* Text inside input fields */
        }

        .login-container input:focus {
            outline: none;
            border-color: #8faacb; /* Light blue for focus effect */
            box-shadow: 0 0 5px rgba(143, 170, 203, 0.5);
        }

        .login-container button {
            width: 85%;
            padding: 12px;
            background-color: #4973a7; /* Warm blue for button */
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .login-container button:hover {
            background-color: #3a5f8b; /* Darker warm blue for hover effect */
            box-shadow: 0 4px 6px rgba(58, 95, 139, 0.3);
        }

        .login-container p {
            margin-top: 10px;
            font-size: 13px;
            color: #556677; /* Subtle gray-blue for secondary text */
        }
</style>

</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
