<?php
include 'DBConnection.php';


$payee_name = 'DEPARTMENT OF TRADE AND INDUSTRY';
$payee_type = 'Internal';

// Check if payee already exists
$check_sql = "SELECT COUNT(*) FROM payee WHERE payee_name = ?";
$check_stmt = $connection->prepare($check_sql);
$check_stmt->bind_param("s", $payee_name);
$check_stmt->execute();
$check_stmt->store_result();
$check_stmt->bind_result($count);
$check_stmt->fetch();

if ($count > 0) {
    echo "Payee already exists!";
} else {
    $insert_sql = "INSERT INTO payee (payee_name, payee_type) VALUES (?, ?)";
    $stmt = $connection->prepare($insert_sql);

    if ($stmt === false) {
        echo "Error preparing the statement: " . $connection->error;
    } else {
        $stmt->bind_param("ss", $payee_name, $payee_type);
        if ($stmt->execute()) {
            echo "DTI payee has been added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$check_stmt->close();
$connection->close();
?>