<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $oopap_id = $_POST['oopap_id'];
    $oopap_name = $_POST['oopap_name'];
    $description = $_POST['description'];

    $sql = "UPDATE oopap SET oopap_name = ?, description = ? WHERE oopap_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssi", $oopap_name, $description, $oopap_id);

    if ($stmt->execute()) {
        header('Location: oopap.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>