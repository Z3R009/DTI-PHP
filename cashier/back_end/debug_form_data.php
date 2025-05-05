<?php
/**
 * Debug Form Data Script
 * Used to debug issues with form submissions in the pending_payments.php file
 */

// Set headers for JSON response
header('Content-Type: application/json');

// Log the request to a file
$log_file = __DIR__ . '/form_debug.log';
$timestamp = date('Y-m-d H:i:s');

// Log GET data
$log_data = "=== DEBUG REQUEST at $timestamp ===\n";
$log_data .= "GET Params:\n";
$log_data .= print_r($_GET, true);

// Log POST data
$log_data .= "\nPOST Params:\n";
$log_data .= print_r($_POST, true);

// Log FILES data if any
if (!empty($_FILES)) {
    $log_data .= "\nFILES Params:\n";
    $log_data .= print_r($_FILES, true);
}

// Log SERVER data
$log_data .= "\nSERVER Info:\n";
$log_data .= "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
$log_data .= "CONTENT_TYPE: " . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'Not set') . "\n";
$log_data .= "HTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
$log_data .= "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "\n";
$log_data .= "HTTP_REFERER: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not set') . "\n";

// Log raw input if not a multipart form
if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') === false) {
    $raw_input = file_get_contents('php://input');
    if (!empty($raw_input)) {
        $log_data .= "\nRaw Input:\n";
        $log_data .= $raw_input . "\n";
    }
}

$log_data .= "=== END DEBUG REQUEST ===\n\n";

// Write to log file
file_put_contents($log_file, $log_data, FILE_APPEND);

// Check for specified data to examine
$debug_type = $_GET['type'] ?? 'all';

// Debug specific elements based on request
$response = [
    'status' => 'success',
    'timestamp' => $timestamp,
    'message' => 'Debug data logged to ' . $log_file
];

// Add request data to response
if ($debug_type == 'all' || $debug_type == 'request') {
    $response['request'] = [
        'method' => $_SERVER['REQUEST_METHOD'],
        'get' => $_GET,
        'post' => $_POST
    ];
}

// If we're debugging checkboxes, run a direct database query
if ($debug_type == 'dv_status' || $debug_type == 'all') {
    // Include DB connection
    include_once __DIR__ . '/../../DBConnection.php';
    
    if (!isset($connection) || !$connection) {
        $response['db_status'] = 'error';
        $response['db_message'] = 'Database connection failed';
    } else {
        // Query to get counts of pending DVs
        $count_query = "SELECT 
            COUNT(*) as total_pending,
            SUM(CASE WHEN status = 'Endorsed by Chief Accountant' THEN 1 ELSE 0 END) as endorsed_count,
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_count
        FROM disbursement_voucher";
        
        $result = $connection->query($count_query);
        
        if ($result) {
            $counts = $result->fetch_assoc();
            $response['dv_counts'] = $counts;
        } else {
            $response['query_error'] = $connection->error;
        }
    }
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT); 