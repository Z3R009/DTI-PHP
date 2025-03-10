<?php
include '../DBConnection.php';

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    // Check if the username already exists (excluding the current user)
    $sql_check = "SELECT COUNT(*) as count FROM users WHERE username = ? AND user_id != ?";
    $stmt_check = $connection->prepare($sql_check);
    $stmt_check->bind_param("si", $username, $user_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        echo "Username is already taken. Please choose a different one.";
        exit;
    }

    // Prepare the SQL query to update user details without changing the password
    $sql_update = "UPDATE users SET fullname = ?, username = ?, role = ? WHERE user_id = ?";
    $stmt_update = $connection->prepare($sql_update);
    $stmt_update->bind_param("sssi", $fullname, $username, $role, $user_id);

    if ($stmt_update->execute()) {
        header('Location: manage_users.php');
        exit; // Ensure to exit after header redirect
    } else {
        echo "Error: " . $stmt_update->error;
    }
}
?>