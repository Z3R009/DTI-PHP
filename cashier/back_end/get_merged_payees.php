<?php
require_once 'db_connection.php';

function ensureMergedPayeesTables() {
    global $connection;
    
    if (!$connection || $connection->connect_error) {
        error_log("Database connection is not available for creating merged payees tables");
        return false;
    }
    
    try {
        $merged_payees_exists = function_exists('tableExists') ? 
            tableExists($connection, 'merged_payees') : 
            ($connection->query("SHOW TABLES LIKE 'merged_payees'")->num_rows > 0);
        
        // Check if merged_payee_items table exists
        $merged_payee_items_exists = function_exists('tableExists') ? 
            tableExists($connection, 'merged_payee_items') : 
            ($connection->query("SHOW TABLES LIKE 'merged_payee_items'")->num_rows > 0);
        
        // Create tables if they don't exist
        if (!$merged_payees_exists) {
            $create_merged_payees = "CREATE TABLE IF NOT EXISTS merged_payees (
                merge_id INT AUTO_INCREMENT PRIMARY KEY,
                merge_name VARCHAR(255) NOT NULL,
                description TEXT,
                payee_type ENUM('Internal', 'External') DEFAULT 'Internal',
                created_by VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if (!$connection->query($create_merged_payees)) {
                error_log("Error creating merged_payees table: " . $connection->error);
                return false;
            }
        }
        
        if (!$merged_payee_items_exists) {
            // Create items table without foreign keys first
            $create_merged_payee_items = "CREATE TABLE IF NOT EXISTS merged_payee_items (
                item_id INT AUTO_INCREMENT PRIMARY KEY,
                merge_id INT NOT NULL,
                dv_id INT NOT NULL,
                added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_merge_id (merge_id),
                INDEX idx_dv_id (dv_id),
                UNIQUE KEY idx_merge_dv (merge_id, dv_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if (!$connection->query($create_merged_payee_items)) {
                error_log("Error creating merged_payee_items table: " . $connection->error);
                return false;
            }
            
            // Now add foreign keys if possible
            $add_foreign_keys = "ALTER TABLE merged_payee_items 
                ADD CONSTRAINT fk_merge_id FOREIGN KEY (merge_id) REFERENCES merged_payees(merge_id) ON DELETE CASCADE,
                ADD CONSTRAINT fk_dv_id FOREIGN KEY (dv_id) REFERENCES dv(dv_id) ON DELETE CASCADE";
            
            $connection->query($add_foreign_keys);  // No need to check result as this might fail and that's okay
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error ensuring merged payees tables: " . $e->getMessage());
        return false;
    }
}

function getMergedPayees() {
    global $connection;
    
    // Ensure tables exist before querying
    if (!ensureMergedPayeesTables()) {
        return [];
    }
    
    // Check if connection is valid
    if (!$connection) {
        return [];
    }
    
    // Initialize array to hold results
    $merged_payees = [];
    
    try {
        // Query to get list of all merged payee groups with their totals
        $merged_groups_sql = "
            SELECT 
                mp.merge_id,
                mp.merge_name,
                mp.description,
                mp.payee_type,
                mp.created_by,
                mp.created_at,
                COUNT(mpi.dv_id) AS total_dvs,
                SUM(dv.net_amount) AS total_amount
            FROM 
                merged_payees mp
            LEFT JOIN 
                merged_payee_items mpi ON mp.merge_id = mpi.merge_id
            LEFT JOIN 
                dv ON mpi.dv_id = dv.dv_id
            GROUP BY 
                mp.merge_id
            ORDER BY 
                mp.created_at DESC";
        
        $merged_groups_result = mysqli_query($connection, $merged_groups_sql);
        
        if ($merged_groups_result && mysqli_num_rows($merged_groups_result) > 0) {
            while ($group = mysqli_fetch_assoc($merged_groups_result)) {
                $merge_id = $group['merge_id'];
                
                // For each merged group, get the DVs that are part of it
                $dvs_sql = "
                    SELECT 
                        dv.dv_id,
                        dv.dv_no,
                        dv.date,
                        dv.ors_id,
                        p.payee_name,
                        o.ors_no,
                        o.purpose,
                        dv.net_amount,
                        mpi.added_at
                    FROM 
                        merged_payee_items mpi
                    JOIN 
                        dv ON mpi.dv_id = dv.dv_id
                    JOIN
                        ors o ON dv.ors_id = o.ors_id
                    JOIN
                        payee p ON o.payee_id = p.payee_id
                    WHERE 
                        mpi.merge_id = ?
                    ORDER BY 
                        p.payee_name, dv.date";
                
                $stmt = mysqli_prepare($connection, $dvs_sql);
                mysqli_stmt_bind_param($stmt, "i", $merge_id);
                mysqli_stmt_execute($stmt);
                $dvs_result = mysqli_stmt_get_result($stmt);
                
                // Add DVs to the group data
                $group['dvs'] = [];
                if ($dvs_result && mysqli_num_rows($dvs_result) > 0) {
                    while ($dv = mysqli_fetch_assoc($dvs_result)) {
                        $group['dvs'][] = $dv;
                    }
                }
                
                $merged_payees[] = $group;
            }
        }
        
        return $merged_payees;
        
    } catch (Exception $e) {
        // Log error or handle the exception
        error_log("Error in getMergedPayees: " . $e->getMessage());
        return [];
    }
}

// If called directly, return JSON data
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    // Get merged payees data
    $merged_payees = getMergedPayees();
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $merged_payees]);
    exit();
}
?> 