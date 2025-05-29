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
        $data = $result->fetch_assoc();
        
        error_log("DV query result: " . print_r($data, true));
        
        if (!$data) {
            error_log("DV not found for ID: " . $dv_id);
            throw new Exception('DV not found');
        }

        // Fetch all ORS numbers associated with this DV
        $ors_query = "SELECT o.ors_no, o.notes 
                     FROM dv_multiple_ors dmo 
                     JOIN ors o ON dmo.ors_id = o.ors_id 
                     WHERE dmo.dv_id = ?";
        $ors_stmt = $connection->prepare($ors_query);
        if ($ors_stmt === false) {
            error_log("Failed to prepare ORS query: " . $connection->error);
            throw new Exception('Failed to prepare ORS query: ' . $connection->error);
        }

        $ors_stmt->bind_param("i", $dv_id);
        if (!$ors_stmt->execute()) {
            error_log("Failed to execute ORS query: " . $ors_stmt->error);
            throw new Exception('Failed to execute ORS query: ' . $ors_stmt->error);
        }

        $ors_result = $ors_stmt->get_result();
        $ors_details = [];
        
        // If no results from dv_multiple_ors, try getting from main ors table
        if ($ors_result->num_rows === 0) {
            $main_ors_query = "SELECT o.ors_no, o.notes 
                             FROM dv d
                             JOIN ors o ON d.ors_id = o.ors_id 
                             WHERE d.dv_id = ?";
            $main_ors_stmt = $connection->prepare($main_ors_query);
            if ($main_ors_stmt === false) {
                error_log("Failed to prepare main ORS query: " . $connection->error);
                throw new Exception('Failed to prepare main ORS query: ' . $connection->error);
            }

            $main_ors_stmt->bind_param("i", $dv_id);
            if (!$main_ors_stmt->execute()) {
                error_log("Failed to execute main ORS query: " . $main_ors_stmt->error);
                throw new Exception('Failed to execute main ORS query: ' . $main_ors_stmt->error);
            }

            $main_ors_result = $main_ors_stmt->get_result();
            while ($ors_row = $main_ors_result->fetch_assoc()) {
                $ors_details[] = [
                    'ors_no' => $ors_row['ors_no'],
                    'notes' => $ors_row['notes']
                ];
            }
            $main_ors_stmt->close();
        } else {
            while ($ors_row = $ors_result->fetch_assoc()) {
                $ors_details[] = [
                    'ors_no' => $ors_row['ors_no'],
                    'notes' => $ors_row['notes']
                ];
            }
        }
        $ors_stmt->close();

        // Add ORS details to the response data
        $data['ors_details'] = $ors_details;

        // Fetch accounting entries
        $accounts_query = "SELECT 
            dv_history.*,
            account_title.account_title,
            account_title.account_code
            FROM dv_history
            LEFT JOIN account_title ON dv_history.account_id = account_title.account_id
            WHERE dv_history.dv_id = ?";

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

        // Add accounts to the response data
        $data['accounts'] = $accounts;

        echo json_encode($data);

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
