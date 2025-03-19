<?php
include '../DBConnection.php';

if (isset($_GET['id'])) {
    $dv_id = $_GET['id'];

    $stmt = $connection->prepare("
        SELECT dv.*, ors.*, 
            CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name) AS fund_cluster,
            responsibility_center.code,
            financial_object_code.object_code_id,
            oopap.oopap_id
        FROM dv
        INNER JOIN ors ON dv.ors_id = ors.ors_id
        LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
        LEFT JOIN financial_object_code ON ors.object_code_id = financial_object_code.object_code_id
        LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
        LEFT JOIN oopap ON ors.oopap_id = oopap.oopap_id
        WHERE dv.dv_id = ?
    ");

    $stmt->bind_param("i", $dv_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "No record found"]);
    }

    $stmt->close();
    $connection->close();
}
?>
