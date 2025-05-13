<?php
include '../DBConnection.php';

if (isset($_GET['approver_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $approver_id = intval($_GET['approver_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM approver WHERE approver_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $approver_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: approver.php?deleted=success');
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