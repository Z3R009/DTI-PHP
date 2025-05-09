<?php
include '../DBConnection.php';

if (isset($_GET['payee_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $payee_id = intval($_GET['payee_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM payee WHERE payee_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $payee_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: payee.php?deleted=success');
        exit();
    } else {
        // Handle error if either query fails
        echo "Error deleting user: " . $connection->error;
    }
} else {
    // Handle invalid request
    echo "Invalid request.";
}
?>