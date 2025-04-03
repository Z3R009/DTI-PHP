<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $payee_id = $_POST['payee_id'];
    $payee_name = $_POST['payee_name'];
    $tin_no = $_POST['tin_no'];
    $bank_acc_no = $_POST['bank_acc_no'];
    $address = $_POST['address'];
    $nature = $_POST['nature'];
    $contact_no = $_POST['contact_no'];

    $sql = "UPDATE payee SET payee_name = ?, tin_no = ?, bank_acc_no = ?, address = ?, nature = ?, contact_no = ? WHERE payee_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssssssi", $payee_name, $tin_no, $bank_acc_no, $address, $nature, $contact_no, $payee_id);

    if ($stmt->execute()) {
        header('Location: payee.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>