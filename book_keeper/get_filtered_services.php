<?php
include '../DBConnection.php';

if (isset($_POST['oopap_id'])) {
    $oopap_id = $_POST['oopap_id'];
    
    $sql = "SELECT services_id, services_name, code 
            FROM services 
            WHERE oopap_id = ?";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $oopap_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = array();
    while ($row = $result->fetch_assoc()) {
        $services[] = array(
            'services_id' => $row['services_id'],
            'services_name' => $row['services_name'],
            'code' => $row['code']
        );
    }
    
    echo json_encode($services);
    
    $stmt->close();
    $connection->close();
}
?> 