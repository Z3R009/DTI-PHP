<?php
include '../DBConnection.php';

if (isset($_GET['account_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $account_id = intval($_GET['account_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM account_title WHERE account_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $account_id);

    // Execute both deletion queries
    if ($stmtUser->execute()) {
        // Redirect to the manage members page after successful deletion
        header('Location: account_title.php');
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