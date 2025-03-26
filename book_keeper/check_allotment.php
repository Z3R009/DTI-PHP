<?php
include '../DBConnection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$account_id = $_POST['account_id'] ?? null;
$oopap_id = $_POST['oopap_id'] ?? null;
$amount = $_POST['amount'] ?? 0;

if (!$account_id || !$oopap_id || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid parameters']);
    exit;
}

try {
    $sql = "SELECT project_id, balances 
            FROM project 
            WHERE account_id = ? AND oopap_id = ? AND balances >= ?";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iid", $account_id, $oopap_id, $amount);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'project_id' => $project['project_id'],
            'remaining_amount' => $project['balances']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient allotment'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$stmt->close();
$connection->close(); 