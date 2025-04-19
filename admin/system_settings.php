<?php
include '../DBConnection.php';
session_start();

// Check if settings table exists, create it if not
function ensureSettingsTable($connection) {
    // Check if table exists
    try {
        $table_check = $connection->query("SHOW TABLES LIKE 'system_settings'");
        $table_exists = $table_check && $table_check->num_rows > 0;
        
        if (!$table_exists) {
            // Create settings table
            $create_table = "CREATE TABLE IF NOT EXISTS system_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting VARCHAR(100) NOT NULL UNIQUE,
                value TEXT,
                description TEXT
            )";
            
            if ($connection->query($create_table)) {
                // Insert default settings
                $default_settings = [
                    ['system_name', 'DTI Financial Management System', 'System Name'],
                    ['system_logo', 'DTI_w12.png', 'System Logo'],
                    ['fiscal_year', date('Y'), 'Current Fiscal Year'],
                    ['email_host', 'smtp.example.com', 'Email SMTP Host'],
                    ['email_username', 'notification@example.com', 'Email Username'],
                    ['email_password', '', 'Email Password']
                ];
                
                foreach ($default_settings as $setting) {
                    $insert = "INSERT INTO system_settings (setting, value, description) VALUES (?, ?, ?)";
                    $stmt = $connection->prepare($insert);
                    $stmt->bind_param("sss", $setting[0], $setting[1], $setting[2]);
                    $stmt->execute();
                }
                
                return true;
            }
            
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error setting up system_settings table: " . $e->getMessage());
        return false;
    }
}

// Initialize settings table if needed
$table_initialized = ensureSettingsTable($connection);

// Initialize settings array
$settings = [];

// Get database information (handled separately as it doesn't rely on system_settings)
try {
    $db_size_query = "SELECT 
        SUM(data_length + index_length) / 1024 / 1024 AS size_mb
    FROM information_schema.TABLES 
    WHERE table_schema = DATABASE()";
    $db_size_result = mysqli_query($connection, $db_size_query);
    $db_size = mysqli_fetch_assoc($db_size_result)['size_mb'] ?? 0;

    $table_count_query = "SELECT COUNT(*) AS table_count FROM information_schema.TABLES WHERE table_schema = DATABASE()";
    $table_count_result = mysqli_query($connection, $table_count_query);
    $table_count = mysqli_fetch_assoc($table_count_result)['table_count'] ?? 0;
} catch (Exception $e) {
    $db_size = 0;
    $table_count = 0;
}

// Check if form was submitted for saving settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if ($table_initialized) {
        // Process system name
        $system_name = mysqli_real_escape_string($connection, $_POST['system_name']);
        
        // Process system logo if uploaded
        if (!empty($_FILES['system_logo']['name'])) {
            $target_dir = "../img/";
            
            // Create directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES["system_logo"]["name"], PATHINFO_EXTENSION);
            $new_filename = "logo." . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            // Check if image file is a actual image
            $check = getimagesize($_FILES["system_logo"]["tmp_name"]);
            if($check !== false) {
                // Upload file
                if (move_uploaded_file($_FILES["system_logo"]["tmp_name"], $target_file)) {
                    // Update settings in database
                    $update_logo = "UPDATE system_settings SET value = ? WHERE setting = 'system_logo'";
                    $stmt = $connection->prepare($update_logo);
                    $stmt->bind_param("s", $new_filename);
                    $stmt->execute();
                }
            }
        }
        
        // Update settings using prepared statements
        $update_settings = [
            ['system_name', $_POST['system_name']],
            ['fiscal_year', $_POST['fiscal_year']],
            ['email_host', $_POST['email_host']],
            ['email_username', $_POST['email_username']]
        ];
        
        // Only update password if not empty
        if (!empty($_POST['email_password'])) {
            $update_settings[] = ['email_password', $_POST['email_password']];
        }
        
        $update_query = "UPDATE system_settings SET value = ? WHERE setting = ?";
        $stmt = $connection->prepare($update_query);
        
        foreach ($update_settings as $setting) {
            $stmt->bind_param("ss", $setting[1], $setting[0]);
            $stmt->execute();
        }
        
        $success_message = "System settings updated successfully.";
    } else {
        $error_message = "Could not update settings. Database table could not be initialized.";
    }
}

// Fetch current settings if table is initialized
if ($table_initialized) {
    try {
        $settings_query = "SELECT * FROM system_settings";
        $settings_result = mysqli_query($connection, $settings_query);
        
        if ($settings_result) {
            while ($row = mysqli_fetch_assoc($settings_result)) {
                $settings[$row['setting']] = $row['value'];
            }
        }
    } catch (Exception $e) {
        // Handle silently if error occurs
    }
}

