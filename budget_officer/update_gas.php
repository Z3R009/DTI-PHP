<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $gas_id = $_POST['gas_id'];
    $project = $_POST['project'];
    $uacs_code = $_POST['uacs_code'];
    $allotment = $_POST['allotment'];

    $sql = "UPDATE gas_allotment SET project = ?, uacs_code = ?, allotment = ? WHERE gas_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssi", $project, $uacs_code, $allotment, $gas_id);

    if ($stmt->execute()) {
        header('Location: gas.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>