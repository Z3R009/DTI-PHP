<?php
// Utility functions for backend processing
require_once 'db_connection.php';

/**
 * Sanitize input data to prevent SQL injection and other attacks
 * 
 * @param string $data The data to sanitize
 * @return string The sanitized data
 */
function sanitizeInput($data) {
    global $connection;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if ($connection) {
        $data = $connection->real_escape_string($data);
    }
    return $data;
}

/**
 * Format a date for display
 * 
 * @param string $date The date to format
 * @param string $format The format to use (default: 'M d, Y')
 * @return string The formatted date
 */
function formatDate($date, $format = 'M d, Y') {
    if (!empty($date)) {
        return date($format, strtotime($date));
    }
    return '';
}

/**
 * Format an amount as currency
 * 
 * @param float $amount The amount to format
 * @param string $symbol The currency symbol (default: '₱')
 * @return string The formatted amount
 */
function formatCurrency($amount, $symbol = '₱') {
    return $symbol . number_format($amount, 2);
}

/**
 * Log an action to the system log
 * 
 * @param string $action The action to log
 * @param string $details Details about the action
 * @param int $user_id The ID of the user who performed the action
 * @return bool True if the log was successful, false otherwise
 */
function logAction($action, $details, $user_id = null) {
    global $connection;
    
    if (!$user_id && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    $query = "INSERT INTO system_log (action, details, user_id, ip_address, timestamp) 
              VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $connection->prepare($query);
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("ssis", $action, $details, $user_id, $ip);
    
    return $stmt->execute();
}

/**
 * Get the status of a DV by ID
 * 
 * @param int $dv_id The ID of the DV
 * @return string The status of the DV
 */
function getDvStatus($dv_id) {
    global $connection;
    
    $query = "SELECT status FROM dv WHERE dv_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $dv_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['status'];
    }
    
    return '';
}

/**
 * Check if a DV has existing payments
 * 
 * @param int $dv_id The ID of the DV
 * @return bool True if the DV has payments, false otherwise
 */
function dvHasPayments($dv_id) {
    global $connection;
    
    $query = "SELECT COUNT(*) as count FROM payment WHERE dv_id = ? AND status != 'Rejected'";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $dv_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['count'] > 0;
    }
    
    return false;
} 