<?php
include '../DBconnection.php'; // Ensure correct DB connection

if (!isset($_POST['fund_cluster_id']) || empty($_POST['fund_cluster_id'])) {
    echo json_encode(["success" => false, "error" => "Fund cluster ID is missing"]);
    exit;
}

$fund_cluster = $_POST['fund_cluster_id'];

// Get date or fallback to current
if (isset($_POST['date']) && !empty($_POST['date'])) {
    $date = $_POST['date'];
    $year = date("y", strtotime($date));
    $month = date("m", strtotime($date));
} else {
    $year = date("y");
    $month = date("m");
}

$like_pattern = "$fund_cluster-$year-%";

// Helper function to get the max series from a table
function get_max_series($connection, $table, $like_pattern)
{
    $allowed_tables = ['dv', 'dv_non_ors'];
    if (!in_array($table, $allowed_tables)) {
        throw new Exception("Invalid table name.");
    }

    $query = "SELECT dv_no FROM $table WHERE dv_no LIKE ? ORDER BY dv_no DESC LIMIT 1";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $like_pattern);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dv_no = $result->fetch_assoc()['dv_no'];
        return (int) substr($dv_no, -3);
    }
    return 0;
}


// Get max series from both tables
$max_dv_series = get_max_series($connection, 'dv', $like_pattern);
$max_non_ors_series = get_max_series($connection, 'dv_non_ors', $like_pattern);

// Get the highest of the two
$max_series = max($max_dv_series, $max_non_ors_series);
$new_series = str_pad($max_series + 1, 3, "0", STR_PAD_LEFT);

// Generate new DV number
$dv_no = "$fund_cluster-$year-$month-$new_series";

// Return JSON response
echo json_encode(["success" => true, "dv_no" => $dv_no]);
?>