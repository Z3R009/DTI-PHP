<?php
/**
 * Payment Handler Router
 * 
 * This script routes payment requests to the appropriate backend processor
 * based on the payment type selected in the form.
 */

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pending_payments.php?error=' . urlencode('Invalid request method.'));
    exit;
}

// Get payment type
$payment_type = isset($_POST['payment_type']) ? $_POST['payment_type'] : '';

// Route to the appropriate handler based on payment type
switch ($payment_type) {
    case 'Cash Advance':
        // Route to the cash advance payment processor
        include 'back_end/cash_advance_payment.php';
        break;
        
    case 'ADA':
        // Route to the ADA payment processor
        // If this is a single DV ADA payment (not batch)
        include 'back_end/ada_payment.php';
        break;
        
    case 'Check':
    case 'Cash':
    default:
        // Route to the standard payment processor for checks, cash, and other types
        include 'back_end/process_payment.php';
        break;
}

// If execution reaches here, something went wrong with the inclusion
header('Location: pending_payments.php?error=' . urlencode('Payment processor not found for type: ' . $payment_type));
exit; 