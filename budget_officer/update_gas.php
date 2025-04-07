<?php
include '../DBConnection.php';

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request
file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Update request received\n", FILE_APPEND);

// Check if project_id and allotment are set
if (isset($_POST['project_id']) && isset($_POST['allotment'])) {
    $project_id = $_POST['project_id'];
    $allotment = $_POST['allotment'];
    
    // Log the data
    file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Project ID: $project_id, Allotment: $allotment\n", FILE_APPEND);

    // Direct SQL query for debugging
    $direct_sql = "UPDATE project SET allotment = $allotment WHERE project_id = $project_id";
    file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Direct SQL: $direct_sql\n", FILE_APPEND);
    
    // Try direct query first
    $direct_result = $connection->query($direct_sql);
    if ($direct_result) {
        $_SESSION['success_message'] = "Project updated successfully!";
        file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Direct update successful\n", FILE_APPEND);
    } else {
        // If direct query fails, try prepared statement
        $sql = "UPDATE project SET allotment = ? WHERE project_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("si", $allotment, $project_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Project updated successfully!";
            file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Prepared statement update successful\n", FILE_APPEND);
        } else {
            $_SESSION['error_message'] = "Error updating project: " . $stmt->error;
            file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Update failed: " . $stmt->error . "\n", FILE_APPEND);
        }
    }
    
    // Redirect to gas.php
    header("Location: gas.php");
    exit();
} else {
    file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Required fields missing\n", FILE_APPEND);
    $_SESSION['error_message'] = "Required fields missing";
    
    // Redirect to gas.php
    header("Location: gas.php");
    exit();
}
?>