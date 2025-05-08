<?php
// Set header to plain text for easier reading
header('Content-Type: text/plain');

echo "Running database fix script for merged_payees table...\n\n";

// Include the fix script
require_once 'back_end/fix_merged_payees_column.php';

echo "\n\nScript complete. You can now go back to the Pending Payments page.";
echo "\n\n<a href='pending_payments.php'>Return to Pending Payments</a>";
?> 