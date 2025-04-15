<?php
include '../DBConnection.php';
session_start();

// Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log request
file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Update request received\n", FILE_APPEND);

if (isset($_POST['draft_id']) && isset($_POST['cash_allotment']) && isset($_POST['payee'])) {
    $draft_id = $_POST['draft_id'];
    $payee = $_POST['payee'];
    $new_cash_allotment = $_POST['cash_allotment'];

    file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Draft ID: $draft_id, Payee: $payee, New Cash_Allotment: $new_cash_allotment\n", FILE_APPEND);

    // First, retrieve the current values
    $get_current = "SELECT cash_allotment, balances FROM draft_project WHERE draft_id = ?";
    $stmt_get = $connection->prepare($get_current);
    $stmt_get->bind_param("i", $draft_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $current_allotment = $row['cash_allotment'];
        $current_balance = $row['balances'];
        
        file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Current values: Allotment: $current_allotment, Balance: $current_balance\n", FILE_APPEND);
        
        // Calculate the difference between allotments
        $allotment_difference = $new_cash_allotment - $current_allotment;
        
        // Calculate new balance based on your rules
        $new_balance = $current_balance + $allotment_difference;
        
        // If balance would go negative, set it to 0
        if ($new_balance < 0) {
            $new_balance = 0;
        }
        
        file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Calculated new balance: $new_balance\n", FILE_APPEND);
        
        // Update both values
        $sql = "UPDATE draft_project SET payee = ?, cash_allotment = ?, balances = ? WHERE draft_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("sssi", $payee, $new_cash_allotment, $new_balance, $draft_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Draft updated successfully!";
            file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Update successful\n", FILE_APPEND);
        } else {
            $_SESSION['error_message'] = "Error updating project: " . $stmt->error;
            file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Update failed: " . $stmt->error . "\n", FILE_APPEND);
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Could not retrieve current draft values";
        file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Failed to get current values\n", FILE_APPEND);
    }
    $stmt_get->close();

    $connection->close();

    $redirect_url = isset($_POST['redirect']) ? $_POST['redirect'] : 'rapidGOP.php';
    header("Location: $redirect_url");
    exit();
    
} else {
    file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Required fields missing\n", FILE_APPEND);
    $_SESSION['error_message'] = "Required fields missing";
    $redirect_url = isset($_POST['redirect']) ? $_POST['redirect'] : 'rapidGOP.php';
    header("Location: $redirect_url");
    exit();
}
?>