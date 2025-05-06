<?php
require_once 'db_connection.php';

// Check if the database connection is available
if (!$connection || $connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// First check if the table exists
$table_exists_query = "SHOW TABLES LIKE 'merged_payees'";
$table_exists = $connection->query($table_exists_query);

if (!$table_exists || $table_exists->num_rows == 0) {
    echo "The merged_payees table does not exist yet. It will be created when needed.\n";
    
    // Create the table with the processed column included
    $create_table_sql = "CREATE TABLE IF NOT EXISTS merged_payees (
        merge_id INT AUTO_INCREMENT PRIMARY KEY,
        merge_name VARCHAR(255) NOT NULL,
        description TEXT,
        payee_type ENUM('Internal', 'External') DEFAULT 'Internal',
        created_by VARCHAR(100) NOT NULL,
        processed TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($connection->query($create_table_sql) === TRUE) {
        echo "The merged_payees table has been created successfully with the processed column.\n";
    } else {
        echo "Error creating merged_payees table: " . $connection->error . "\n";
    }
} else {
    // The table exists, check if the processed column exists
    $check_column_sql = "SHOW COLUMNS FROM merged_payees LIKE 'processed'";
    $column_exists = $connection->query($check_column_sql);
    
    // If the column doesn't exist, add it
    if ($column_exists && $column_exists->num_rows == 0) {
        $add_column_sql = "ALTER TABLE merged_payees ADD COLUMN processed TINYINT(1) NOT NULL DEFAULT 0";
        
        if ($connection->query($add_column_sql) === TRUE) {
            echo "The 'processed' column has been added to the merged_payees table successfully.\n";
        } else {
            echo "Error adding 'processed' column: " . $connection->error . "\n";
        }
    } else {
        echo "The 'processed' column already exists in the merged_payees table.\n";
    }
}

// No need to close the connection here as other scripts may still need it
?> 