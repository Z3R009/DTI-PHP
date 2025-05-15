<?php
// This script modifies the sidebar.php file to remove the inline JavaScript
// that might conflict with our new main.js file

$sidebarFile = __DIR__ . '/sidebar.php';
$sidebarContent = file_get_contents($sidebarFile);

// Remove the inline script that handles sidebar toggle
$pattern = '/<script>(.*?)<\/script>/s';
$replacement = '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Active page highlighting
        const currentPage = window.location.pathname.split("/").pop();
        const navLinks = document.querySelectorAll(".sidebar-nav .nav-link, .sidebar-nav .nav-content a");
        
        navLinks.forEach(link => {
            if (link.getAttribute("href") === currentPage) {
                link.classList.add("active");
            }
        });
    });
</script>';

$modifiedContent = preg_replace($pattern, $replacement, $sidebarContent);

// Save the modified content back to the file
file_put_contents($sidebarFile, $modifiedContent);

echo "Sidebar script has been updated successfully!";
?> 