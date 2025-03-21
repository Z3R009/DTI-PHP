<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $oo4_1_1_id = $_POST['oo4_1_1_id'];
    $project = $_POST['project'];
    $uacs_code = $_POST['uacs_code'];
    $allotment = $_POST['allotment'];

    $sql = "UPDATE oo4_1_1_allotment SET project = ?, uacs_code = ?, allotment = ? WHERE oo4_1_1_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssi", $project, $uacs_code, $allotment, $oo4_1_1_id);

    if ($stmt->execute()) {
        header('Location: oo4_1_1.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>