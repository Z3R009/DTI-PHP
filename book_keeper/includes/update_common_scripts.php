<?php
// This script modifies all main PHP files in the book_keeper directory
// to include the common_scripts.php file if they don't already have it

// Key PHP files to update - these are the main pages with UI elements
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

$includeStatement = "<?php include \"includes/common_scripts.php\"; ?>\n\n</body>";
$count = 0;

foreach ($mainFiles as $file) {
    $filePath = __DIR__ . '/../' . $file;
    
    if (!file_exists($filePath)) {
        echo "File not found: $file<br>";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Check if the file already has the include
    if (strpos($content, 'includes/common_scripts.php') !== false) {
        echo "File already updated: $file<br>";
        continue;
    }
    
    // Replace the closing body tag with our include statement and the body tag
    $updatedContent = str_replace("</body>", $includeStatement, $content);
    
    // Save the updated content
    file_put_contents($filePath, $updatedContent);
    
    echo "Updated file: $file<br>";
    $count++;
}

echo "<br>Total files updated: $count";
?> 