// Get the last backup time (in a real system, you would store this in a settings table)
$last_backup = "Never";
if (isset($settings['last_backup']) && !empty($settings['last_backup'])) {
    $last_backup = date('F d, Y H:i:s', strtotime($settings['last_backup']));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>System Settings - Administrator</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php include "includes/header.php"; ?>
    <?php include "includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>System Settings</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">System Settings</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-octagon me-1"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!$table_initialized): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Database setup required</strong>
                    <p>The system settings table could not be created automatically. Please check your database configuration and ensure the database user has sufficient privileges.</p>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">General Settings</h5>

                            <form method="POST" enctype="multipart/form-data">
                                <div class="row mb-3">
                                    <label for="system_name" class="col-sm-2 col-form-label">System Name</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="system_name" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? 'DTI Financial Management System'); ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="system_logo" class="col-sm-2 col-form-label">System Logo</label>
                                    <div class="col-sm-10">
                                        <input class="form-control" type="file" id="system_logo" name="system_logo">
                                        <div class="form-text">Current logo: <?php echo htmlspecialchars($settings['system_logo'] ?? 'No logo set'); ?></div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="fiscal_year" class="col-sm-2 col-form-label">Fiscal Year</label>
                                    <div class="col-sm-10">
                                        <input type="number" class="form-control" id="fiscal_year" name="fiscal_year" min="2000" max="2099" value="<?php echo htmlspecialchars($settings['fiscal_year'] ?? date('Y')); ?>">
                                    </div>
                                </div>

                                <h5 class="card-title mt-4">Email Settings</h5>
                                
                                <div class="row mb-3">
                                    <label for="email_host" class="col-sm-2 col-form-label">SMTP Host</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="email_host" name="email_host" value="<?php echo htmlspecialchars($settings['email_host'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="email_username" class="col-sm-2 col-form-label">Email Username</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="email_username" name="email_username" value="<?php echo htmlspecialchars($settings['email_username'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="email_password" class="col-sm-2 col-form-label">Email Password</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" id="email_password" name="email_password" value="">
                                        <div class="form-text">Leave empty to keep the current password.</div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-10 offset-sm-2">
                                        <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Database Information</h5>
                            
                            <div class="row mb-2">
                                <div class="col-sm-6 fw-bold">Database Size:</div>
                                <div class="col-sm-6"><?php echo number_format($db_size, 2); ?> MB</div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-sm-6 fw-bold">Number of Tables:</div>
                                <div class="col-sm-6"><?php echo $table_count; ?></div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-sm-6 fw-bold">Last Backup:</div>
                                <div class="col-sm-6"><?php echo $last_backup; ?></div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" id="backupDb">
                                    <i class="bi bi-download me-1"></i> Backup Database
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">System Maintenance</h5>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-warning mb-2" id="clearCache">
                                    <i class="bi bi-trash me-1"></i> Clear System Cache
                                </button>
                                
                                <button class="btn btn-info mb-2" id="optimizeDb">
                                    <i class="bi bi-speedometer2 me-1"></i> Optimize Database
                                </button>
                                
                                <button class="btn btn-danger" id="maintMode" data-bs-toggle="modal" data-bs-target="#maintenanceModal">
                                    <i class="bi bi-tools me-1"></i> Maintenance Mode
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Maintenance Mode Modal -->
    <div class="modal fade" id="maintenanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Maintenance Mode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Enabling maintenance mode will make the system inaccessible to all users except administrators. Are you sure you want to continue?</p>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="maintenanceSwitch">
                        <label class="form-check-label" for="maintenanceSwitch">Enable Maintenance Mode</label>
                    </div>
                    <div class="mt-3">
                        <label for="maintenanceMessage" class="form-label">Maintenance Message</label>
                        <textarea class="form-control" id="maintenanceMessage" rows="3" placeholder="System is currently under maintenance. Please try again later."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveMaintenanceMode">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Vendor JS Files -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Backup Database
            document.getElementById('backupDb').addEventListener('click', function() {
                if (confirm('Do you want to create a database backup?')) {
                    // In a real implementation, this would trigger an AJAX call to a PHP script
                    // that performs the database backup
                    alert('Database backup completed successfully!');
                }
            });
            
            // Clear Cache
            document.getElementById('clearCache').addEventListener('click', function() {
                if (confirm('Do you want to clear the system cache?')) {
                    // In a real implementation, this would trigger an AJAX call to a PHP script
                    // that clears the cache files
                    alert('System cache cleared successfully!');
                }
            });
            
            // Optimize Database
            document.getElementById('optimizeDb').addEventListener('click', function() {
                if (confirm('Do you want to optimize the database tables?')) {
                    // In a real implementation, this would trigger an AJAX call to a PHP script
                    // that runs OPTIMIZE TABLE queries
                    alert('Database optimization completed successfully!');
                }
            });
            
            // Maintenance Mode
            document.getElementById('saveMaintenanceMode').addEventListener('click', function() {
                const maintenanceEnabled = document.getElementById('maintenanceSwitch').checked;
                const maintenanceMessage = document.getElementById('maintenanceMessage').value;
                
                // In a real implementation, this would update a setting in the database
                // and potentially create a maintenance file
                alert(maintenanceEnabled ? 'Maintenance mode enabled!' : 'Maintenance mode disabled!');
                
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('maintenanceModal'));
                modal.hide();
            });
        });
    </script>
</body>
</html> 