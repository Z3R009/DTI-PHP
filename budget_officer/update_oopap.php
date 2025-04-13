<?php
include '../DBConnection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ors_id = isset($_POST['ors_id']) ? intval($_POST['ors_id']) : 0;
    $oopap_id = isset($_POST['oopap_id']) ? intval($_POST['oopap_id']) : 0;

    if ($ors_id > 0 && $oopap_id > 0) {
        $update_query = "UPDATE ors SET oopap_id = ? WHERE ors_id = ?";
        $stmt = mysqli_prepare($connection, $update_query);
        mysqli_stmt_bind_param($stmt, "ii", $oopap_id, $ors_id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'OOPAP updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating OOPAP: ' . mysqli_error($connection)]);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ORS ID or OOPAP ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

mysqli_close($connection);
?> 