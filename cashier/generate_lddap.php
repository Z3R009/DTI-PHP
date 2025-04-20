<?php

include '../DBConnection.php';

// Check if LDDAP data is available in session
if (!isset($_SESSION['lddap_data'])) {
    header('Location: pending_payments.php');
    exit();
}

$lddap_data = $_SESSION['lddap_data'];
$reference_no = $lddap_data['reference_no'];
$payment_date = $lddap_data['payment_date'];
$dvs = $lddap_data['dvs'];
$total_gross = $lddap_data['total_gross'];
$total_withholding = $lddap_data['total_withholding'];
$total_net = $lddap_data['total_net'];

// Update status to "Completed" for all payments with this reference number
if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    $ref = $_GET['ref'];
    $has_multiple_references = isset($lddap_data['has_multiple_references']) && $lddap_data['has_multiple_references'];
    
    if ($ref === 'multiple' || $has_multiple_references) {
        // For multiple references, we'll update the individual payment statuses based on the DVs in the session
        foreach ($dvs as $dv) {
            $dv_id = $dv['dv_id'];
            $individual_ref = $dv['reference_no'];
            
            // Update payment status to "Completed"
            $update_query = "UPDATE payment SET status = 'Completed' 
                            WHERE dv_id = ? AND reference_no = ? AND payment_type = 'ADA'";
            $update_stmt = $connection->prepare($update_query);
            $update_stmt->bind_param("is", $dv_id, $individual_ref);
            $update_stmt->execute();
            
            // Update DV status to "Completed"
            $update_dv = "UPDATE dv SET status = 'Completed' WHERE dv_id = ?";
            $update_dv_stmt = $connection->prepare($update_dv);
            $update_dv_stmt->bind_param("i", $dv_id);
            $update_dv_stmt->execute();
        }
    } else {
        // Update all payments with this reference number to "Completed"
        $update_query = "UPDATE payment SET status = 'Completed' WHERE reference_no = ? AND payment_type = 'ADA'";
        $update_stmt = $connection->prepare($update_query);
        $update_stmt->bind_param("s", $ref);
        $update_stmt->execute();
        
        // Get all DV IDs associated with this reference number
        $dv_query = "SELECT dv_id FROM payment WHERE reference_no = ? AND payment_type = 'ADA'";
        $dv_stmt = $connection->prepare($dv_query);
        $dv_stmt->bind_param("s", $ref);
        $dv_stmt->execute();
        $dv_result = $dv_stmt->get_result();
        
        // Update all associated DVs to "Completed" status
        while ($row = $dv_result->fetch_assoc()) {
            $dv_id = $row['dv_id'];
            $update_dv = "UPDATE dv SET status = 'Completed' WHERE dv_id = ?";
            $update_dv_stmt = $connection->prepare($update_dv);
            $update_dv_stmt->bind_param("i", $dv_id);
            $update_dv_stmt->execute();
        }
    }
}

// Convert the net amount to words
function numberToWords($number) {
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    $words = ucfirst($f->format($number));
    
    // Get decimal part
    $decimal = round(($number - floor($number)) * 100);
    if ($decimal > 0) {
        $words .= ' and ' . $decimal . '/100';
    } else {
        $words .= ' Only';
    }
    
    return $words;
}

// If PHP has the NumberFormatter class (requires intl extension)
// if (class_exists('NumberFormatter')) {
//     $amount_in_words = numberToWords($total_net);
// } else {
    // Fallback implementation (simplified)
    function simplified_number_to_words($number) {
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
        
        if ($number < 20) {
            return $ones[$number];
        }
        
        if ($number < 100) {
            $ten = floor($number / 10);
            $one = $number % 10;
            return $tens[$ten] . ($one > 0 ? " " . $ones[$one] : "");
        }
        
        // Handle hundreds and thousands
        if ($number < 1000) {
            $hundred = floor($number / 100);
            $remainder = $number % 100;
            return $ones[$hundred] . " Hundred" . ($remainder > 0 ? " " . simplified_number_to_words($remainder) : "");
        }
        
        if ($number < 1000000) {
            $thousand = floor($number / 1000);
            $remainder = $number % 1000;
            return simplified_number_to_words($thousand) . " Thousand" . ($remainder > 0 ? " " . simplified_number_to_words($remainder) : "");
        }
        
        return "Number too large"; // Simplification
    }
    
    // Get the whole number part
    $whole = floor($total_net);
    // Get the cents part
    $cents = round(($total_net - $whole) * 100);
    
    $amount_in_words = simplified_number_to_words($whole);
    if ($cents > 0) {
        $amount_in_words .= " and " . $cents . "/100";
    }
    $amount_in_words .= " Pesos Only";
