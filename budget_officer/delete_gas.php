<?php
include '../DBConnection.php';

if (isset($_GET['gas_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $gas_id = intval($_GET['gas_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM gas_allotment WHERE gas_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $gas_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: gas.php');
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