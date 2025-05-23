<?php
include '../DBConnection.php';

// Define the payee information
$payee_name = 'DEPARTMENT OF TRADE AND INDUSTRY';
$payee_type = 'Internal';
$bank_acc_no = '2075-9006-81';

// Check if the payee already exists
$check_query = "SELECT payee_id FROM payee WHERE payee_name = ?";
$check_stmt = $connection->prepare($check_query);
$check_stmt->bind_param("s", $payee_name);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows == 0) {
    // Payee doesn't exist, insert it
    $insert_query = "INSERT INTO payee (payee_name, payee_type, bank_acc_no) VALUES (?, ?, ?)";
    $insert_stmt = $connection->prepare($insert_query);
    $insert_stmt->bind_param("sss", $payee_name, $payee_type, $bank_acc_no);
    
    if ($insert_stmt->execute()) {
        echo "Payee added successfully!";
    } else {
        echo "Error adding payee: " . $insert_stmt->error;
    }
    
    $insert_stmt->close();
} else {
    echo "Payee already exists!";
}

$check_stmt->close();
$connection->close();
?> 