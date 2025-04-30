<?php
header('Content-Type: application/json');
require_once '../DBConnection.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'balance' => 0,
    'account_id' => null,
    'draft_id' => null
];

// Check if account_id was provided
if (!isset($_GET['account_id']) || empty($_GET['account_id'])) {
    $response['message'] = 'Account ID is required';
    echo json_encode($response);
    exit;
}

$account_id = intval($_GET['account_id']);
$response['account_id'] = $account_id;

try {
    // Query to get the most recent draft project for this account
    $query = "SELECT dp.draft_id, dp.account_id, dp.balances, dp.created_at, an.account_name 
              FROM draft_project dp 
              JOIN account_name an ON dp.account_id = an.account_id
              WHERE dp.account_id = ? 
              ORDER BY dp.created_at DESC 
              LIMIT 1";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        $response['success'] = true;
        $response['balance'] = floatval($data['balances']);
        $response['draft_id'] = $data['draft_id'];
        $response['account_name'] = $data['account_name'];
        $response['message'] = 'Balance retrieved successfully';
    } else {
        $response['message'] = 'No draft project found for this account';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response);
exit;
?> 