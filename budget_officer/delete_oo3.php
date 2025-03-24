<?php
include '../DBConnection.php';

if (isset($_GET['project_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $project_id = intval($_GET['project_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM project WHERE project_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $project_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: oo3.php');
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