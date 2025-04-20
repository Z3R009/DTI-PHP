<?php
// Process batch ADA payment functionality
require_once 'db_connection.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Check if the request is POST and the action is to submit batch ADA payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_batch_ada'])) {
    if(isset($_POST['selected_dvs']) && !empty($_POST['selected_dvs'])) {
        $selected_dvs = $_POST['selected_dvs'];
        $use_common_ada = isset($_POST['use_common_ada']) && $_POST['use_common_ada'] == '1';
        
        // Check for both possible parameter names
        $common_reference_no = '';
        if(isset($_POST['batch_reference_no'])) {
            $common_reference_no = $_POST['batch_reference_no'];
        } elseif(isset($_POST['common_ada_ref'])) {
            $common_reference_no = $_POST['common_ada_ref'];
        }
        
        // Check for both possible parameter names
        $payment_date = '';
        if(isset($_POST['batch_payment_date'])) {
            $payment_date = $_POST['batch_payment_date'];
        } elseif(isset($_POST['batch_date'])) {
            $payment_date = $_POST['batch_date'];
        }
        
        $remarks = isset($_POST['batch_remarks']) ? $_POST['batch_remarks'] : '';
        
        // Debug log
        error_log("Batch ADA params: " . json_encode([
            'use_common_ada' => $use_common_ada,
            'reference_no' => $common_reference_no,
            'payment_date' => $payment_date,
            'selected_dvs_count' => count($selected_dvs)
        ]));
        
        // Validate required fields
        if ($use_common_ada && empty($common_reference_no)) {
            $response['message'] = "ADA reference number is required for batch payment.";
        } elseif (empty($payment_date)) {
            $response['message'] = "Payment date is required.";
        } else {
            // Begin transaction
            $connection->begin_transaction();
            
            try {
                // Create a session variable to store batch ADA details for LDDAP-APA form
                $_SESSION['lddap_data'] = [
                    'reference_no' => $use_common_ada ? $common_reference_no : 'Multiple ADA References',
                    'payment_date' => $payment_date,
                    'remarks' => $remarks,
                    'dvs' => [],
                    'total_gross' => 0,
                    'total_withholding' => 0,
                    'total_net' => 0
                ];
                
                $total_gross = 0;
                $total_withholding = 0;
                $total_net = 0;
                
                foreach($selected_dvs as $dv_id) {
                    // Get the amount for this DV
                    $amount_query = "SELECT d.*, d.net_amount, d.vat_amount, d.tax_1_amount, d.tax_2_amount, 
                                    p.payee_name, p.bank_acc_no, o.ors_no, o.purpose 
                                    FROM dv d 
                                    JOIN ors o ON d.ors_id = o.ors_id 
                                    JOIN payee p ON o.payee_id = p.payee_id 
                                    WHERE d.dv_id = ?";
                    $amount_stmt = $connection->prepare($amount_query);
                    $amount_stmt->bind_param("i", $dv_id);
                    $amount_stmt->execute();
                    $amount_result = $amount_stmt->get_result();
                    $dv_data = $amount_result->fetch_assoc();
                    
                    $amount = $dv_data['net_amount'];
                    // Calculate gross amount properly as the sum of net amount plus tax amounts
                    $gross_amount = $dv_data['net_amount'] + $dv_data['vat_amount'] + $dv_data['tax_1_amount'] + $dv_data['tax_2_amount'];
                    $withholding_tax = $dv_data['vat_amount'] + $dv_data['tax_1_amount'] + $dv_data['tax_2_amount'];
                    
                    // Determine the reference number for this DV
                    $reference_no = $use_common_ada ? $common_reference_no : (isset($_POST['ada_references'][$dv_id]) ? $_POST['ada_references'][$dv_id] : '');
                    
                    // Add to LDDAP data
                    $_SESSION['lddap_data']['dvs'][] = [
                        'dv_id' => $dv_id,
                        'dv_no' => $dv_data['dv_no'],
                        'payee_name' => $dv_data['payee_name'],
                        'bank_account' => $dv_data['bank_acc_no'] ?? 'N/A',
                        'ors_no' => $dv_data['ors_no'],
                        'purpose' => $dv_data['purpose'],
                        'gross_amount' => $gross_amount,
                        'withholding_tax' => $withholding_tax,
                        'net_amount' => $amount,
                        'reference_no' => $reference_no // Add individual reference number
                    ];
                    
                    $total_gross += $gross_amount;
                    $total_withholding += $withholding_tax;
                    $total_net += $amount;
                    
                    // Insert payment record for this DV
                    $insert_query = "INSERT INTO payment (dv_id, payment_date, payment_type, reference_no, amount, remarks, created_by, status) 
                                    VALUES (?, ?, 'ADA', ?, ?, ?, 'Cashier', 'Pending')";
                    
                    $stmt = $connection->prepare($insert_query);
                    $stmt->bind_param("issds", $dv_id, $payment_date, $reference_no, $amount, $remarks);
                    $stmt->execute();
                    
                    // Update DV status to 'Processing'
                    $update_dv = "UPDATE dv SET status = 'Processing' WHERE dv_id = ?";
                    $update_stmt = $connection->prepare($update_dv);
                    $update_stmt->bind_param("i", $dv_id);
                    $update_stmt->execute();
                }
                
                // Add totals to LDDAP data
                $_SESSION['lddap_data']['total_gross'] = $total_gross;
                $_SESSION['lddap_data']['total_withholding'] = $total_withholding;
                $_SESSION['lddap_data']['total_net'] = $total_net;
                $_SESSION['lddap_data']['has_multiple_references'] = !$use_common_ada;
                
                // Commit transaction
                $connection->commit();
                
                $response['success'] = true;
                $response['message'] = "Batch ADA payment has been recorded successfully!";
                $response['redirect'] = "../generate_lddap.php?ref=" . urlencode($use_common_ada ? $common_reference_no : 'multiple');
            } catch (Exception $e) {
                // Roll back transaction on error
                $connection->rollback();
                $response['message'] = "Error recording batch ADA payment: " . $e->getMessage();
            }
        }
    } else {
        $response['message'] = "No DVs selected for batch ADA payment.";
    }
    
    // Return JSON response if it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        // Handle normal form submission
        if ($response['success']) {
            header('Location: ' . $response['redirect']);
            exit;
        } else {
            // Redirect back with error message
            header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
            exit;
        }
    }
} 