<?php


$connection = new mysqli("localhost", "root", "", "dti-php");

if ($connection->connect_error) {
    die("Database connection failed: " . $connection->connect_error);
}
?>