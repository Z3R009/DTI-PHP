<?php
include '../DBConnection.php';

$oopap_id = isset($_GET['oopap_id']) ? $_GET['oopap_id'] : '';

if ($oopap_id === '') {
    // If no OOPAP ID is provided, get total balance for all projects
    $query = "SELECT SUM(balances) AS total_balance FROM project";
} else {
    // If OOPAP ID is provided, get balance for specific OOPAP
    $query = "SELECT SUM(balances) AS total_balance FROM project WHERE oopap_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $oopap_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode(['total_balance' => number_format($row['total_balance'], 2)]);
    exit;
}

$result = $connection->query($query);
$row = $result->fetch_assoc();
echo json_encode(['total_balance' => number_format($row['total_balance'], 2)]);
?> 