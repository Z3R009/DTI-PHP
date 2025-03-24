<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $project_id = $_POST['project_id'];
    $project_name = $_POST['project_name'];
    $uacs_code = $_POST['uacs_code'];
    $allotment = $_POST['allotment'];

    $sql = "UPDATE project SET project_name = ?, uacs_code = ?, allotment = ? WHERE project_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssi", $project_name, $uacs_code, $allotment, $project_id);

    if ($stmt->execute()) {
        header('Location: oo3.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>