<?php
include '../DBConnection.php';

$oopap_id = $_GET['oopap_id'] ?? '';

if ($oopap_id !== '') {
    // Filter by selected OOPAP category
    $query = "SELECT SUM(allotment) AS total_allotment FROM project WHERE oopap_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $oopap_id);
} else {
    // Get total allotment for all categories
    $query = "SELECT SUM(allotment) AS total_allotment FROM project";
    $stmt = $connection->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$totalAllotment = (double) ($row['total_allotment'] ?? 0);
$formattedTotal = "" . number_format($totalAllotment, 2);

echo json_encode(["total_allotment" => $formattedTotal]);

$stmt->close();
$connection->close();
?>