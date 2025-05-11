<?php
include '../DBConnection.php';

if (isset($_GET['oopap_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $oopap_id = intval($_GET['oopap_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM oopap WHERE oopap_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $oopap_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: oopap.php?deleted=success');
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