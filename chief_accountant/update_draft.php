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
    $cash_allotment = $_POST['cash_allotment'];

    file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Draft ID: $draft_id, Payee: $payee, Cash_Allotment: $cash_allotment\n", FILE_APPEND);

    $sql = "UPDATE draft_project SET payee = ?, cash_allotment = ? WHERE draft_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssi", $payee, $cash_allotment, $draft_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Draft updated successfully!";
        file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Update successful\n", FILE_APPEND);
    } else {
        $_SESSION['error_message'] = "Error updating project: " . $stmt->error;
        file_put_contents('../debug.log', date('Y-m-d H:i:s') . " - Update failed: " . $stmt->error . "\n", FILE_APPEND);
    }

    $stmt->close();
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
