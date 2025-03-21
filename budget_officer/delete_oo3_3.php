<?php
include '../DBConnection.php';

if (isset($_GET['oo3_3_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $oo3_3_id = intval($_GET['oo3_3_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM oo3_3_allotment WHERE oo3_3_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $oo3_3_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: oo3_3.php');
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