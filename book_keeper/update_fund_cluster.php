<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $fund_cluster_id = $_POST['fund_cluster_id'];
    $fund_cluster_name = $_POST['fund_cluster_name'];
    $uacs_code = $_POST['uacs_code'];
    $status = $_POST['status'];

    $sql = "UPDATE fund_cluster SET fund_cluster_name = ?, uacs_code = ?, status = ? WHERE fund_cluster_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sisi", $fund_cluster_name, $uacs_code, $status, $fund_cluster_id);

    if ($stmt->execute()) {
        header('Location: fund_cluster.php?updated=success');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>