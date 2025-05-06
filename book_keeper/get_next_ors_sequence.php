<?php
include '../DBConnection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_code = $_POST['service_code'];
    $year = $_POST['year'];
    $month = $_POST['month'];

    // Query to get the latest sequence number for the given service code, year and month
    $sql = "SELECT ors_no FROM ors 
            WHERE ors_no LIKE ? 
            ORDER BY ors_id DESC LIMIT 1";
    
    // Handle special case for ADMIN&POLICY
    if ($service_code === 'ADMIN&POLICY') {
        $pattern = 'ADMIN&POLICY-' . $year . '-' . $month . '-%';
    } else {
        $pattern = $service_code . "-" . $year . "-%";
    }
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $pattern);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_ors_no = $row['ors_no'];
        // Extract the sequence number
        $parts = explode("-", $last_ors_no);
        $last_sequence = intval(end($parts));
        $next_sequence = $last_sequence + 1;
    } else {
        // If no existing record found, start with 1
        $next_sequence = 1;
    }

    echo json_encode(['next_sequence' => $next_sequence]);
    
    $stmt->close();
    $connection->close();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 