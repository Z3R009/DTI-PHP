<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $approver_id = $_POST['approver_id'];
    $approver_name = $_POST['approver_name'];
    $designation = $_POST['designation'];

    $sql = "UPDATE approver SET approver_name = ?, designation = ? WHERE approver_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssi", $approver_name, $designation, $approver_id);

    if ($stmt->execute()) {
        header('Location: approver.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>
