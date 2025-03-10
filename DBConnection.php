<?php
session_start();

$connection = new mysqli("localhost", "root", "", "dti-php");

// Check if the connection was successful
if ($connection->connect_error) {
    die("Database connection failed: " . $connection->connect_error);
}
?>