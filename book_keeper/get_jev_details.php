<?php
include '../DBConnection.php';

if (isset($_GET['id'])) {
    $dv_id = $_GET['id'];
    
    $query = "
        SELECT 
            dv.dv_id,
            dv.dv_no,
            dv.date as dv_date,
            ors.ors_id,
            ors.ors_no,
            ors.date as ors_date,
            GROUP_CONCAT(DISTINCT payee.payee_name) as payee_names,
            GROUP_CONCAT(DISTINCT CONCAT(fund_cluster.uacs_code, '-', fund_cluster.fund_cluster_name)) AS fund_clusters,
            GROUP_CONCAT(DISTINCT responsibility_center.code) as rc_codes,
            GROUP_CONCAT(DISTINCT account_title.account_title) as account_titles,
            GROUP_CONCAT(DISTINCT dv.amount) as amounts,
            GROUP_CONCAT(DISTINCT dv.type) as types,
            GROUP_CONCAT(DISTINCT account_title.account_code) as account_codes
        FROM dv
        LEFT JOIN ors ON dv.ors_id = ors.ors_id
        LEFT JOIN payee ON ors.payee_id = payee.payee_id
        LEFT JOIN fund_cluster ON ors.fund_cluster_id = fund_cluster.fund_cluster_id
        LEFT JOIN responsibility_center ON ors.rc_id = responsibility_center.rc_id
        LEFT JOIN account_title ON dv.account_id = account_title.account_id
        WHERE dv.dv_id = ?
        GROUP BY dv.dv_id, ors.ors_id
    ";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $dv_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Split the concatenated values into arrays
        $row['account_titles'] = explode(',', $row['account_titles']);
        $row['amounts'] = explode(',', $row['amounts']);
        $row['types'] = explode(',', $row['types']);
        $row['account_codes'] = explode(',', $row['account_codes']);
        
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No DV found']);
    }
    
    $stmt->close();
    $connection->close();
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?>
