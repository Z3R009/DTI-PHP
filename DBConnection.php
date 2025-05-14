<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "dti-php";

$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    error_log("Database connection failed: " . $connection->connect_error);
    die("Database connection failed: " . $connection->connect_error);
}

$connection->set_charset("utf8mb4");

// This function checks if a table exists in the database
function tableExists($connection, $tableName) {
    $result = $connection->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}
?>