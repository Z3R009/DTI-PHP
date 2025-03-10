<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "dti";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'];

    if ($type == "category") {
        // Insert into financial_categories
        $category_name = $_POST['category_name'];
        $stmt = $conn->prepare("INSERT INTO financial_categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);

    } elseif ($type == "subcategory") {
        // Insert into financial_subcategories
        $category_id = $_POST['category_id'];
        $subcategory_name = $_POST['subcategory_name'];
        $stmt = $conn->prepare("INSERT INTO financial_subcategories (category_id, subcategory_name) VALUES (?, ?)");
        $stmt->bind_param("is", $category_id, $subcategory_name);

    } elseif ($type == "submodule") {
        // Insert into financial_submodules
        $category_id = $_POST['category_id'];
        $submodule_name = $_POST['submodule_name'];
        $stmt = $conn->prepare("INSERT INTO financial_submodules (category_id, submodule_name) VALUES (?, ?)");
        $stmt->bind_param("is", $category_id, $submodule_name);

    } elseif ($type == "object_code") {
        // Insert into financial_object_codes
        $submodule_id = $_POST['submodule_id'];
        $object_code_name = $_POST['object_code_name'];
        $uacs_code = $_POST['uacs_code'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("INSERT INTO financial_object_code (submodule_id, object_name, uacs_code, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $submodule_id, $object_code_name, $uacs_code, $status);
    }

    // Execute query and check for success
    if ($stmt->execute()) {
        echo "<script>alert('Record added successfully!'); window.location.href='add.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
}

// Close database connection
$conn->close();
?>