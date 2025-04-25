<?php
require_once 'db_connection.php';

// Create batch_ada table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS batch_ada (
    batch_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_no VARCHAR(50) NOT NULL,
    payment_date DATE NOT NULL,
    fund_code VARCHAR(20) NOT NULL,
    bank_info VARCHAR(255) NOT NULL,
    total_gross DECIMAL(15,2) NOT NULL,
    total_withholding DECIMAL(15,2) NOT NULL,
    total_net DECIMAL(15,2) NOT NULL,
    remarks TEXT,
    status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(50) NOT NULL,
    UNIQUE KEY unique_reference (reference_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$connection->query($create_table_sql);

// Create batch_ada_dvs table to store associated DVs
$create_dvs_table_sql = "CREATE TABLE IF NOT EXISTS batch_ada_dvs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id INT NOT NULL,
    dv_id INT NOT NULL,
    reference_no VARCHAR(50) NOT NULL,
    gross_amount DECIMAL(15,2) NOT NULL,
    withholding_tax DECIMAL(15,2) NOT NULL,
    net_amount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (batch_id) REFERENCES batch_ada(batch_id) ON DELETE CASCADE,
    FOREIGN KEY (dv_id) REFERENCES dv(dv_id) ON DELETE CASCADE,
    UNIQUE KEY unique_dv_batch (dv_id, batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$connection->query($create_dvs_table_sql);

$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_batch_ada'])) {
    if(isset($_POST['selected_dvs']) && !empty($_POST['selected_dvs'])) {
        $selected_dvs = array_unique($_POST['selected_dvs']);
        $use_common_ada = isset($_POST['use_common_ada']) && $_POST['use_common_ada'] == '1';
        $fund_code = isset($_POST['fund_code']) ? $_POST['fund_code'] : '01101101';
        $bank_info = isset($_POST['bank_info']) ? $_POST['bank_info'] : 'LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH- 2075-9006-81';
        $common_reference_no = '';
        if(isset($_POST['batch_reference_no'])) {
            $common_reference_no = $_POST['batch_reference_no'];
        } elseif(isset($_POST['common_ada_ref'])) {
            $common_reference_no = $_POST['common_ada_ref'];
        }
        $payment_date = '';
        if(isset($_POST['batch_payment_date'])) {
            $payment_date = $_POST['batch_payment_date'];
        } elseif(isset($_POST['batch_date'])) {
            $payment_date = $_POST['batch_date'];
        }
        
        $remarks = isset($_POST['batch_remarks']) ? $_POST['batch_remarks'] : '';
        $month = date('m', strtotime($payment_date));
        $year = date('Y', strtotime($payment_date));
        $short_fund_code = substr($fund_code, -3);
        $reference_numbers = [];
        foreach($selected_dvs as $dv_id) {
            if ($use_common_ada) {
                $reference_numbers[] = $common_reference_no;
            } else {
                $individual_ref = isset($_POST['ada_references'][$dv_id]) ? $_POST['ada_references'][$dv_id] : '';
                $reference_numbers[] = $individual_ref;
            }
        }
        sort($reference_numbers, SORT_NUMERIC);
        $first_ref = $reference_numbers[0];
        $last_ref = end($reference_numbers);
        $formatted_ada_ref = $short_fund_code . "-" . $month . "-" . $first_ref . "-" . $last_ref . "-" . $year;
        error_log("Batch ADA params: " . json_encode([
            'use_common_ada' => $use_common_ada,
            'reference_no' => $formatted_ada_ref,
            'payment_date' => $payment_date,
            'selected_dvs_count' => count($selected_dvs)
        ]));
        if ($use_common_ada && empty($common_reference_no)) {
            $response['message'] = "ADA reference number is required for batch payment.";
        } elseif (empty($payment_date)) {
            $response['message'] = "Payment date is required.";
        } else {
            $connection->begin_transaction();
            
            try {
                // Insert into batch_ada table
                $insert_batch_sql = "INSERT INTO batch_ada (
                    reference_no, payment_date, fund_code, bank_info, 
                    total_gross, total_withholding, total_net, remarks, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Cashier')";
                
                $insert_batch_stmt = $connection->prepare($insert_batch_sql);
                $insert_batch_stmt->bind_param(
                    "ssssddds", 
                    $formatted_ada_ref, 
                    $payment_date, 
                    $fund_code, 
                    $bank_info,
                    $total_gross,
                    $total_withholding,
                    $total_net,
                    $remarks
                );
                $insert_batch_stmt->execute();
                $batch_id = $connection->insert_id;
                
                // Insert into batch_ada_dvs table for each DV
                $insert_dv_sql = "INSERT INTO batch_ada_dvs (
                    batch_id, dv_id, reference_no, gross_amount, withholding_tax, net_amount
                ) VALUES (?, ?, ?, ?, ?, ?)";
                
                $insert_dv_stmt = $connection->prepare($insert_dv_sql);
                
                $lddap_data = [
                    'referenceNo' => $formatted_ada_ref,
                    'paymentDate' => $payment_date,
                    'remarks' => $remarks,
                    'dvs' => [],
                    'totalGross' => 0,
                    'totalWithholding' => 0,
                    'totalNet' => 0,
                    'fundCode' => $fund_code,
                    'bankInfo' => $bank_info,
                    'amountInWords' => ''
                ];
                
                $total_gross = 0;
                $total_withholding = 0;
                $total_net = 0;
                
                foreach($selected_dvs as $dv_id) {
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
                    $gross_amount = $dv_data['net_amount'] + $dv_data['vat_amount'] + $dv_data['tax_1_amount'] + $dv_data['tax_2_amount'];
                    $withholding_tax = $dv_data['vat_amount'] + $dv_data['tax_1_amount'] + $dv_data['tax_2_amount'];
                    
                    if ($use_common_ada) {
                        $dv_reference_no = $formatted_ada_ref;
                    } else {
                        $individual_ref = isset($_POST['ada_references'][$dv_id]) ? $_POST['ada_references'][$dv_id] : '';
                        $dv_reference_no = $short_fund_code . "-" . $month . "-" . $individual_ref . "-" . $year;
                    }
                    
                    $insert_dv_stmt->bind_param(
                        "iisddd",
                        $batch_id,
                        $dv_id,
                        $dv_reference_no,
                        $gross_amount,
                        $withholding_tax,
                        $amount
                    );
                    $insert_dv_stmt->execute();
                    
                    $lddap_data['dvs'][] = [
                        'dv_id' => $dv_id,
                        'dv_no' => $dv_data['dv_no'],
                        'payee_name' => $dv_data['payee_name'],
                        'bank_account' => $dv_data['bank_acc_no'] ?? 'N/A',
                        'ors_no' => $dv_data['ors_no'],
                        'purpose' => $dv_data['purpose'],
                        'gross_amount' => $gross_amount,
                        'withholding_tax' => $withholding_tax,
                        'net_amount' => $amount,
                        'reference_no' => $dv_reference_no 
                    ];
                    
                    $total_gross += $gross_amount;
                    $total_withholding += $withholding_tax;
                    $total_net += $amount;
                    $insert_query = "INSERT INTO payment (dv_id, payment_date, payment_type, reference_no, ada_no, amount, remarks, created_by, status) 
                                    VALUES (?, ?, 'ADA', ?, ?, ?, ?, 'Cashier', 'Pending')";
                    
                    $stmt = $connection->prepare($insert_query);
                    $stmt->bind_param("isssds", $dv_id, $payment_date, $dv_reference_no, $dv_reference_no, $amount, $remarks);
                    $stmt->execute();
                    $update_dv = "UPDATE dv SET status = 'Processing' WHERE dv_id = ?";
                    $update_stmt = $connection->prepare($update_dv);
                    $update_stmt->bind_param("i", $dv_id);
                    $update_stmt->execute();
                }
                $lddap_data['totalGross'] = $total_gross;
                $lddap_data['totalWithholding'] = $total_withholding;
                $lddap_data['totalNet'] = $total_net;
                $lddap_data['has_multiple_references'] = !$use_common_ada;
                $lddap_data['amountInWords'] = customNumberToWords($total_net);
                $_SESSION['lddap_data'] = $lddap_data;
                if (!$use_common_ada && count($lddap_data['dvs']) > 0) {
                    $series_numbers = [];
                    foreach ($lddap_data['dvs'] as $dv) {
                        $parts = explode('-', $dv['reference_no']);
                        if (count($parts) >= 4) {
                            $series_numbers[] = $parts[2];
                        }
                    }
                    if (count($series_numbers) > 1) {
                        sort($series_numbers, SORT_NUMERIC);
                        $first_series = $series_numbers[0];
                        $last_series = $series_numbers[count($series_numbers) - 1];
                        $lddap_ada_ref = $short_fund_code . "-" . $month . "-" . $first_series . "-" . $last_series . "-" . $year;
                        $lddap_data['referenceNo'] = $lddap_ada_ref;
                        $formatted_ada_ref = $lddap_ada_ref;
                    }
                }
                $connection->commit();
                $lddap_data_json = json_encode($lddap_data);
                $storage_key = 'lddap_' . $formatted_ada_ref;
                
                $response['success'] = true;
                $response['message'] = "Batch ADA payment has been recorded successfully!";
                $response['redirect'] = "../pending_payments.php?success=3&lddap_ref=" . urlencode($formatted_ada_ref) . 
                                        "&storage_key=" . urlencode($storage_key) . 
                                        "&lddap_data=" . urlencode($lddap_data_json);
            } catch (Exception $e) {
                $connection->rollback();
                $response['message'] = "Error recording batch ADA payment: " . $e->getMessage();
            }
        }
    } else {
        $response['message'] = "No DVs selected for batch ADA payment.";
    }
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        if ($response['success']) {
            header('Location: ' . $response['redirect']);
            exit;
        } else {
            header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
            exit;
        }
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_ada_printed'])) {
    if (isset($_POST['reference_no']) && !empty($_POST['reference_no'])) {
        $reference_no = $_POST['reference_no'];
        $dv_ids = isset($_POST['dv_ids']) ? $_POST['dv_ids'] : [];
        $connection->begin_transaction();
        
        try {
            if (empty($dv_ids)) {
                $fetch_dvs_query = "SELECT dv_id FROM payment WHERE reference_no = ? AND payment_type = 'ADA'";
                $fetch_stmt = $connection->prepare($fetch_dvs_query);
                $fetch_stmt->bind_param("s", $reference_no);
                $fetch_stmt->execute();
                $result = $fetch_stmt->get_result();
                
                $dv_ids = [];
                while ($row = $result->fetch_assoc()) {
                    $dv_ids[] = $row['dv_id'];
                }
            }
            $update_payment_query = "UPDATE payment SET status = 'Complete' WHERE reference_no = ? AND payment_type = 'ADA'";
            $update_payment_stmt = $connection->prepare($update_payment_query);
            $update_payment_stmt->bind_param("s", $reference_no);
            $update_payment_stmt->execute();
            foreach ($dv_ids as $dv_id) {
                $update_dv_query = "UPDATE dv SET status = 'Complete' WHERE dv_id = ?";
                $update_dv_stmt = $connection->prepare($update_dv_query);
                $update_dv_stmt->bind_param("i", $dv_id);
                $update_dv_stmt->execute();
            }
            
            $connection->commit();
            $response['success'] = true;
            $response['message'] = "ADA payment marked as complete and printed successfully!";
            $response['redirect'] = "../completed_payments.php?success=1";
        } catch (Exception $e) {
            $connection->rollback();
            $response['message'] = "Error marking ADA as printed: " . $e->getMessage();
            $response['redirect'] = "../pending_payments.php?error=" . urlencode($response['message']);
        }
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else {
            if ($response['success']) {
                header('Location: ' . $response['redirect']);
            } else {
                header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
            }
            exit;
        }
    } else {
        $response['message'] = "Reference number is required to mark ADA as printed.";
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            header('Location: ../pending_payments.php?error=' . urlencode($response['message']));
        }
        exit;
    }
} else {
    header("Location: ../pending_payments.php?error=" . urlencode("Invalid form submission"));
    exit;
}