// }

// Store data for LDDAP-APA form
// Determine the return path based on the referrer
$return_path = 'pending_payments.php?success=3';
if (isset($_SERVER['HTTP_REFERER'])) {
    if (strpos($_SERVER['HTTP_REFERER'], 'ada_records.php') !== false) {
        $return_path = 'ada_records.php';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LDDAP-APA Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .iframe-container {
            width: 100%;
            flex-grow: 1;
            border: none;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .footer {
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            margin: 0 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-secondary {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
    </style>
    <script>
        // Data to populate the LDDAP-APA form
        const lddapData = {
            referenceNo: "<?php echo htmlspecialchars($reference_no); ?>",
            paymentDate: "<?php echo htmlspecialchars($payment_date); ?>",
            dvs: <?php 
                // Create a modified copy of the DVs array with remarks included
                $dvs_with_remarks = array_map(function($dv) use ($connection) {
                    // Retrieve the remarks from the payment table for this DV
                    $dv_id = $dv['dv_id'];
                    $ref = isset($dv['reference_no']) ? $dv['reference_no'] : '';
                    
                    $remarks_query = "SELECT remarks FROM payment 
                                    WHERE dv_id = ? AND reference_no = ? AND payment_type = 'ADA' 
                                    LIMIT 1";
                    $stmt = $connection->prepare($remarks_query);
                    $stmt->bind_param("is", $dv_id, $ref);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $payment_data = $result->fetch_assoc();
                    
                    // Add remarks to the DV data
                    $dv_clean = $dv;
                    if (isset($dv_clean['purpose'])) {
                        unset($dv_clean['purpose']);
                    }
                    $dv_clean['remarks'] = $payment_data ? $payment_data['remarks'] : '';
                    
                    return $dv_clean;
                }, $dvs);
                
                echo json_encode($dvs_with_remarks); 
            ?>,
            totalGross: <?php echo $total_gross; ?>,
            totalWithholding: <?php echo $total_withholding; ?>,
            totalNet: <?php echo $total_net; ?>,
            amountInWords: "<?php echo htmlspecialchars($amount_in_words); ?>",
            remarks: "<?php echo isset($lddap_data['remarks']) ? htmlspecialchars(str_replace(array("\r\n", "\r", "\n"), "\\n", $lddap_data['remarks'])) : ''; ?>"
        };
        
        // Store data in local storage for embedded view
        localStorage.setItem('lddap_<?php echo htmlspecialchars($reference_no); ?>', JSON.stringify(lddapData));
        
        document.addEventListener('DOMContentLoaded', function() {
            // Load the LDDAP-APA form in the iframe
            const iframe = document.getElementById('lddap-iframe');
            iframe.src = 'LDDAP-APA.html?ref=<?php echo urlencode($reference_no); ?>';
            
            // Add print event handler
            document.getElementById('print-btn').addEventListener('click', function() {
                // Print the LDDAP-APA form
                document.getElementById('lddap-iframe').contentWindow.print();
                
                // Update status message
                const statusMessage = document.getElementById('status-message');
                if (statusMessage) {
                    statusMessage.textContent = "Payment status updated to 'Completed'";
                    statusMessage.style.display = 'block';
                }
            });
        });
    </script>
</head>
<body>
    <?php if (isset($_GET['ref'])): ?>
    <div class="success-message" id="status-message">
        The ADA payment and associated DVs have been marked as completed.
    </div>
    <?php endif; ?>
    
    <div class="iframe-container">
        <iframe id="lddap-iframe" title="LDDAP-ADA Form"></iframe>
    </div>
    <div class="footer">
        <button type="button" class="btn btn-primary" id="print-btn">Print LDDAP-APA</button>
        <a href="<?php echo $return_path; ?>" class="btn btn-secondary">Back</a>
    </div>
   
</body>
</html> 