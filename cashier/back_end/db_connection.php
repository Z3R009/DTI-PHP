<?php
// Database connection file
// This file will be included in all backend files

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the main database connection file
require_once dirname(dirname(__DIR__)) . '/DBConnection.php'; 