/**
 * Convert a number to words without requiring the NumberFormatter class
 * @param float $number
 * @return string
 */
function customNumberToWords($number) {
    $ones = array(
        0 => "Zero", 1 => "One", 2 => "Two", 3 => "Three", 4 => "Four", 
        5 => "Five", 6 => "Six", 7 => "Seven", 8 => "Eight", 9 => "Nine",
        10 => "Ten", 11 => "Eleven", 12 => "Twelve", 13 => "Thirteen", 14 => "Fourteen",
        15 => "Fifteen", 16 => "Sixteen", 17 => "Seventeen", 18 => "Eighteen", 19 => "Nineteen"
    );
    
    $tens = array(
        2 => "Twenty", 3 => "Thirty", 4 => "Forty", 5 => "Fifty",
        6 => "Sixty", 7 => "Seventy", 8 => "Eighty", 9 => "Ninety"
    );
    
    $scales = array(
        0 => "", 1 => "Thousand", 2 => "Million", 3 => "Billion",
        4 => "Trillion", 5 => "Quadrillion", 6 => "Quintillion"
    );
    
    // Handle zero
    if ($number == 0) {
        return "Zero Pesos Only";
    }
    
    // Handle negative numbers
    $sign = ($number < 0) ? "Negative " : "";
    $number = abs($number);
    
    // Split into whole and decimal parts
    $wholeNumber = floor($number);
    $decimal = round(($number - $wholeNumber) * 100);
    
    $words = "";
    
    // Convert whole number part
    $scaleCounter = 0;
    while ($wholeNumber > 0) {
        $chunk = $wholeNumber % 1000;
        if ($chunk > 0) {
            $chunkWords = "";
            
            $hundreds = floor($chunk / 100);
            $tensUnits = $chunk % 100;
            
            // Handle hundreds
            if ($hundreds > 0) {
                $chunkWords .= $ones[$hundreds] . " Hundred";
                if ($tensUnits > 0) {
                    $chunkWords .= " ";
                }
            }
            
            // Handle tens and units
            if ($tensUnits > 0) {
                if ($tensUnits < 20) {
                    $chunkWords .= $ones[$tensUnits];
                } else {
                    $unit = $tensUnits % 10;
                    $ten = floor($tensUnits / 10);
                    $chunkWords .= $tens[$ten];
                    if ($unit > 0) {
                        $chunkWords .= "-" . $ones[$unit];
                    }
                }
            }
            
            // Add scale (thousand, million, etc.)
            if ($scaleCounter > 0) {
                $chunkWords .= " " . $scales[$scaleCounter];
            }
            
            // Add to final words
            if ($words) {
                $words = $chunkWords . " " . $words;
            } else {
                $words = $chunkWords;
            }
        }
        
        $wholeNumber = floor($wholeNumber / 1000);
        $scaleCounter++;
    }
    
    // Format the result
    $words = $sign . $words;
    
    if ($decimal > 0) {
        $words .= " and " . $decimal . "/100";
    }
    
    $words .= " Pesos Only";
    
    return $words;
}
?>