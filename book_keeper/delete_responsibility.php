<?php
include '../DBConnection.php';

if (isset($_GET['rc_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $rc_id = intval($_GET['rc_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM responsibility_center WHERE rc_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $rc_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: responsibility.php?deleted=success');
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