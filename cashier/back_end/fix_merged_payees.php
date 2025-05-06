<?php
// Include database connection
require_once 'db_connection.php';

// Set content type to plain text for debugging output
header('Content-Type: text/plain');

echo "Starting merged payees fix script...\n\n";

// Function to safely execute SQL
function executeSafely($conn, $sql, $description) {
    echo "Executing: $description...\n";
    echo "SQL: $sql\n";
    
    try {
        $result = $conn->query($sql);
        if ($result === false) {
            echo "ERROR: " . $conn->error . "\n";
            return false;
        }
        echo "SUCCESS!\n";
        return true;
    } catch (Exception $e) {
        echo "EXCEPTION: " . $e->getMessage() . "\n";
        return false;
    }
}

// Start transaction
$connection->begin_transaction();

try {
    echo "Checking constraints on merged_payee_items table...\n";
    
    // Step 1: Drop the foreign key constraint if it exists
    $check_fk_query = "
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE CONSTRAINT_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'merged_payee_items' 
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        AND CONSTRAINT_NAME IN ('fk_merged_payee_items_dv_id', 'fk_dv_id')";
    
    $fk_result = $connection->query($check_fk_query);
    
    if ($fk_result && $fk_result->num_rows > 0) {
        while ($row = $fk_result->fetch_assoc()) {
            $constraint_name = $row['CONSTRAINT_NAME'];
            executeSafely($connection, 
                "ALTER TABLE merged_payee_items DROP FOREIGN KEY `$constraint_name`", 
                "Dropping existing foreign key constraint: $constraint_name");
        }
    } else {
        echo "No existing foreign key constraints found on merged_payee_items table. This is good.\n";
    }
    
    // Step 2: Make sure we have the proper indexes
    executeSafely($connection,
        "ALTER TABLE merged_payee_items 
         ADD INDEX idx_dv_id (dv_id) IF NOT EXISTS,
         ADD INDEX idx_merge_id (merge_id) IF NOT EXISTS,
         ADD UNIQUE INDEX uk_merge_dv (merge_id, dv_id) IF NOT EXISTS",
        "Adding/ensuring proper indexes");
    
    // Step 3: Add back the foreign key with correct options
    executeSafely($connection,
        "ALTER TABLE merged_payee_items 
         ADD CONSTRAINT fk_merged_payee_items_dv_id FOREIGN KEY (dv_id) REFERENCES dv(dv_id) ON DELETE RESTRICT",
        "Adding foreign key with RESTRICT delete behavior");
    
    // Step 4: Ensure the fk_merged_payee_items_merge_id constraint is correct
    executeSafely($connection,
        "ALTER TABLE merged_payee_items 
         ADD CONSTRAINT fk_merged_payee_items_merge_id FOREIGN KEY (merge_id) REFERENCES merged_payees(merge_id) ON DELETE CASCADE",
        "Ensuring merge_id foreign key is correct");
    
    // Step 5: Add 'processed' column to merged_payees table if it doesn't exist
    $check_processed_column = "SHOW COLUMNS FROM merged_payees LIKE 'processed'";
    $processed_column_result = $connection->query($check_processed_column);
    
    if ($processed_column_result && $processed_column_result->num_rows == 0) {
        executeSafely($connection,
            "ALTER TABLE merged_payees 
             ADD COLUMN processed TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Flag to indicate if this merged payee has been processed in an ADA'",
            "Adding 'processed' column to merged_payees table");
    } else {
        echo "The 'processed' column already exists in the merged_payees table.\n";
    }
    
    // Commit changes
    $connection->commit();
    
    echo "\nFix script completed successfully!";
    echo "\nThe constraint has been updated to prevent accidental deletion of merged payee items when adding DVs to ADA.";
    
} catch (Exception $e) {
    // If an error occurs, rollback the transaction
    $connection->rollback();
    echo "ERROR occurred: " . $e->getMessage() . "\n";
    echo "Changes have been rolled back.\n";
}

echo "\n\nScript execution complete.\n";
?> 