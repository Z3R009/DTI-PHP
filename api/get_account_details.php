<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if account_id parameter is provided
if (!isset($_GET['account_id']) || empty($_GET['account_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Account ID is required'
    ]);
    exit;
}

$account_id = $_GET['account_id'];

// Prepare and execute the query to get account details
$stmt = $conn->prepare("SELECT fund_code, account_number FROM accounts WHERE id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $account = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'fund_code' => $account['fund_code'],
        'account_number' => $account['account_number']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Account not found'
    ]);
}

$stmt->close();
$conn->close();
?> 