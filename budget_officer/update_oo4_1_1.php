<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $project_id = $_POST['project_id'];
    $allotment = $_POST['allotment'];

    $sql = "UPDATE project SET allotment = ? WHERE project_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $allotment, $project_id);

    if ($stmt->execute()) {
        header('Location: oo4_1_1.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>