<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $object_code_id = $_POST['object_code_id'];
    $object_name = $_POST['object_name'];
    $uacs_code = $_POST['uacs_code'];
    $status = $_POST['status'];

    $sql = "UPDATE financial_object_code SET object_name = ?, uacs_code = ?, status = ? WHERE object_code_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssi", $object_name, $uacs_code, $status, $object_code_id);

    if ($stmt->execute()) {
        header('Location: account_title.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>