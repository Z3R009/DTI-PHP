<?php
include '../../DBConnection.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Function to log errors
function logError($message, $data = null) {
    error_log("LDDAP Data Error: " . $message);
    if ($data !== null) {
        error_log("Error Data: " . print_r($data, true));
    }
}

try {
    if (!isset($_GET['ref']) || empty($_GET['ref'])) {
        throw new Exception('Reference number is required');
    }

    $reference_no = mysqli_real_escape_string($connection, $_GET['ref']);
    logError("Processing reference number: " . $reference_no);

    // First get the batch ID
    $batch_query = "SELECT batch_id, payment_date, remarks FROM batch_ada WHERE reference_no = ?";
    $batch_stmt = $connection->prepare($batch_query);
    if (!$batch_stmt) {
        throw new Exception("Failed to prepare batch query: " . $connection->error);
    }
    
    $batch_stmt->bind_param('s', $reference_no);
    if (!$batch_stmt->execute()) {
        throw new Exception("Failed to execute batch query: " . $batch_stmt->error);
    }
    
    $batch_result = $batch_stmt->get_result();
    if ($batch_result->num_rows === 0) {
        throw new Exception('ADA record not found for reference: ' . $reference_no);
    }

    $batch_row = $batch_result->fetch_assoc();
    $batch_id = $batch_row['batch_id'];
    logError("Found batch ID: " . $batch_id);

    // Get all DVs and their details
    $payments_query = "SELECT bad.*, d.dv_id, d.dv_no, d.net_amount as dv_net, d.vat_amount, d.tax_1_amount, d.tax_2_amount, 
                      bad.net_amount as amount, pa.payee_name, pa.bank_acc_no, o.ors_no, o.purpose, o.notes,
                      at.account_code, p.remarks, p.payment_date, mp.merge_id, mp.merge_name, mp.payee_type
                      FROM batch_ada_dvs bad
                      JOIN dv d ON bad.dv_id = d.dv_id
                      JOIN ors o ON d.ors_id = o.ors_id
                      JOIN payee pa ON o.payee_id = pa.payee_id
                      LEFT JOIN account_title at ON o.account_id = at.account_id
                      LEFT JOIN payment p ON p.dv_id = d.dv_id AND p.payment_type = 'ADA'
                      LEFT JOIN merged_payee_items mpi ON d.dv_id = mpi.dv_id
                      LEFT JOIN merged_payees mp ON mpi.merge_id = mp.merge_id
                      WHERE bad.batch_id = ?
                      AND (mp.merge_id IS NULL OR EXISTS (
                          SELECT 1 FROM merged_payee_items mpi2 
                          JOIN batch_ada_dvs bad2 ON mpi2.dv_id = bad2.dv_id 
                          WHERE mpi2.merge_id = mp.merge_id 
                          AND bad2.batch_id = ?
                          GROUP BY mpi2.merge_id
                          HAVING COUNT(DISTINCT bad2.dv_id) = (
                              SELECT COUNT(*) FROM merged_payee_items mpi3 
                              WHERE mpi3.merge_id = mp.merge_id
                          )
                      ))
                      ORDER BY mp.merge_id, pa.payee_name ASC";

    $stmt = $connection->prepare($payments_query);
    if (!$stmt) {
        throw new Exception("Failed to prepare payments query: " . $connection->error);
    }
    
    $stmt->bind_param('ii', $batch_id, $batch_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute payments query: " . $stmt->error);
    }
    
    $payments_result = $stmt->get_result();
    if ($payments_result->num_rows === 0) {
        throw new Exception('No DVs found for this ADA record');
    }

    logError("Found " . $payments_result->num_rows . " DVs for batch");

    // Initialize LDDAP data
    $lddap_data = [
        'reference_no' => $reference_no,
        'ada_no' => $reference_no,
        'payment_date' => $batch_row['payment_date'] ?? '',
        'remarks' => $batch_row['remarks'] ?? '',
        'dvs' => [],
        'mergedGroups' => [],
        'total_gross' => 0,
        'total_withholding' => 0,
        'total_net' => 0,
        'fundCode' => '01101101',
        'bankInfo' => 'LAND BANK OF THE PHILIPPINES- KORONADAL BRANCH- 2075-9006-81',
        'has_multiple_references' => false,
        'has_merged_groups' => false
    ];

    $total_gross = 0;
    $total_withholding = 0;
    $total_net = 0;
    $current_merge_id = null;
    $current_merge_group = null;
    $merged_dvs = [];

    // Process all DVs
    while ($row = $payments_result->fetch_assoc()) {
        $gross_amount = $row['dv_net'] + $row['vat_amount'] + $row['tax_1_amount'] + $row['tax_2_amount'];
        $net_amount = $row['amount'];
        $withholding_tax = $gross_amount - $row['dv_net'];
        
        // If this DV is part of a merged group in this batch
        if ($row['merge_id']) {
            if ($current_merge_id !== $row['merge_id']) {
                // If we were processing a previous merge group, add it to the LDDAP data
                if ($current_merge_group) {
                    $lddap_data['mergedGroups'][] = $current_merge_group;
                    $lddap_data['has_merged_groups'] = true;
                }
                
                // Start a new merge group
                $current_merge_id = $row['merge_id'];
                $current_merge_group = [
                    'merge_id' => $row['merge_id'],
                    'merge_name' => $row['merge_name'],
                    'payee_type' => $row['payee_type'],
                    'dvs' => [],
                    'gross_amount' => 0,
                    'withholding_tax' => 0,
                    'net_amount' => 0,
                    'batch_id' => $batch_id
                ];
            }
            
            // Add DV to current merge group
            $dv_data = [
                'dv_id' => $row['dv_id'],
                'dv_no' => $row['dv_no'],
                'payee_name' => $row['payee_name'],
                'bank_account' => $row['bank_acc_no'] ?? 'N/A',
                'ors_no' => $row['ors_no'],
                'account_code' => $row['account_code'] ?? '',
                'purpose' => $row['purpose'],
                'notes' => $row['notes'],
                'gross_amount' => $gross_amount,
                'withholding_tax' => $withholding_tax,
                'net_amount' => $net_amount,
                'reference_no' => $reference_no,
                'batch_id' => $batch_id
            ];
            
            $current_merge_group['dvs'][] = $dv_data;
            $current_merge_group['gross_amount'] += $gross_amount;
            $current_merge_group['withholding_tax'] += $withholding_tax;
            $current_merge_group['net_amount'] += $net_amount;
            
            // Add merged group as a special entry in the main DVs list
            $lddap_data['dvs'][] = [
                'dv_id' => 'merge_' . $row['merge_id'],
                'dv_no' => 'MERGED',
                'payee_name' => $row['merge_name'] . ' (Merged Group)',
                'bank_account' => $row['bank_acc_no'] ?? 'N/A',
                'ors_no' => $row['ors_no'] ?? 'MULTIPLE',
                'account_code' => $row['account_code'] ?? 'MULTIPLE',
                'purpose' => 'Merged payment for multiple vouchers',
                'gross_amount' => $current_merge_group['gross_amount'],
                'withholding_tax' => $current_merge_group['withholding_tax'],
                'net_amount' => $current_merge_group['net_amount'],
                'reference_no' => $reference_no,
                'is_merged' => true,
                'merge_id' => $row['merge_id'],
                'batch_id' => $batch_id
            ];
        } else {
            // Regular DV (not part of a merged group)
            $dv_data = [
                'dv_id' => $row['dv_id'],
                'dv_no' => $row['dv_no'],
                'payee_name' => $row['payee_name'],
                'bank_account' => $row['bank_acc_no'] ?? 'N/A',
                'ors_no' => $row['ors_no'],
                'account_code' => $row['account_code'] ?? '',
                'purpose' => $row['purpose'],
                'notes' => $row['notes'],
                'gross_amount' => $gross_amount,
                'withholding_tax' => $withholding_tax,
                'net_amount' => $net_amount,
                'reference_no' => $reference_no,
                'batch_id' => $batch_id
            ];
            
            $lddap_data['dvs'][] = $dv_data;
        }
        
        $total_gross += $gross_amount;
        $total_withholding += $withholding_tax;
        $total_net += $net_amount;
    }

    // Add the last merge group if there is one
    if ($current_merge_group) {
        $lddap_data['mergedGroups'][] = $current_merge_group;
        $lddap_data['has_merged_groups'] = true;
    }

    $lddap_data['total_gross'] = $total_gross;
    $lddap_data['total_withholding'] = $total_withholding;
    $lddap_data['total_net'] = $total_net;

    // Convert amount to words
    function numberToWords($number) {
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
        $hundreds = array(
            "Hundred", "Thousand", "Million", "Billion", "Trillion", "Quadrillion"
        );
        
        $num = number_format($number, 2, '.', '');
        $num_arr = explode('.', $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(',', $wholenum));
        krsort($whole_arr);
        $rettext = "";
        
        foreach($whole_arr as $key => $i) {
            if($i < 20) {
                $rettext .= $ones[$i];
            } elseif($i < 100) {
                $rettext .= $tens[substr($i, 0, 1)];
                $rettext .= " ".$ones[substr($i, 1, 1)];
            } else {
                $rettext .= $ones[substr($i, 0, 1)]." ".$hundreds[0];
                $tmp = substr($i, 1, 2);
                if($tmp > 0) {
                    $rettext .= " and ".numberToWords($tmp);
                }
            }
            if($key > 0) {
                $rettext .= " ".$hundreds[$key]." ";
            }
        }
        
        if($decnum > 0) {
            $rettext .= " and ";
            if($decnum < 20) {
                $rettext .= $ones[$decnum];
            } elseif($decnum < 100) {
                $rettext .= $tens[substr($decnum, 0, 1)];
                $rettext .= " ".$ones[substr($decnum, 1, 1)];
            }
            $rettext .= " Centavos";
        }
        
        return $rettext . " Pesos Only";
    }

    $lddap_data['amountInWords'] = numberToWords($total_net);

    // Store in session
    $_SESSION['lddap_data'] = $lddap_data;
    logError("Successfully prepared LDDAP data", [
        'reference_no' => $reference_no,
        'dv_count' => count($lddap_data['dvs']),
        'total_amount' => $total_net
    ]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    logError($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'details' => 'Check server logs for more information'
    ]);
}
?> 