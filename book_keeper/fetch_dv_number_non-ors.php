<?php
include '../DBconnection.php'; // Ensure correct DB connection

// Validate required inputs
if (!isset($_POST['fund_cluster_id']) || empty($_POST['fund_cluster_id'])) {
    echo json_encode(["success" => false, "error" => "Fund cluster ID is missing"]);
    exit;
}

if (!isset($_POST['uacs_code']) || empty($_POST['uacs_code'])) {
    echo json_encode(["success" => false, "error" => "UACS code is missing"]);
    exit;
}

$fund_cluster_id = $_POST['fund_cluster_id']; // for DB storage if needed later
$uacs_code = $_POST['uacs_code']; // used for display in DV number

// Get date or fallback to current
if (isset($_POST['date']) && !empty($_POST['date'])) {
    $date = $_POST['date'];
    $year = date("y", strtotime($date));
    $month = date("m", strtotime($date));
} else {
    $year = date("y");
    $month = date("m");
}

// DV number pattern: UACS-YEAR-MONTH-XXX
$like_pattern = "$uacs_code-$year-$month-%";

// Helper function to get max series number
function get_max_series($connection, $table, $like_pattern)
{
    $query = "SELECT dv_no FROM $table WHERE dv_no LIKE ? ORDER BY dv_no DESC LIMIT 1";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $like_pattern);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dv_no = $result->fetch_assoc()['dv_no'];
        return (int) substr($dv_no, -3); // Get last 3 digits
    }
    return 0;
}

// Get highest series from dv and dv_non_ors tables
$max_dv_series = get_max_series($connection, 'dv', $like_pattern);
$max_non_ors_series = get_max_series($connection, 'dv_non_ors', $like_pattern);

// Determine the next series number
$max_series = max($max_dv_series, $max_non_ors_series);
$new_series = str_pad($max_series + 1, 3, "0", STR_PAD_LEFT);

// Generate final DV number using UACS code
$dv_no = "$uacs_code-$year-$month-$new_series";

// Respond with JSON
echo json_encode(["success" => true, "dv_no" => $dv_no]);
?>