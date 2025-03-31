<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $services_id = $_POST['services_id'];
    $services_name = $_POST['services_name'];
    $code = $_POST['code'];

    $sql = "UPDATE services SET services_name = ?, code = ?  WHERE services_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssi", $services_name, $code, $services_id);

    if ($stmt->execute()) {
        header('Location: services.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>