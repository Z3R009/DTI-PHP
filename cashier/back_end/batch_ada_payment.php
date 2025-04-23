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
        // Fix duplication issue by removing duplicate DV IDs
        $selected_dvs = array_unique($_POST['selected_dvs']);
        
        $use_common_ada = isset($_POST['use_common_ada']) && $_POST['use_common_ada'] == '1';
        
        // Get fund code
        $fund_code = isset($_POST['fund_code']) ? $_POST['fund_code'] : '01101101';
        
        // Get bank info
        $bank_info = isset($_POST['bank_info']) ? $_POST['bank_info'] : 'LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH- 2075-9006-81';
        
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
        
        // Format the ADA reference number: fund_code-month-series-year
        $month = date('m', strtotime($payment_date));
        $year = date('Y', strtotime($payment_date));
        
        // Get only the last 3 digits of fund code
        $short_fund_code = substr($fund_code, -3);
        $formatted_ada_ref = $short_fund_code . "-" . $month . "-" . $common_reference_no . "-" . $year;
        
        // Debug log
        error_log("Batch ADA params: " . json_encode([
            'use_common_ada' => $use_common_ada,
            'reference_no' => $formatted_ada_ref,
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
                // Create session data for LDDAP-APA form - use existing session
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
                    if ($use_common_ada) {
                        $dv_reference_no = $formatted_ada_ref;
                    } else {
                        // If using individual references, format each one
                        $individual_ref = isset($_POST['ada_references'][$dv_id]) ? $_POST['ada_references'][$dv_id] : '';
                        $dv_reference_no = $short_fund_code . "-" . $month . "-" . $individual_ref . "-" . $year;
                    }
                    
                    // Add to LDDAP data
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
                        'reference_no' => $dv_reference_no // Add individual reference number for each DV
                    ];
                    
                    $total_gross += $gross_amount;
                    $total_withholding += $withholding_tax;
                    $total_net += $amount;
                    
                    // Insert payment record for this DV
                    $insert_query = "INSERT INTO payment (dv_id, payment_date, payment_type, reference_no, amount, remarks, created_by, status) 
                                    VALUES (?, ?, 'ADA', ?, ?, ?, 'Cashier', 'Pending')";
                    
                    $stmt = $connection->prepare($insert_query);
                    $stmt->bind_param("issds", $dv_id, $payment_date, $dv_reference_no, $amount, $remarks);
                    $stmt->execute();
                    
                    // Update DV status to 'Processing'
                    $update_dv = "UPDATE dv SET status = 'Processing' WHERE dv_id = ?";
                    $update_stmt = $connection->prepare($update_dv);
                    $update_stmt->bind_param("i", $dv_id);
                    $update_stmt->execute();
                }
                
                // Add totals to LDDAP data
                $lddap_data['totalGross'] = $total_gross;
                $lddap_data['totalWithholding'] = $total_withholding;
                $lddap_data['totalNet'] = $total_net;
                $lddap_data['has_multiple_references'] = !$use_common_ada;
                $lddap_data['amountInWords'] = customNumberToWords($total_net);
                
                // Store in session for later access if needed
                $_SESSION['lddap_data'] = $lddap_data;
                
                // Find the first and last ADA series numbers
                if (!$use_common_ada && count($lddap_data['dvs']) > 0) {
                    $series_numbers = [];
                    foreach ($lddap_data['dvs'] as $dv) {
                        // Extract the series part (third element) from the reference number
                        $parts = explode('-', $dv['reference_no']);
                        if (count($parts) >= 4) {
                            $series_numbers[] = $parts[2];
                        }
                    }
                    
                    // If we have multiple series numbers, create a range format
                    if (count($series_numbers) > 1) {
                        sort($series_numbers, SORT_NUMERIC);
                        $first_series = $series_numbers[0];
                        $last_series = $series_numbers[count($series_numbers) - 1];
                        
                        // Format the master LDDAP-ADA number to include the range
                        $lddap_ada_ref = $short_fund_code . "-" . $month . "-" . $first_series . "-" . $last_series . "-" . $year;
                        $lddap_data['referenceNo'] = $lddap_ada_ref;
                        $formatted_ada_ref = $lddap_ada_ref;
                    }
                }
                
                // Commit transaction
                $connection->commit();
                
                // Store LDDAP data in local storage via redirect parameter
                $lddap_data_json = json_encode($lddap_data);
                $storage_key = 'lddap_' . $formatted_ada_ref;
                
                $response['success'] = true;
                $response['message'] = "Batch ADA payment has been recorded successfully!";
                $response['redirect'] = "../pending_payments.php?success=3&lddap_ref=" . urlencode($formatted_ada_ref) . 
                                        "&storage_key=" . urlencode($storage_key) . 
                                        "&lddap_data=" . urlencode($lddap_data_json);
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
// New code to handle ADA print completion
elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_ada_printed'])) {
    if (isset($_POST['reference_no']) && !empty($_POST['reference_no'])) {
        $reference_no = $_POST['reference_no'];
        $dv_ids = isset($_POST['dv_ids']) ? $_POST['dv_ids'] : [];
        
        // Begin transaction
        $connection->begin_transaction();
        
        try {
            // If dv_ids are not explicitly provided, fetch them based on the reference number
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
            
            // Update payment status to 'Complete'
            $update_payment_query = "UPDATE payment SET status = 'Complete' WHERE reference_no = ? AND payment_type = 'ADA'";
            $update_payment_stmt = $connection->prepare($update_payment_query);
            $update_payment_stmt->bind_param("s", $reference_no);
            $update_payment_stmt->execute();
            
            // Update all related DVs to 'Complete'
            foreach ($dv_ids as $dv_id) {
                $update_dv_query = "UPDATE dv SET status = 'Complete' WHERE dv_id = ?";
                $update_dv_stmt = $connection->prepare($update_dv_query);
                $update_dv_stmt->bind_param("i", $dv_id);
                $update_dv_stmt->execute();
            }
            
            // Commit transaction
            $connection->commit();
            
            // Prepare response
            $response['success'] = true;
            $response['message'] = "ADA payment marked as complete and printed successfully!";
            $response['redirect'] = "../completed_payments.php?success=1";
        } catch (Exception $e) {
            // Roll back transaction on error
            $connection->rollback();
            $response['message'] = "Error marking ADA as printed: " . $e->getMessage();
            $response['redirect'] = "../pending_payments.php?error=" . urlencode($response['message']);
        }
        
        // Return response based on the request type
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
    // Redirect if no form submission
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