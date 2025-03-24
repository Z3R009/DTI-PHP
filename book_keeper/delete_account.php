<?php
include '../DBConnection.php';

if (isset($_GET['object_code_id']) && $_GET['confirm'] == 'yes') {
    // Get the user ID from the query string
    $object_code_id = intval($_GET['object_code_id']);

    // Prepare and execute the deletion query for 'users' table
    $deleteUserSql = "DELETE FROM financial_object_code WHERE object_code_id = ?";
    $stmtUser = $connection->prepare($deleteUserSql);
    $stmtUser->bind_param("i", $object_code_id);

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