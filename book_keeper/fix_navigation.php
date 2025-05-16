<?php
// Navigation Fix Script for Book Keeper

// 1. Create js directory if it doesn't exist
if (!file_exists(__DIR__ . '/js')) {
    mkdir(__DIR__ . '/js', 0755);
    echo "<p>Created js directory</p>";
}

// 2. Create main.js if it doesn't exist
$mainJsFile = __DIR__ . '/js/main.js';
$mainJsContent = <<<'EOT'
/**
 * Book Keeper Main JavaScript File
 * Handles sidebar toggling, dropdown menus, and other UI interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap components
    initBootstrapComponents();
    
    // Setup sidebar toggle
    setupSidebarToggle();
    
    // Setup dropdown menu functionality
    setupDropdownMenus();
});

/**
 * Initialize Bootstrap components
 */
function initBootstrapComponents() {
    // Initialize all dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownElementList.map(function(dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Initialize all collapse elements
    var collapseElementList = [].slice.call(document.querySelectorAll('.collapse'));
    collapseElementList.map(function(collapseEl) {
        return new bootstrap.Collapse(collapseEl, {
            toggle: false
        });
    });
    
    // Initialize tooltips if any
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Setup sidebar toggle functionality
 */
function setupSidebarToggle() {
    // Toggle sidebar on button click
    const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', function() {
            document.body.classList.toggle('toggle-sidebar');
        });
    }
    
    // Add active class to current page in sidebar
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link, .sidebar-nav .nav-content a');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
            
            // If the active link is in a collapsible menu, expand that menu
            const parent = link.closest('.nav-content');
            if (parent && parent.classList.contains('collapse')) {
                const bsCollapse = new bootstrap.Collapse(parent);
                bsCollapse.show();
                
                // Update the parent toggle button
                const parentToggle = document.querySelector(`[data-bs-target="#${parent.id}"]`);
                if (parentToggle) {
                    parentToggle.classList.remove('collapsed');
                }
            }
        }
    });
}

/**
 * Setup dropdown menu functionality
 */
function setupDropdownMenus() {
    // Profile dropdown menu
    const profileDropdown = document.querySelector('.nav-item.dropdown.pe-3 .nav-link');
    if (profileDropdown) {
        profileDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = bootstrap.Dropdown.getInstance(this);
            if (!dropdown) {
                new bootstrap.Dropdown(this).toggle();
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const dropdownMenus = document.querySelectorAll('.dropdown-menu.show');
        dropdownMenus.forEach(menu => {
            const dropdown = menu.closest('.dropdown');
            if (dropdown && !dropdown.contains(e.target)) {
                const bsDropdown = bootstrap.Dropdown.getInstance(dropdown.querySelector('[data-bs-toggle="dropdown"]'));
                if (bsDropdown) {
                    bsDropdown.hide();
                }
            }
        });
    });
}
EOT;

file_put_contents($mainJsFile, $mainJsContent);
echo "<p>Created or updated main.js file</p>";

// 3. Create common_scripts.php if it doesn't exist
$commonScriptsFile = __DIR__ . '/includes/common_scripts.php';
$commonScriptsContent = <<<'EOT'
<!-- Vendor JS Files -->
<script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
<script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
<script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
<script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
<script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

<!-- Template Main JS Files -->
<script src="../NiceAdmin/assets/js/main.js"></script>
<script src="js/main.js"></script>
EOT;

file_put_contents($commonScriptsFile, $commonScriptsContent);
echo "<p>Created or updated common_scripts.php file</p>";

// 4. Update header.php to fix the dropdown toggle
$headerFile = __DIR__ . '/includes/header.php';
$headerContent = file_get_contents($headerFile);

// Add the bootstrap dropdown initialization script
if (strpos($headerContent, 'bootstrap.Dropdown') === false) {
    $scriptToAdd = <<<'EOT'

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap dropdowns
        var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'))
        var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl)
        });
        
        // Make sure toggle-sidebar-btn works
        const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
        if (toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener('click', function() {
                document.body.classList.toggle('toggle-sidebar');
            });
        }
    });
</script>
EOT;

    $headerContent = str_replace('</header>', '</header>' . $scriptToAdd, $headerContent);
    file_put_contents($headerFile, $headerContent);
    echo "<p>Updated header.php with dropdown script</p>";
} else {
    echo "<p>Header.php already has dropdown script</p>";
}

// 5. Simplify sidebar.php script to avoid conflicts
$sidebarFile = __DIR__ . '/includes/sidebar.php';
$sidebarContent = file_get_contents($sidebarFile);

// Replace the old script with a simplified version
$oldScriptPattern = '/<script>(.*?)<\/script>/s';
$newSidebarScript = <<<'EOT'
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // This is a simplified script that will be supplemented by main.js
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-link, .sidebar-nav .nav-content a');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    });
</script>
EOT;

$sidebarContent = preg_replace($oldScriptPattern, $newSidebarScript, $sidebarContent);
file_put_contents($sidebarFile, $sidebarContent);
echo "<p>Updated sidebar.php with simplified script</p>";

// 6. Update key PHP files to include common_scripts.php
$mainFiles = [
    'dashboard.php',
    'ors.php', 
    'dv.php',
    'jev.php',
    'payee.php',
    'account_title.php',
    'fund_cluster.php',
    'responsibility.php',
    'oopap.php',
    'services.php',
    'reports.php'
];

$count = 0;
foreach ($mainFiles as $file) {
    $filePath = __DIR__ . '/' . $file;
    
    if (!file_exists($filePath)) {
        echo "<p>File not found: $file</p>";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Check if the file already has the include
    if (strpos($content, 'includes/common_scripts.php') !== false) {
        echo "<p>File already updated: $file</p>";
        continue;
    }
    
    // Replace the vendor JS files with our common scripts include
    if (strpos($content, '<!-- Vendor JS Files -->') !== false) {
        $pattern = '/<!-- Vendor JS Files -->.*?<script src="\.\.\/NiceAdmin\/assets\/js\/main\.js"><\/script>/s';
        $replacement = '<?php include "includes/common_scripts.php"; ?>';
        $updatedContent = preg_replace($pattern, $replacement, $content);
        
        if ($updatedContent === $content) {
            // If the pattern didn't match, try another approach - add before body closing tag
            $updatedContent = str_replace('</body>', '<?php include "includes/common_scripts.php"; ?>' . "\n\n" . '</body>', $content);
        }
    } else {
        // Just add before body closing tag
        $updatedContent = str_replace('</body>', '<?php include "includes/common_scripts.php"; ?>' . "\n\n" . '</body>', $content);
    }
    
    // Save the updated content
    file_put_contents($filePath, $updatedContent);
    
    echo "<p>Updated file: $file</p>";
    $count++;
}

echo "<p>Total files updated: $count</p>";

// Display success message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Fix Complete</title>
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 50px;
            font-family: 'Nunito', sans-serif;
        }
        .container {
            max-width: 800px;
        }
        .result-box {
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .success-banner {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-banner">
            <h2>Navigation Fix Successfully Applied!</h2>
            <p>The sidebar toggle and header dropdown functionality have been fixed.</p>
        </div>
        
        <div class="result-box">
            <h3>What was fixed:</h3>
            <ul>
                <li>Added proper dropdown menu functionality in the header</li>
                <li>Fixed sidebar toggle button</li>
                <li>Created centralized JavaScript for better maintenance</li>
                <li>Ensured consistent script loading across all pages</li>
            </ul>
            
            <div class="alert alert-info">
                <strong>Next Steps:</strong> Go back to your Book Keeper dashboard to see the fixed navigation.
            </div>
            
            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html> 