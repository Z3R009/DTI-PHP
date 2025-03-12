<?php
include '../DBConnection.php';

if (isset($_GET['id'])) {
  $orsId = $_GET['id'];
  $query = " SELECT 
  ors.*, 
  financial_object_code.object_name, 
  approver.approver_name,
  CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
  responsibility_center.code,
  oopap.oopap_name
FROM ors
LEFT JOIN financial_object_code ON ors.object_code_id = financial_object_code.object_code_id
LEFT JOIN approver ON ors.approver_id = approver.approver_id
LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
              WHERE ors.ors_id = $orsId";
  $result = mysqli_query($connection, $query);
  $data = mysqli_fetch_assoc($result);
  echo json_encode($data);
}
?>