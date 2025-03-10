<?php
include 'DBConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($password, $hashed_password)) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on user type
            if ($_SESSION['role'] == 'Admin') {
                header('Location: admin/dashboard.php');
            } elseif ($_SESSION['role'] == 'Bookkeeper') {
                header('Location: book_keeper/dashboard.php');
            } elseif ($_SESSION['role'] == 'Budget Officer') {
                header('Location: budget_officer/dashboard.php');
            } elseif ($_SESSION['role'] == 'Chief Accountant') {
                header('Location: chief_accountant/dashboard.php');
            } elseif ($_SESSION['role'] == 'Guest') {
                header('Location: guest/dashboard.php');
            } else {
                echo "<script>alert('Invalid user type value');</script>";
            }
            exit();
        } else {
            echo "<script>alert('Incorrect Password');</script>";
        }
    } else {
        echo "<script>alert('Incorrect Username');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script type="module" src="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <div class="login-container">
        <div class="logo" style="display: flex; justify-content: center;">
            <img src="img/dti_logo.png" alt="Company Logo" width="100" height="100">

        </div>

        <p id="error-message" style="color: red; display: none;"></p>
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <ion-icon name="mail-outline"></ion-icon>
                <input type="text" id="username" name="username" placeholder="Enter your Username" autocomplete="off"
                    required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input type="password" id="password" name="password" placeholder="Enter your Password"
                    autocomplete="off" required>
            </div>

            <button type="submit">Login</button>
        </form>
</body>

</html>