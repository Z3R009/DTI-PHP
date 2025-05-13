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
    // Check if we have any selections (either DVs or merged groups)
    $has_selections = (isset($_POST['selected_dvs']) && !empty($_POST['selected_dvs'])) || 
                     (isset($_POST['selected_merged_groups']) && !empty($_POST['selected_merged_groups']));
    
    if($has_selections) {
        // Initialize arrays
        $selected_dvs = isset($_POST['selected_dvs']) ? array_unique($_POST['selected_dvs']) : [];
        $selected_merged_groups = isset($_POST['selected_merged_groups']) ? array_unique($_POST['selected_merged_groups']) : [];
        
        $use_common_ada = isset($_POST['use_common_ada']) && $_POST['use_common_ada'] == '1';
        $account_id = isset($_POST['account_name']) ? $_POST['account_name'] : '';
        
        // Get account information if account_id is provided
        $fund_code = isset($_POST['fund_code']) ? $_POST['fund_code'] : '01101101';
        $bank_info = isset($_POST['bank_info']) ? $_POST['bank_info'] : 'LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH- 2075-9006-81';
        
        // Validate required fields
        $errors = [];
        
        if (empty($account_id)) {
            $errors[] = "Account selection is required.";
        }
        
        $payment_date = '';
        if(isset($_POST['batch_payment_date'])) {
            $payment_date = $_POST['batch_payment_date'];
        } elseif(isset($_POST['batch_date'])) {
            $payment_date = $_POST['batch_date'];
        }
        
        if (empty($payment_date)) {
            $errors[] = "Payment date is required.";
        }
        
        // Validate ADA references
        if ($use_common_ada) {
            if (!isset($_POST['common_ada_ref']) || empty($_POST['common_ada_ref'])) {
                $errors[] = "Common ADA reference number is required.";
            }
        } else {
            // Check individual references
            foreach ($selected_dvs as $dv_id) {
                if (!isset($_POST['ada_references']["dv_".$dv_id]) || empty($_POST['ada_references']["dv_".$dv_id])) {
                    $errors[] = "ADA reference number is required for DV ID: " . $dv_id;
                }
            }
            
            foreach ($selected_merged_groups as $merge_id) {
                if (!isset($_POST['ada_references']["merge_".$merge_id]) || empty($_POST['ada_references']["merge_".$merge_id])) {
                    $errors[] = "ADA reference number is required for Merged Group ID: " . $merge_id;
                }
            }
        }
        
        if (!empty($errors)) {
            $error_message = "Form validation failed:\n- " . implode("\n- ", $errors);
            error_log("Batch ADA validation errors: " . $error_message);
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error_message]);
                exit;
            } else {
                header('Location: ../pending_payments.php?error=' . urlencode($error_message));
                exit;
            }
        }
        
        // If validation passes, proceed with the rest of the code
        $remarks = isset($_POST['batch_remarks']) ? $_POST['batch_remarks'] : '';
        $month = date('m', strtotime($payment_date));
        $year = date('Y', strtotime($payment_date));
        $short_fund_code = substr($fund_code, -3);
        $reference_numbers = [];
        
        // Get reference numbers for regular DVs
        foreach($selected_dvs as $dv_id) {
            if ($use_common_ada) {
                $reference_numbers[] = $_POST['common_ada_ref'];
            } else {
                $individual_ref = isset($_POST['ada_references']["dv_".$dv_id]) ? $_POST['ada_references']["dv_".$dv_id] : '';
                $reference_numbers[] = $individual_ref;
            }
        }
        
        // Get reference numbers for merged groups
        foreach($selected_merged_groups as $merge_id) {
            if ($use_common_ada) {
                $reference_numbers[] = $_POST['common_ada_ref'];
            } else {
                $individual_ref = isset($_POST['ada_references']["merge_".$merge_id]) ? $_POST['ada_references']["merge_".$merge_id] : '';
                $reference_numbers[] = $individual_ref;
            }
        }
        
        sort($reference_numbers, SORT_NUMERIC);
        $first_ref = !empty($reference_numbers) ? $reference_numbers[0] : '000';
        $last_ref = !empty($reference_numbers) ? end($reference_numbers) : '000';
        $formatted_ada_ref = $short_fund_code . "-" . $month . "-" . $first_ref . "-" . $last_ref . "-" . $year;
        error_log("Batch ADA params: " . json_encode([
            'use_common_ada' => $use_common_ada,
            'reference_no' => $formatted_ada_ref,
            'payment_date' => $payment_date,
            'selected_dvs_count' => count($selected_dvs),
            'selected_merged_groups_count' => count($selected_merged_groups)
        ]));
        if ($use_common_ada && empty($_POST['common_ada_ref'])) {
            $response['message'] = "ADA reference number is required for batch payment.";
        } elseif (empty($payment_date)) {
            $response['message'] = "Payment date is required.";
        } elseif (empty($account_id)) {
            $response['message'] = "Account selection is required.";
        } elseif (!$use_common_ada) {
            // Validate individual reference numbers for each DV and merged group
            $missing_refs = [];
            
            // Check DVs
            foreach ($selected_dvs as $dv_id) {
                if (!isset($_POST['ada_references']["dv_".$dv_id]) || empty($_POST['ada_references']["dv_".$dv_id])) {
                    $missing_refs[] = "DV ID: " . $dv_id;
                }
            }
            
            // Check merged groups
            foreach ($selected_merged_groups as $merge_id) {
                if (!isset($_POST['ada_references']["merge_".$merge_id]) || empty($_POST['ada_references']["merge_".$merge_id])) {
                    $missing_refs[] = "Merged Group ID: " . $merge_id;
                }
            }
            
            if (!empty($missing_refs)) {
                $response['message'] = "Reference numbers are required for: " . implode(", ", $missing_refs);
            }
        }
        
        if (empty($response['message'])) {
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
                    'mergedGroups' => [],
                    'totalGross' => 0,
                    'totalWithholding' => 0,
                    'totalNet' => 0,
                    'fundCode' => $fund_code,
                    'bankInfo' => $bank_info,
                    'amountInWords' => '',
                    // Add the account information
                    'accountInfo' => [
                        'account_id' => $account_id,
                        'account_name' => isset($account_data['account_name']) ? $account_data['account_name'] : '',
                        'account_number' => isset($account_data['account_number']) ? $account_data['account_number'] : '',
                        'nca_no' => isset($nca_no) ? $nca_no : '',
                        'nca_date' => isset($nca_date) ? $nca_date : '',
                        'fund_source' => isset($fund_source) ? $fund_source : '',
                        'description' => isset($description) ? $description : ''
                    ]
                ];
                
                $total_gross = 0;
                $total_withholding = 0;
                $total_net = 0;
                
                // Process regular DVs
                foreach($selected_dvs as $dv_id) {
                    $amount_query = "SELECT d.*, d.net_amount, d.vat_amount, d.tax_1_amount, d.tax_2_amount, 
                                    p.payee_name, p.bank_acc_no, o.ors_no, o.purpose, at.account_code 
                                    FROM dv d 
                                    JOIN ors o ON d.ors_id = o.ors_id 
                                    JOIN payee p ON o.payee_id = p.payee_id 
                                    LEFT JOIN account_title at ON o.account_id = at.account_id
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
                        $individual_ref = isset($_POST['ada_references']["dv_".$dv_id]) ? $_POST['ada_references']["dv_".$dv_id] : '';
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
                        'account_code' => $dv_data['account_code'],
                        'purpose' => $dv_data['purpose'],
                        'gross_amount' => $gross_amount,
                        'withholding_tax' => $withholding_tax,
                        'net_amount' => $amount,
                        'reference_no' => $dv_reference_no,
                        'is_merged' => false
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
                
                // Process merged payee groups
                require_once 'get_merged_payees.php';
                
                foreach($selected_merged_groups as $merge_id) {
                    // Get merged group data
                    $merge_query = "SELECT mp.*, mp.merge_name, mp.description, mp.payee_type
                                  FROM merged_payees mp
                                  WHERE mp.merge_id = ?";
                    $merge_stmt = $connection->prepare($merge_query);
                    $merge_stmt->bind_param("i", $merge_id);
                    $merge_stmt->execute();
                    $merge_result = $merge_stmt->get_result();
                    $merge_data = $merge_result->fetch_assoc();
                    
                    if (!$merge_data) {
                        continue;
                    }
                    
                    // Get all DVs in this merged group
                    $dvs_query = "SELECT d.*, d.net_amount, d.vat_amount, d.tax_1_amount, d.tax_2_amount,
                                p.payee_name, p.bank_acc_no, o.ors_no, o.purpose, at.account_code
                                FROM merged_payee_items mpi
                                JOIN dv d ON mpi.dv_id = d.dv_id
                                JOIN ors o ON d.ors_id = o.ors_id
                                JOIN payee p ON o.payee_id = p.payee_id
                                LEFT JOIN account_title at ON o.account_id = at.account_id
                                WHERE mpi.merge_id = ?";
                    $dvs_stmt = $connection->prepare($dvs_query);
                    $dvs_stmt->bind_param("i", $merge_id);
                    $dvs_stmt->execute();
                    $dvs_result = $dvs_stmt->get_result();
                    
                    // Set reference number for the merged group
                    if ($use_common_ada) {
                        $merge_reference_no = $formatted_ada_ref;
                    } else {
                        $individual_ref = isset($_POST['ada_references']["merge_".$merge_id]) ? $_POST['ada_references']["merge_".$merge_id] : '';
                        if (!empty($individual_ref)) {
                            $merge_reference_no = $short_fund_code . "-" . $month . "-" . $individual_ref . "-" . $year;
                        } else {
                            throw new Exception("Reference number is required for merged group ID: " . $merge_id);
                        }
                    }
                    
                    $group_gross = 0;
                    $group_withholding = 0;
                    $group_net = 0;
                    $merged_dvs = [];
                    
                    // Process each DV in the merged group
                    while ($dv_data = $dvs_result->fetch_assoc()) {
                        $dv_id = $dv_data['dv_id'];
                        $amount = $dv_data['net_amount'];
                        $gross_amount = $dv_data['net_amount'] + $dv_data['vat_amount'] + $dv_data['tax_1_amount'] + $dv_data['tax_2_amount'];
                        $withholding_tax = $dv_data['vat_amount'] + $dv_data['tax_1_amount'] + $dv_data['tax_2_amount'];
                        
                        // Add to batch_ada_dvs table
                        $insert_dv_stmt->bind_param(
                            "iisddd",
                            $batch_id,
                            $dv_id,
                            $merge_reference_no,
                            $gross_amount,
                            $withholding_tax,
                            $amount
                        );
                        $insert_dv_stmt->execute();
                        
                        // Create payment record for each DV
                        $insert_query = "INSERT INTO payment (dv_id, payment_date, payment_type, reference_no, ada_no, amount, remarks, created_by, status) 
                                        VALUES (?, ?, 'ADA', ?, ?, ?, ?, 'Cashier', 'Pending')";
                        $stmt = $connection->prepare($insert_query);
                        $stmt->bind_param("isssds", $dv_id, $payment_date, $merge_reference_no, $merge_reference_no, $amount, $remarks);
                        $stmt->execute();
                        
                        // Update DV status but don't delete from merged group
                        $update_dv = "UPDATE dv SET status = 'Processing' WHERE dv_id = ?";
                        $update_stmt = $connection->prepare($update_dv);
                        $update_stmt->bind_param("i", $dv_id);
                        $update_stmt->execute();
                        
                        // Add to group totals
                        $group_gross += $gross_amount;
                        $group_withholding += $withholding_tax;
                        $group_net += $amount;
                        
                        // Add to merged DVs array
                        $merged_dvs[] = [
                            'dv_id' => $dv_id,
                            'dv_no' => $dv_data['dv_no'],
                            'payee_name' => $dv_data['payee_name'],
                            'bank_account' => $dv_data['bank_acc_no'] ?? 'N/A',
                            'ors_no' => $dv_data['ors_no'],
                            'account_code' => $dv_data['account_code'],
                            'purpose' => $dv_data['purpose'],
                            'gross_amount' => $gross_amount,
                            'withholding_tax' => $withholding_tax,
                            'net_amount' => $amount
                        ];
                    }
                    
                    // Add merged group to LDDAP data
                    $lddap_data['mergedGroups'][] = [
                        'merge_id' => $merge_id,
                        'merge_name' => $merge_data['merge_name'],
                        'payee_type' => $merge_data['payee_type'],
                        'description' => $merge_data['description'],
                        'dvs' => $merged_dvs,
                        'gross_amount' => $group_gross,
                        'withholding_tax' => $group_withholding,
                        'net_amount' => $group_net,
                        'reference_no' => $merge_reference_no
                    ];
                    
                    // Add merged group as a special entry in the main DVs list
                    $lddap_data['dvs'][] = [
                        'dv_id' => 'merge_' . $merge_id,
                        'dv_no' => 'MERGED',
                        'payee_name' => $merge_data['merge_name'] . ' (Merged Group)',
                        'bank_account' => $merged_dvs[0]['bank_account'] ?? 'N/A', // Use first DV's bank account
                        'ors_no' => $merged_dvs[0]['ors_no'] ?? 'MULTIPLE', // Use first ORS number
                        'account_code' => $merged_dvs[0]['account_code'] ?? 'MULTIPLE', // Use first account code
                        'purpose' => $merge_data['description'] ?: 'Merged payment for multiple vouchers',
                        'gross_amount' => $group_gross,
                        'withholding_tax' => $group_withholding,
                        'net_amount' => $group_net,
                        'reference_no' => $merge_reference_no,
                        'is_merged' => true,
                        'merge_id' => $merge_id
                    ];
                    
                    // Add to total
                    $total_gross += $group_gross;
                    $total_withholding += $group_withholding;
                    $total_net += $group_net;
                }
                
                // Mark all processed merged payees as processed
                if (!empty($selected_merged_groups)) {
                    // Check if processed column exists in the merged_payees table
                    $check_column_sql = "SHOW COLUMNS FROM merged_payees LIKE 'processed'";
                    $column_exists = $connection->query($check_column_sql);
                    
                    if ($column_exists && $column_exists->num_rows > 0) {
                        // The processed column exists, mark as processed
                        $placeholders = str_repeat('?,', count($selected_merged_groups) - 1) . '?';
                        $mark_processed_query = "UPDATE merged_payees SET processed = 1 WHERE merge_id IN ($placeholders)";
                        $mark_processed_stmt = $connection->prepare($mark_processed_query);
                        
                        $types = str_repeat('i', count($selected_merged_groups));
                        $mark_processed_stmt->bind_param($types, ...$selected_merged_groups);
                        $mark_processed_stmt->execute();
                    } else {
                        // The processed column doesn't exist yet, log this condition
                        error_log("Cannot mark merged payees as processed: 'processed' column does not exist in merged_payees table");
                    }
                }
                
                // Update the total in batch_ada
                $update_batch_sql = "UPDATE batch_ada SET 
                                    total_gross = ?, 
                                    total_withholding = ?, 
                                    total_net = ? 
                                    WHERE batch_id = ?";
                $update_batch_stmt = $connection->prepare($update_batch_sql);
                $update_batch_stmt->bind_param(
                    "dddi", 
                    $total_gross, 
                    $total_withholding, 
                    $total_net,
                    $batch_id
                );
                $update_batch_stmt->execute();
                
                // Update the selected account's draft project balance
                if (!empty($account_id)) {
                    // Retrieve the current draft_project for this account
                    $draft_query = "SELECT draft_id, balances FROM draft_project 
                                   WHERE account_id = ? 
                                   ORDER BY created_at DESC LIMIT 1";
                    $draft_stmt = $connection->prepare($draft_query);
                    $draft_stmt->bind_param("i", $account_id);
                    $draft_stmt->execute();
                    $draft_result = $draft_stmt->get_result();
                    
                    if ($draft_data = $draft_result->fetch_assoc()) {
                        // Reduce the balance by the total payment amount
                        $new_balance = $draft_data['balances'] - $total_net;
                        
                        // Ensure balance doesn't go negative
                        if ($new_balance < 0) {
                            $new_balance = 0;
                        }
                        
                        // Update the draft_project balance
                        $update_draft = "UPDATE draft_project SET balances = ? WHERE draft_id = ?";
                        $update_draft_stmt = $connection->prepare($update_draft);
                        $update_draft_stmt->bind_param("di", $new_balance, $draft_data['draft_id']);
                        $update_draft_stmt->execute();
                        
                        error_log("Updated draft project balance - Draft ID: " . $draft_data['draft_id'] . 
                                 ", Previous Balance: " . $draft_data['balances'] . 
                                 ", New Balance: " . $new_balance . 
                                 ", Total payment: " . $total_net);
                    } else {
                        error_log("No draft project found for account ID: " . $account_id);
                    }
                } else {
                    error_log("No account ID provided for batch payment");
                }
                
                $lddap_data['totalGross'] = $total_gross;
                $lddap_data['totalWithholding'] = $total_withholding;
                $lddap_data['totalNet'] = $total_net;
                $lddap_data['has_multiple_references'] = !$use_common_ada;
                $lddap_data['has_merged_groups'] = !empty($selected_merged_groups);
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
                $response['data'] = [
                    'batch_id' => $batch_id,
                    'reference_no' => $formatted_ada_ref,
                    'payment_date' => $payment_date,
                    'total_gross' => $total_gross,
                    'total_net' => $total_net
                ];
                
                // If this is a regular form submission (not AJAX), redirect
                if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
                    $_SESSION['success_message'] = $response['message'];
                    header('Location: ' . $response['redirect']);
                    exit;
                }
            } catch (Exception $e) {
                $connection->rollback();
                $response['message'] = "Error recording batch ADA payment: " . $e->getMessage();
            }
        }
    } else {
        $response['message'] = "No items selected for batch ADA payment.";
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