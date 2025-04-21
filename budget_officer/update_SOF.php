<?php
include '../DBConnection.php';
session_start();

// Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log request
file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Update request received\n", FILE_APPEND);

if (isset($_POST['project_id']) && isset($_POST['allotment'])) {
    $project_id = $_POST['project_id'];
    $allotment = $_POST['allotment'];

    file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Project ID: $project_id, Allotment: $allotment\n", FILE_APPEND);

    // First, retrieve the current values
    $get_current = "SELECT allotment, balances FROM project WHERE project_id = ?";
    $stmt_get = $connection->prepare($get_current);
    $stmt_get->bind_param("i", $project_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();

    if ($row = $result->fetch_assoc()) {
        $current_allotment = $row['allotment'];
        $current_balance = $row['balances'];

        file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Current values: Allotment: $current_allotment, Balance: $current_balance\n", FILE_APPEND);

        // Calculate the difference between allotments
        $allotment_difference = $allotment - $current_allotment;

        // Calculate new balance based on your rules
        $new_balance = $current_balance + $allotment_difference;

        // If balance would go negative, set it to 0
        if ($new_balance < 0) {
            $new_balance = 0;
        }

        file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Calculated new balance: $new_balance\n", FILE_APPEND);

        // Update both values
        $sql = "UPDATE project SET allotment = ?, balances = ? WHERE project_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssi", $allotment, $new_balance, $project_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Project updated successfully!";
            file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Update successful\n", FILE_APPEND);
        } else {
            $_SESSION['error_message'] = "Error updating project: " . $stmt->error;
            file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Update failed: " . $stmt->error . "\n", FILE_APPEND);
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Could not retrieve current project values";
        file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Failed to get current values\n", FILE_APPEND);
    }
    $stmt_get->close();

    $connection->close();

    $redirect_url = isset($_POST['redirect']) ? $_POST['redirect'] : 'oo1_personalServices.php';
    header("Location: $redirect_url");
    exit();

} else {
    file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Required fields missing\n", FILE_APPEND);
    $_SESSION['error_message'] = "Required fields missing";
    $redirect_url = isset($_POST['redirect']) ? $_POST['redirect'] : 'oo1_personalServices.php';
    header("Location: $redirect_url");
    exit();
}
?>