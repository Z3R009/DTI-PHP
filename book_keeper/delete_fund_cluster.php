<?php
include '../DBConnection.php';

if (isset($_GET['fund_cluster_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $fund_cluster_id = intval($_GET['fund_cluster_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM fund_cluster WHERE fund_cluster_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $fund_cluster_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: fund_cluster.php');
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