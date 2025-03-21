<?php
include '../DBConnection.php';

if (isset($_GET['oo4_1_1_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $oo4_1_1_id = intval($_GET['oo4_1_1_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM oo4_1_1_allotment WHERE oo4_1_1_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $oo4_1_1_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: oo4_1_1.php');
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