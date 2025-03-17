<?php
include '../DBConnection.php';

if (isset($_GET['id'])) {
    $orsId = $_GET['id'];
    $query = " SELECT 
  dv.*, ors.*, 
    fund_cluster.fund_cluster_id,
    responsibility_center.rc_id,
    financial_object_code.object_code_id,
    oopap.oopap_id
    FROM dv
    INNER JOIN ors ON dv.ors_id = ors.ors_id
    LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
    LEFT JOIN financial_object_code ON ors.object_code_id = financial_object_code.object_code_id
    LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
    LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id";
    $result = mysqli_query($connection, $query);
    $data = mysqli_fetch_assoc($result);
    echo json_encode($data);
}
?>