<?php
include '../DBConnection.php';

if (isset($_GET['oo1_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $oo1_id = intval($_GET['oo1_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM oo1_allotment WHERE oo1_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $oo1_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: oo1.php');
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