<?php
session_start();
include '../DBConnection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'book_keeper') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please log in again.'
    ]);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Get form data
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$userId = $_SESSION['user_id'];

// Validate inputs
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);
    exit;
}

// Check if new password and confirm password match
if ($newPassword !== $confirmPassword) {
    echo json_encode([
        'success' => false,
        'message' => 'New password and confirm password do not match.'
    ]);
    exit;
}

// Validate password strength
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.'
    ]);
    exit;
}

try {
    // Get the current user's password hash
    $stmt = $connection->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found.'
        ]);
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect.'
        ]);
        exit;
    }
    
    // Hash new password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in database
    $updateStmt = $connection->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $updateStmt->bind_param("si", $newPasswordHash, $userId);
    $updateStmt->execute();
    
    if ($updateStmt->affected_rows > 0) {
        // Log the password change
        $logStmt = $connection->prepare("INSERT INTO user_activity_log (user_id, activity_type, description, ip_address) VALUES (?, ?, ?, ?)");
        $activityType = 'password_change';
        $description = 'Password changed successfully';
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $logStmt->bind_param("isss", $userId, $activityType, $description, $ipAddress);
        $logStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Password changed successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update password. Please try again.'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
} finally {
    // Close statements
    if (isset($stmt)) $stmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($logStmt)) $logStmt->close();
}
?> 