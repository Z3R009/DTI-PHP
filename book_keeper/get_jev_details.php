<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../DBConnection.php';

// Log request details
error_log("Request received - GET params: " . print_r($_GET, true));

if (!$connection) {
    error_log("Database connection failed: " . mysqli_connect_error());
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]));
}

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $dv_id = $_GET['id'];
    error_log("Processing DV ID: " . $dv_id);
    
    try {
        // Fetch DV details with related information
        $query = "SELECT 
            dv.*,
            ors.ors_no,
            CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
            responsibility_center.code,
            payee.payee_name
            FROM dv 
            LEFT JOIN ors ON dv.ors_id = ors.ors_id
            LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
            LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
            LEFT JOIN payee ON ors.payee_id = payee.payee_id
            WHERE dv.dv_id = ?";

        error_log("Preparing DV query: " . $query);
        
        $stmt = $connection->prepare($query);
        if ($stmt === false) {
            error_log("Failed to prepare DV query: " . $connection->error);
            throw new Exception('Failed to prepare DV query: ' . $connection->error);
        }

        $stmt->bind_param("i", $dv_id);
        error_log("Executing DV query with ID: " . $dv_id);
        
        if (!$stmt->execute()) {
            error_log("Failed to execute DV query: " . $stmt->error);
            throw new Exception('Failed to execute DV query: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $dv_details = $result->fetch_assoc();
        
        error_log("DV query result: " . print_r($dv_details, true));
        
        if (!$dv_details) {
            error_log("DV not found for ID: " . $dv_id);
            throw new Exception('DV not found');
        }

        // Fetch related accounts from dv_history
        $accounts_query = "SELECT 
            dv_history.*,
            account_title.account_title,
            account_title.account_code
            FROM dv_history 
            LEFT JOIN account_title ON dv_history.account_id = account_title.account_id
            WHERE dv_history.dv_id = ?";

        error_log("Preparing accounts query: " . $accounts_query);
        
        $accounts_stmt = $connection->prepare($accounts_query);
        if ($accounts_stmt === false) {
            error_log("Failed to prepare accounts query: " . $connection->error);
            throw new Exception('Failed to prepare accounts query: ' . $connection->error);
        }

        $accounts_stmt->bind_param("i", $dv_id);
        error_log("Executing accounts query with ID: " . $dv_id);
        
        if (!$accounts_stmt->execute()) {
            error_log("Failed to execute accounts query: " . $accounts_stmt->error);
            throw new Exception('Failed to execute accounts query: ' . $accounts_stmt->error);
        }
        
        $accounts_result = $accounts_stmt->get_result();
        
        $accounts = [];
        while ($account = $accounts_result->fetch_assoc()) {
            $accounts[] = $account;
        }

        error_log("Accounts query result: " . print_r($accounts, true));

        // Combine DV details and accounts
        $response = array_merge($dv_details, ['accounts' => $accounts]);
        
        error_log("Final response: " . print_r($response, true));
        echo json_encode($response);

    } catch (Exception $e) {
        error_log("Error occurred: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage(),
            'dv_id' => $dv_id,
            'trace' => $e->getTraceAsString()
        ]);
    }

} else {
    error_log("No DV ID provided in request");
    http_response_code(400);
    echo json_encode(['error' => 'DV ID is required']);
}
?>
