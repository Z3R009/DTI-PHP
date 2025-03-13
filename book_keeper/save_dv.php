<?php
include '../DBConnection.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $fund_cluster_id = $_POST['fund_cluster_id'] ?? null;
    $date = $_POST['date'] ?? null;
    $dv_no = $_POST['dv_no'] ?? null;
    $mode_payment = $_POST['mode_payment'] ?? null;
    $payee_name = $_POST['payee_name'] ?? null;
    $tin_no = $_POST['tin_no'] ?? null;
    $address = $_POST['address'] ?? null;
    $notes = $_POST['notes'] ?? null;
    $rc_id = $_POST['rc_id'] ?? null;
    $oopap_id = $_POST['oopap_id'] ?? null;
    $amount = $_POST['amount'] ?? 0;
    $gross_amount = $_POST['gross_amount'] ?? 0;
    $vat = $_POST['vat'] ?? 0;
    $tax_1 = $_POST['tax_1'] ?? 0;
    $tax_2 = $_POST['tax_2'] ?? 0;
    $net_amount = $_POST['net_amount'] ?? 0;
    $approver_id = $_POST['approver_id'] ?? null;
    $budget_officer = $_POST['budget_officer'] ?? null;
    $chief_accountant = $_POST['chief_accountant'] ?? null;
    $regional_director = $_POST['regional_director'] ?? null;
    $check_no = $_POST['check_no'] ?? null;
    $bank_acc_no = $_POST['bank_acc_no'] ?? null;
    
    // Validate required fields
    if (!$date || !$dv_no || !$payee_name) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        exit;
    }
    
    // Prepare and execute the SQL statement
    $query = "INSERT INTO dv (
                fund_cluster_id, date, dv_no, mode_payment, 
                payee_name, tin_no, address, notes, 
                rc_id, oopap_id, amount, gross_amount, 
                vat, tax_1, tax_2, net_amount, 
                approver_id, budget_officer, chief_accountant, 
                regional_director, check_no, bank_acc_no
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $connection->prepare($query);
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $connection->error
        ]);
        exit;
    }
    
    $stmt->bind_param(
        "isssssssssdddddsssss",
        $fund_cluster_id, $date, $dv_no, $mode_payment,
        $payee_name, $tin_no, $address, $notes,
        $rc_id, $oopap_id, $amount, $gross_amount,
        $vat, $tax_1, $tax_2, $net_amount,
        $approver_id, $budget_officer, $chief_accountant,
        $regional_director, $check_no, $bank_acc_no
    );
    
    if ($stmt->execute()) {
        $dv_id = $connection->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Disbursement Voucher saved successfully',
            'dv_id' => $dv_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error saving Disbursement Voucher: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>