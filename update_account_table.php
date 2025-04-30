<?php
// This script adds required columns to the account_name table if they don't already exist
include 'DBConnection.php';

if (!isset($connection)) {
    die("Database connection not available. Please check the DBConnection.php file.");
}

echo "<h1>Account Table Update Script</h1>";

// Define the columns to add
$columns_to_add = [
    'fund_code' => [
        'sql' => "ALTER TABLE `account_name` ADD COLUMN `fund_code` VARCHAR(20) NOT NULL DEFAULT '01101101' AFTER `type`",
        'post_actions' => [
            "UPDATE `account_name` SET `fund_code` = '01101101' WHERE `type` = 'EMDS'",
            "UPDATE `account_name` SET `fund_code` = '01091201' WHERE `type` = 'REGULAR LCCA'"
        ]
    ],
    'NCA_NO' => [
        'sql' => "ALTER TABLE `account_name` ADD COLUMN `NCA_NO` VARCHAR(50) DEFAULT NULL AFTER `account_number`",
        'post_actions' => []
    ],
    'NCA_DATE' => [
        'sql' => "ALTER TABLE `account_name` ADD COLUMN `NCA_DATE` DATE DEFAULT NULL AFTER `NCA_NO`",
        'post_actions' => []
    ],
    'FUND_SOURCE' => [
        'sql' => "ALTER TABLE `account_name` ADD COLUMN `FUND_SOURCE` VARCHAR(100) DEFAULT NULL AFTER `NCA_DATE`",
        'post_actions' => []
    ],
    'Description' => [
        'sql' => "ALTER TABLE `account_name` ADD COLUMN `Description` TEXT DEFAULT NULL AFTER `FUND_SOURCE`",
        'post_actions' => []
    ]
];

// Process each column
foreach ($columns_to_add as $column_name => $column_data) {
    // Check if column already exists
    $check_column_sql = "SHOW COLUMNS FROM account_name LIKE '$column_name'";
    $result = $connection->query($check_column_sql);
    
    if ($result->num_rows > 0) {
        echo "<p>The <strong>$column_name</strong> column already exists in the account_name table.</p>";
    } else {
        // Add the column
        if ($connection->query($column_data['sql']) === TRUE) {
            echo "<p>Successfully added <strong>$column_name</strong> column to account_name table.</p>";
            
            // Execute any post-actions (like updating values)
            foreach ($column_data['post_actions'] as $action_sql) {
                if ($connection->query($action_sql) === TRUE) {
                    echo "<p>Successfully executed post-action for <strong>$column_name</strong>.</p>";
                } else {
                    echo "<p class='error'>Error executing post-action for <strong>$column_name</strong>: " . $connection->error . "</p>";
                }
            }
        } else {
            echo "<p class='error'>Error adding <strong>$column_name</strong> column: " . $connection->error . "</p>";
        }
    }
}

echo "<p>Account table update process completed!</p>";
echo "<p><a href='cashier/pending_payments.php' class='btn'>Return to Pending Payments</a></p>";

// Add some basic styling
echo "
<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; line-height: 1.6; }
    h1 { color: #2c3e50; }
    p { margin-bottom: 10px; }
    .error { color: #e74c3c; }
    .btn { display: inline-block; background: #3498db; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
    .btn:hover { background: #2980b9; }
</style>
";
?> 