<?php
include '../DBconnection.php'; // Ensure your database connectionection is correct

// Get fund cluster
$query = "SELECT uacs_code FROM fund_cluster LIMIT 1";
$result = $connection->query($query);
$fund_cluster = $result->fetch_assoc()['uacs_code'];

$year = date("y"); // Get last two digits of year
$month = date("m"); // Get two-digit month

// Check latest DV number
$query = "SELECT dv_no FROM dv WHERE dv_no LIKE '$fund_cluster-$year-$month-%' ORDER BY dv_no DESC LIMIT 1";
$result = $connection->query($query);

if ($result->num_rows > 0) {
    $last_dv = $result->fetch_assoc()['dv_no'];
    $last_series = (int) substr($last_dv, -3); // Extract last 3 digits (series)
    $new_series = str_pad($last_series + 1, 3, "0", STR_PAD_LEFT); // Increment
} else {
    $new_series = "001"; // Start from 001 if no previous record
}

$dv_no = "$fund_cluster-$year-$month-$new_series";

// Return JSON response
echo json_encode(["success" => true, "dv_no" => $dv_no]);
?>