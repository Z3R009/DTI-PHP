<?php
session_start();

$connection = new mysqli("192.168.0.147", "dti_user", "", "dti-php");

// Check if the connection was successful
if ($connection->connect_error) {
    die("Database connection failed: " . $connection->connect_error);
}
?>