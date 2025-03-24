<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $user_id = $_POST['user_id'];
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $role = $_POST['role'];

    $sql = "UPDATE users SET fullname = ?, username = ?, role = ? WHERE user_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssi", $fullname, $username, $role, $user_id);

    if ($stmt->execute()) {
        header('Location: manage_users.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>