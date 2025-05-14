<?php
include '../DBConnection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display to user, but log errors

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate parameters
    $service_code = isset($_POST['service_code']) ? trim($_POST['service_code']) : '';
    $year = isset($_POST['year']) ? trim($_POST['year']) : '';
    $month = isset($_POST['month']) ? trim($_POST['month']) : '';

    // Log the request
    error_log("get_next_ors_sequence.php called with: service_code=$service_code, year=$year, month=$month");

    // Validate inputs
    if (empty($service_code) || empty($year) || empty($month)) {
        error_log("Missing required parameters");
        echo json_encode(['next_sequence' => 1, 'error' => 'Missing parameters']);
        exit();
    }

    try {
        // Prepare SQL pattern based on service code
        // Special handling for service codes with special characters
        if ($service_code === 'ADMIN&POLICY') {
            $pattern = 'ADMIN&POLICY-' . $year . '-%';
        } else {
            $pattern = $service_code . '-' . $year . '-%';
        }

        // Query to get the latest sequence number for the given service code, year and month
        $sql = "SELECT ors_no FROM ors 
                WHERE ors_no LIKE ? 
                ORDER BY ors_id DESC LIMIT 1";

        $stmt = $connection->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $connection->error);
        }

        $stmt->bind_param("s", $pattern);
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $last_ors_no = $row['ors_no'];

            // Extract the sequence number
            $parts = explode("-", $last_ors_no);
            $last_sequence = intval(end($parts));
            $next_sequence = $last_sequence + 1;

            error_log("Found existing ORS number: $last_ors_no, next sequence: $next_sequence");
        } else {
            // If no existing record found, start with 1
            $next_sequence = 1;
            error_log("No existing ORS numbers found for pattern: $pattern, starting with 1");
        }

        echo json_encode(['next_sequence' => $next_sequence]);

    } catch (Exception $e) {
        error_log("Error in get_next_ors_sequence.php: " . $e->getMessage());
        // Return 1 as a default value if there's an error
        echo json_encode(['next_sequence' => 1, 'error' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $connection->close();
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed', 'next_sequence' => 1]);
}
?>