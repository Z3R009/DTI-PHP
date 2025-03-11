<?php
include '../DBConnection.php';

if (isset($_GET['id'])) {
    $orsId = $_GET['id'];
    $query = "SELECT 
                ors.*, 
                financial_object_code.object_name, 
                approver.approver_name 
              FROM ors
              LEFT JOIN financial_object_code ON ors.object_code_id = financial_object_code.object_code_id
              LEFT JOIN approver ON ors.approver_id = approver.approver_id
              WHERE ors.ors_id = $orsId";
    $result = mysqli_query($connection, $query);
    $data = mysqli_fetch_assoc($result);
    echo json_encode($data);
}
?>