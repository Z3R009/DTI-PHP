<?php
include '../DBconnection.php'; // Ensure correct DB connection

if (!isset($_POST['fund_cluster_id']) || empty($_POST['fund_cluster_id'])) {
    echo json_encode(["success" => false, "error" => "Fund cluster ID is missing"]);
    exit;
}

$fund_cluster = $_POST['fund_cluster_id']; // Directly use input as fund cluster

// Check if date is provided, otherwise use current date
if (isset($_POST['date']) && !empty($_POST['date'])) {
    $date = $_POST['date'];
    $year = date("y", strtotime($date)); // Get last two digits of the year from the selected date
    $month = date("m", strtotime($date)); // Get two-digit month from the selected date
} else {
    $year = date("y"); // Get last two digits of the year
    $month = date("m"); // Get two-digit month
}

// Check latest DV number for the selected fund cluster
$query = "SELECT jev_no FROM jev WHERE jev_no LIKE ? ORDER BY jev_no DESC LIMIT 1";
$like_pattern = "$fund_cluster-$year-$month-%";
$stmt = $connection->prepare($query);
$stmt->bind_param("s", $like_pattern);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $last_dv = $result->fetch_assoc()['jev_no'];
    $last_series = (int) substr($last_dv, -3); // Extract last 3 digits (series)
    $new_series = str_pad($last_series + 1, 3, "0", STR_PAD_LEFT); // Increment
} else {
    $new_series = "001"; // Start from 001 if no previous record
}

$jev_no = "$fund_cluster-$year-$month-$new_series";

// Return JSON response
echo json_encode(["success" => true, "jev_no" => $jev_no]);
?>