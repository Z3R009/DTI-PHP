<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $oo1_id = $_POST['oo1_id'];
    $project = $_POST['project'];
    $uacs_code = $_POST['uacs_code'];
    $allotment = $_POST['allotment'];

    $sql = "UPDATE oo1_allotment SET project = ?, uacs_code = ?, allotment = ? WHERE oo1_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssi", $project, $uacs_code, $allotment, $oo1_id);

    if ($stmt->execute()) {
        header('Location: oo1.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>