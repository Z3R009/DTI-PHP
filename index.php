<?php
session_start();
include 'DBConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($password, $hashed_password)) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on user type
            if ($_SESSION['role'] == 'Admin') {
                header('Location: admin/dashboard.php?user_id=' . $_SESSION['user_id']);
            } elseif ($_SESSION['role'] == 'Bookkeeper') {
                header('Location: book_keeper/dashboard.php?user_id=' . $_SESSION['user_id']);
            } elseif ($_SESSION['role'] == 'Budget Officer') {
                header('Location: budget_officer/dashboard.php?user_id=' . $_SESSION['user_id']);
            } elseif ($_SESSION['role'] == 'Chief Accountant') {
                header('Location: chief_accountant/dashboard.php?user_id=' . $_SESSION['user_id']);
            } elseif ($_SESSION['role'] == 'Guest') {
                header('Location: guest/dashboard.php?user_id=' . $_SESSION['user_id']);
            } elseif ($_SESSION['role'] == 'Cashier') {
                header('Location: cashier/dashboard.php?user_id=' . $_SESSION['user_id']);
            } else {
                echo "<script>alert('Invalid user type value');</script>";
            }
            exit();
        } else {
            echo "<script>alert('Incorrect Password');</script>";
        }
    } else {
        echo "<script>alert('Incorrect Username');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - DTI Region 12 Financial Processing System</title>
    <script type="module" src="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <!-- icon -->
    <link href="img/dti_logo.png" rel="icon">
    <link rel="stylesheet" href="css/index.css">
    <!-- Bootstrap and icons -->
    <link href="bootstrap-5.2.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <!-- Mobile web app capable -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#03045e">
</head>
<style>
    
    body {
        font-family: 'Inter', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-image: url('img/bg.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .login-container {
        width: 400px;
        padding: 40px;
        background-color: rgb(255, 255, 255);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }

    .logo-container {
        perspective: 1000px;
        width: 120px;
        height: 120px;
        margin: 0 auto 30px;
    }

    .logo-carousel {
        width: 100%;
        height: 100%;
        position: relative;
        transform-style: preserve-3d;
        transition: transform 1s;
    }

    .logo-face {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .logo-face img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .logo-back {
        transform: rotateY(180deg);
    }

    .loader-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        display: none;
    }

    .absolute {
        position: absolute;
    }

    .inline-block {
        display: inline-block;
    }

    .loader {
        display: flex;
        margin: 0.25em 0;
    }

    .w-2 {
        width: 0.5em;
    }

    .dash {
        animation: dashArray 2s ease-in-out infinite,
            dashOffset 2s linear infinite;
    }

    .spin {
        animation: spinDashArray 2s ease-in-out infinite,
            spin 8s ease-in-out infinite,
            dashOffset 2s linear infinite;
        transform-origin: center;
    }

    @keyframes dashArray {
        0% {
            stroke-dasharray: 0 1 359 0;
        }

        50% {
            stroke-dasharray: 0 359 1 0;
        }

        100% {
            stroke-dasharray: 359 1 0 0;
        }
    }

    @keyframes spinDashArray {
        0% {
            stroke-dasharray: 270 90;
        }

        50% {
            stroke-dasharray: 0 360;
        }

        100% {
            stroke-dasharray: 270 90;
        }
    }

    @keyframes dashOffset {
        0% {
            stroke-dashoffset: 365;
        }

        100% {
            stroke-dashoffset: 5;
        }
    }

    @keyframes spin {
        0% {
            rotate: 0deg;
        }

        12.5%,
        25% {
            rotate: 270deg;
        }

        37.5%,
        50% {
            rotate: 540deg;
        }

        62.5%,
        75% {
            rotate: 810deg;
        }

        87.5%,
        100% {
            rotate: 1080deg;
        }
    }

    button:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .login-text {
        display: inline-block;
    }

    .login-text.hidden {
        display: none;
    }
    
    /* Payee Button Style */
    .payee-btn {
        display: none;
    }
    
    .payee-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    
    .payee-modal-content {
        background-color: white;
        border-radius: 10px;
        width: 90%;
        max-width: 1000px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
    }
    
    .payee-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background-color: #03045e;
        color: white;
    }
    
    .payee-modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .payee-modal-title i {
        margin-right: 8px;
    }
    
    .payee-close {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        opacity: 0.8;
        transition: opacity 0.2s, transform 0.2s;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    .payee-close:hover {
        opacity: 1;
        transform: scale(1.1);
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .payee-close:active {
        transform: scale(0.95);
    }
    
    .payee-modal-body {
        padding: 20px;
        overflow-y: auto;
        max-height: calc(80vh - 130px);
    }
    
    .payee-search {
        margin-bottom: 15px;
        display: flex;
        position: relative;
    }
    
    .payee-search input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }
    
    .payee-search i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }
    
    .payee-table {
        width: 100%;
        border-collapse: collapse;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border-radius: 5px;
        overflow: hidden;
    }
    
    .payee-table th, .payee-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .payee-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        position: sticky;
        top: 0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        color: #03045e;
    }
    
    .payee-table tbody tr:hover {
        background-color: #f5f5f5;
    }
    
    .payee-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .payee-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }
    
    .payee-badge-internal {
        background-color: #0d6efd;
    }
    
    .payee-badge-external {
        background-color: #fd7e14;
    }
    
    .payee-modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        background-color: #f8f9fa;
    }
    
    .payee-modal-footer button {
        padding: 8px 20px;
        border: none;
        border-radius: 5px;
        background-color: #03045e;
        color: white;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    
    .payee-modal-footer button:hover {
        background-color: #D90429;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .payee-modal-footer button:active {
        transform: translateY(1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .payee-loader {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 30px;
        flex-direction: column;
    }
    
    .payee-loader-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #03045e;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }
    
    .payee-loader-text {
        color: #666;
        font-size: 14px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .main-header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        padding: 12px 25px;
        z-index: 100;
        border-bottom: 2px solid #03045e;
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
    }

    .logo-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-logo {
        height: 45px;
        transition: transform 0.3s ease;
    }

    .header-logo:hover {
        transform: scale(1.05);
    }

    .header-titles {
        display: flex;
        flex-direction: column;
    }

    .header-titles h1 {
        font-size: 20px;
        font-weight: 700;
        color: #03045e;
        margin: 0;
        line-height: 1.2;
        letter-spacing: -0.5px;
    }

    .header-titles h2 {
        font-size: 14px;
        font-weight: 500;
        color: #D90429;
        margin: 0;
        letter-spacing: 0.5px;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .header-nav {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .nav-link {
        color: #03045e;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #03045e 0%, #D90429 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }

    .nav-link:hover {
        color: white;
        transform: translateY(-2px);
    }

    .nav-link:hover::before {
        opacity: 1;
    }

    .nav-link i {
        font-size: 16px;
        transition: transform 0.3s ease;
    }

    .nav-link:hover i {
        transform: scale(1.1);
    }

    .nav-link.active {
        background: linear-gradient(135deg, #03045e 0%, #D90429 100%);
        color: white;
    }

    /* Adjust body padding to accommodate the header */
    body {
        padding-top: 70px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .main-header {
            padding: 10px 15px;
        }

        .header-logo {
            height: 35px;
        }

        .header-titles h1 {
            font-size: 16px;
        }

        .header-titles h2 {
            font-size: 12px;
        }

        .nav-link span {
            display: none;
        }

        .nav-link {
            padding: 8px;
            border-radius: 50%;
        }

        .nav-link i {
            margin: 0;
            font-size: 18px;
        }

        body {
            padding-top: 60px;
        }
    }

    @media (max-width: 480px) {
        .header-titles h1 {
            font-size: 14px;
        }

        .header-titles h2 {
            font-size: 10px;
        }

        .header-logo {
            height: 30px;
        }

        .main-header {
            padding: 8px 12px;
        }

        body {
            padding-top: 55px;
        }
    }

    /* Footer Styles */
    footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(5px);
        padding: 10px 20px;
        text-align: center;
        font-size: 12px;
        color: #666;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
        z-index: 90;
    }
    
    .footer-content {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    footer img {
        vertical-align: middle;
        margin-right: 5px;
    }
    
    footer p {
        margin: 0;
    }
    
    .footer-links {
        display: flex;
        align-items: center;
    }
    
    .footer-divider {
        margin: 0 8px;
        color: #ccc;
        font-size: 10px;
    }
    
    footer a {
        color: #03045e;
        text-decoration: none;
        transition: color 0.3s;
        font-weight: 500;
    }
    
    footer a:hover {
        color: #D90429;
    }
    
    @media (max-width: 768px) {
        footer {
            padding: 8px 15px;
            font-size: 11px;
        }
        
        .footer-content {
            flex-direction: column;
            gap: 8px;
        }
        
        .footer-links {
            margin-top: 5px;
        }
    }

    /* Mobile responsive modal styles */
    @media (max-width: 768px) {
        .payee-modal-content {
            width: 95%;
            max-height: 90vh;
            margin: 20px 0;
        }
        
        .payee-modal-header {
            padding: 12px 15px;
        }
        
        .payee-modal-title {
            font-size: 16px;
        }
        
        .payee-modal-body {
            padding: 15px;
            max-height: calc(90vh - 120px);
        }
        
        .payee-table th, .payee-table td {
            padding: 10px 12px;
            font-size: 13px;
        }
    }
    
    @media (max-width: 580px) {
        .payee-modal-content {
            width: 98%;
            height: 95vh;
            max-height: 95vh;
            margin: 10px 0;
            border-radius: 8px;
        }
        
        .payee-modal-header {
            padding: 10px;
        }
        
        .payee-modal-title {
            font-size: 15px;
        }
        
        .payee-modal-body {
            padding: 10px;
            max-height: calc(95vh - 110px);
        }
        
        .payee-search input {
            padding: 8px 8px 8px 35px;
            font-size: 13px;
        }
        
        .payee-table {
            font-size: 12px;
        }
        
        .payee-table th, .payee-table td {
            padding: 8px 10px;
        }
        
        /* Make table responsive on small screens */
        .payee-table-container {
            overflow-x: auto;
        }
    }

    @media (max-width: 900px) {
        .payee-btn-text {
            display: none;
        }
    }

    /* About Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: white;
        border-radius: 10px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background-color: #03045e;
        color: white;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .close-modal {
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        opacity: 0.8;
        transition: opacity 0.2s, transform 0.2s;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .close-modal:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 20px;
        overflow-y: auto;
        max-height: calc(80vh - 70px);
    }

    .about-content {
        color: #333;
    }

    .about-content h4 {
        color: #03045e;
        margin-bottom: 15px;
        font-size: 20px;
    }

    .about-content p {
        margin-bottom: 20px;
        line-height: 1.6;
    }

    .about-features {
        margin: 25px 0;
    }

    .about-features h5 {
        color: #03045e;
        margin-bottom: 10px;
        font-size: 16px;
    }

    .about-features ul {
        list-style: none;
        padding-left: 0;
    }

    .about-features li {
        padding: 8px 0 8px 25px;
        position: relative;
    }

    .about-features li:before {
        content: "•";
        color: #D90429;
        position: absolute;
        left: 0;
        font-size: 20px;
    }

    .about-contact {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .about-contact h5 {
        color: #03045e;
        margin-bottom: 15px;
        font-size: 16px;
    }

    .about-contact p {
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .about-contact i {
        color: #D90429;
        font-size: 16px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .header-nav {
            gap: 10px;
        }
        
        .nav-link span {
            display: none;
        }
        
        .nav-link {
            padding: 8px;
            border-radius: 50%;
        }
        
        .nav-link i {
            margin: 0;
            font-size: 18px;
        }
        
        .modal-content {
            width: 95%;
            margin: 20px;
        }
    }

    @media (max-width: 480px) {
        .modal-header h3 {
            font-size: 16px;
        }
        
        .about-content h4 {
            font-size: 18px;
        }
        
        .about-features li {
            font-size: 14px;
        }
        
        .about-contact p {
            font-size: 14px;
        }
    }
</style>

<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo-section">
            <img src="img/Bagong-Pilipinas-Logo-e1717212149320-1920x1488.png" alt="DTI Logo" class="header-logo">
            <div class="header-titles">
                <h1>Department of Trade and Industry</h1>
                <h2>Region 12 Financial Processing System</h2>
            </div>
        </div>
        <div class="header-right">
            <nav class="header-nav">
                <a href="suppliers.php" class="nav-link">
                    <i class="bi bi-people-fill"></i>
                    <span>Registry of Suppliers</span>
                </a>
                <a href="about.php" class="nav-link">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>About</span>
                </a>
            </nav>
        </div>
    </div>
</header>

<!-- About Modal -->
<div id="aboutModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="bi bi-info-circle-fill"></i> About the System</h3>
            <button class="close-modal" id="closeAboutModal"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">
            <div class="about-content">
                <h4>DTI Region 12 Financial Processing System</h4>
                <p>This system is designed to streamline and modernize the financial processes of the Department of Trade and Industry Region 12. It provides a comprehensive platform for managing financial transactions, supplier information, and other related administrative tasks.</p>
                
                <div class="about-features">
                    <h5>Key Features:</h5>
                    <ul>
                        <li>Secure user authentication and role-based access control</li>
                        <li>Comprehensive supplier registry management</li>
                        <li>Efficient financial transaction processing</li>
                        <li>Real-time data tracking and reporting</li>
                        <li>User-friendly interface for all stakeholders</li>
                    </ul>
                </div>
                
                <div class="about-contact">
                    <h5>Contact Information:</h5>
                    <p><i class="bi bi-geo-alt-fill"></i> DTI Region 12 Office, Koronadal City</p>
                    <p><i class="bi bi-telephone-fill"></i> (083) 228-0016</p>
                    <p><i class="bi bi-envelope-fill"></i> ro12@dti.gov.ph</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payee Modal -->
<div id="payeeModal" class="payee-modal">
    <div class="payee-modal-content">
        <div class="payee-modal-header">
            <h3 class="payee-modal-title">
                <i class="bi bi-people-fill"></i> Suppliers
            </h3>
            <button class="payee-close" id="closePayeeModal"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="payee-modal-body">
            <div class="payee-search">
                <i class="bi bi-search"></i>
                <input type="text" id="payeeSearch" placeholder="Search payee by name, TIN, address...">
            </div>
            <div class="payee-table-container">
                <table class="payee-table" id="payeeTable">
                    <thead>
                        <tr>
                            <th>Payee Name</th>
                          
                            <th>Address</th>
                            <th>Category</th>
                            <th>Contact Number</th>
                           
                        </tr>
                    </thead>
                    <tbody id="payeeTableBody">
                        <!-- Payee data will be loaded here via AJAX -->
                        <tr>
                            <td colspan="7">
                                <div class="payee-loader">
                                    <div class="payee-loader-spinner"></div>
                                    <div class="payee-loader-text">Loading payee data...</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
       
    </div>
</div>

    <div class="loader-container" id="loader-container">
        <div class="loader">
            <svg height="0" width="0" viewBox="0 0 64 64" class="absolute">
                <defs xmlns="http://www.w3.org/2000/svg">
                    <!-- Blue gradient for D -->
                    <linearGradient gradientUnits="userSpaceOnUse" y2="2" x2="0" y1="62" x1="0" id="blue-grad">
                        <stop stop-color="#0033CC" offset="0"></stop>
                        <stop stop-color="#66CCFF" offset="1"></stop>
                        <animateTransform repeatCount="indefinite"
                            keySplines=".42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1"
                            keyTimes="0; 0.125; 0.25; 0.375; 0.5; 0.625; 0.75; 0.875; 1" dur="8s"
                            values="0 32 32;-270 32 32;-270 32 32;-540 32 32;-540 32 32;-810 32 32;-810 32 32;-1080 32 32;-1080 32 32"
                            type="rotate" attributeName="gradientTransform"></animateTransform>
                    </linearGradient>

                    <!-- Red gradient for T -->
                    <linearGradient gradientUnits="userSpaceOnUse" y2="0" x2="0" y1="64" x1="0" id="red-grad">
                        <stop stop-color="#FF0000" offset="0"></stop>
                        <stop stop-color="#FF9999" offset="1"></stop>
                        <animateTransform repeatCount="indefinite"
                            keySplines=".42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1"
                            keyTimes="0; 0.125; 0.25; 0.375; 0.5; 0.625; 0.75; 0.875; 1" dur="8s"
                            values="0 32 32;-270 32 32;-270 32 32;-540 32 32;-540 32 32;-810 32 32;-810 32 32;-1080 32 32;-1080 32 32"
                            type="rotate" attributeName="gradientTransform"></animateTransform>
                    </linearGradient>

                    <!-- Yellow gradient for I -->
                    <linearGradient gradientUnits="userSpaceOnUse" y2="2" x2="0" y1="62" x1="0" id="yellow-grad">
                        <stop stop-color="#0033CC" offset="0"></stop>
                        <stop stop-color="#66CCFF" offset="1"></stop>
                        <animateTransform repeatCount="indefinite"
                            keySplines=".42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1;.42,0,.58,1"
                            keyTimes="0; 0.125; 0.25; 0.375; 0.5; 0.625; 0.75; 0.875; 1" dur="8s"
                            values="0 32 32;-270 32 32;-270 32 32;-540 32 32;-540 32 32;-810 32 32;-810 32 32;-1080 32 32;-1080 32 32"
                            type="rotate" attributeName="gradientTransform"></animateTransform>
                    </linearGradient>
                </defs>
            </svg>

            <!-- D Letter (Blue Gradient) -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 64 64" height="64" width="64"
                class="inline-block">
                <path stroke-linejoin="round" stroke-linecap="round" stroke-width="8" stroke="url(#blue-grad)"
                    d="M 10,4 h 18 c 12,0 22,10 22,22 v 12 c 0,12 -10,22 -22,22 h -18 z" class="dash" id="d"
                    pathLength="360"></path>
            </svg>

            <!-- T Letter (Red Gradient) -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                style="--rotation-duration:0ms; --rotation-direction:normal;" viewBox="0 0 64 64" height="64" width="64"
                class="inline-block">
                <path stroke-linejoin="round" stroke-linecap="round" stroke-width="10" stroke="url(#red-grad)"
                    d="M 16,10 h 32 M 32,10 v 50" class="spin" id="t" pathLength="360"></path>
            </svg>

            <div class="w-2"></div>

            <!-- I Letter (Yellow Gradient) -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                style="--rotation-duration:0ms; --rotation-direction:normal;" viewBox="0 0 64 64" height="64" width="64"
                class="inline-block">
                <path stroke-linejoin="round" stroke-linecap="round" stroke-width="8" stroke="url(#yellow-grad)"
                    d="M 32,4 v 56" class="dash" id="i" pathLength="360"></path>
            </svg>
        </div>
    </div>

    <div class="login-container">
        <div class="logo-container" style="margin-bottom: 10px;">
            <div class="logo-carousel" id="logo-carousel">
                <div class="logo-face logo-front">
                    <img src="img/dti_logo.png" alt="DTI Logo">
                </div>
                <div class="logo-face logo-back">
                    <img src="img/Bagong-Pilipinas-Logo-e1717212149320-1920x1488.png" alt="Secondary Logo">
                </div>
            </div>
        </div>

        <p id="error-message" style="color: red; display: none;"></p>
        <form method="post" id="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <ion-icon name="mail-outline"></ion-icon>
                <input type="text" id="username" name="username" placeholder="Enter your Username" autocomplete="off"
                    required>
            </div>

            <div class="form-group" style="margin-bottom: 10px;">
                <label for="password">Password</label>
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input type="password" id="password" name="password" placeholder="Enter your Password"
                    autocomplete="off" required>
            </div>

            <button type="submit" id="login-button" style="border-radius: 10px; margin-top: 15px;">
                <span class="login-text">Login</span>
            </button>
        </form>
    </div> 
    <footer>
        <div class="footer-content">
            <img src="img/DTI_short.png" alt="DTI Logo" height="16">
            <p>Copyright &copy; 2025 Department of Trade and Industry</p>
            <!-- <div class="footer-links">
                <a href="#">Terms</a>
                <span class="footer-divider">|</span>
                <a href="#">Privacy</a>
                <span class="footer-divider">|</span>
                <a href="#">Contact</a>
            </div> -->
        </div>
    </footer>

    
    <!-- Bootstrap Script -->
    <script src="bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
</body>
<script>
    const carousel = document.getElementById('logo-carousel');
    let currentRotation = 0;
    let animationRunning = true;
    let animationSpeed = 2000;
    let animationInterval;
    let lastTimestamp = 0;

    function rotateLogo(timestamp) {
        if (!lastTimestamp) lastTimestamp = timestamp;
        const elapsed = timestamp - lastTimestamp;
        
        if (elapsed >= animationSpeed) {
            currentRotation += 180;
            carousel.style.transform = `rotateY(${currentRotation}deg)`;
            lastTimestamp = timestamp;
        }
        
        if (animationRunning) {
            requestAnimationFrame(rotateLogo);
        }
    }

    function startAnimation() {
        if (!animationRunning) {
            animationRunning = true;
            lastTimestamp = 0;
            requestAnimationFrame(rotateLogo);
        }
    }

    function stopAnimation() {
        animationRunning = false;
    }

    // Handle visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAnimation();
        } else {
            startAnimation();
        }
    });

    // Initial start
    startAnimation();

    const secondaryLogo = document.querySelector('.logo-back img');
    secondaryLogo.onerror = function () {
        this.src = 'img/dti_logo.png';
        this.style.filter = 'hue-rotate(180deg)';
    };
    const loginForm = document.getElementById('login-form');
    const loginButton = document.getElementById('login-button');
    const loaderContainer = document.getElementById('loader-container');

    loginForm.addEventListener('submit', function (event) {
        loaderContainer.style.display = 'flex';
        loginButton.disabled = true;
    });
    window.addEventListener('load', function () {
        loaderContainer.style.display = 'none';
        loginButton.disabled = false;
    });
    
    // Payee Modal Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const viewPayeesBtn = document.getElementById('viewPayeesBtn');
        const payeeModal = document.getElementById('payeeModal');
        const closePayeeModal = document.getElementById('closePayeeModal');
        const payeeSearch = document.getElementById('payeeSearch');
        const payeeTableBody = document.getElementById('payeeTableBody');
        
        // Open modal
        viewPayeesBtn.addEventListener('click', function() {
            payeeModal.style.display = 'flex';
            fetchPayeeData();
        });
        
        // Close modal
        closePayeeModal.addEventListener('click', function() {
            payeeModal.style.display = 'none';
        });
        
        // Close when clicking outside modal
        window.addEventListener('click', function(event) {
            if (event.target === payeeModal) {
                payeeModal.style.display = 'none';
            }
        });
        
        // Search functionality
        payeeSearch.addEventListener('input', function() {
            const searchText = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#payeeTableBody tr');
            
            rows.forEach(row => {
                // Skip the loading row
                if (row.querySelector('.payee-loader')) {
                    return;
                }
                
                // Get all cell text from the row
                const rowText = Array.from(row.querySelectorAll('td'))
                    .map(cell => cell.textContent.toLowerCase())
                    .join(' ');
                
                // Show/hide based on search match
                if (rowText.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show a message if no results found
            const visibleRows = document.querySelectorAll('#payeeTableBody tr:not([style*="display: none"])');
            const noResultsRow = document.getElementById('no-search-results');
            
            if (visibleRows.length === 0 && searchText !== '') {
                if (!noResultsRow) {
                    const noResults = document.createElement('tr');
                    noResults.id = 'no-search-results';
                    noResults.innerHTML = `
                        <td colspan="4" style="text-align: center; padding: 20px;">
                            <i class="bi bi-search" style="font-size: 24px; color: #ccc;"></i>
                            <p style="margin-top: 10px; color: #666;">No results found for "${searchText}"</p>
                        </td>
                    `;
                    payeeTableBody.appendChild(noResults);
                } else {
                    noResultsRow.querySelector('p').textContent = `No results found for "${searchText}"`;
                    noResultsRow.style.display = '';
                }
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        });
        
        // Fetch payee data from server
        function fetchPayeeData() {
            // Show loading message
            payeeTableBody.innerHTML = `
            <tr>
                <td colspan="4">
                    <div class="payee-loader">
                        <div class="payee-loader-spinner"></div>
                        <div class="payee-loader-text">Loading payee data...</div>
                    </div>
                </td>
            </tr>`;
            
            // Use AJAX to fetch data
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_payees.php', true);
            
            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const payees = JSON.parse(this.responseText);
                        
                        if (payees.length === 0) {
                            payeeTableBody.innerHTML = `
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px;">
                                    <i class="bi bi-exclamation-circle text-warning me-2" style="font-size: 24px;"></i>
                                    <p>No payee data available</p>
                                </td>
                            </tr>`;
                            return;
                        }
                        
                        // Build the table rows
                        let html = '';
                        payees.forEach(payee => {
                            html += `
                            <tr>
                                <td><strong>${payee.payee_name || ''}</strong></td>
                                <td>${payee.address || ''}</td>
                                <td>${payee.nature || ''}</td>
                                <td>${payee.contact_no || ''}</td>
                            </tr>
                            `;
                        });
                        
                        payeeTableBody.innerHTML = html;
                        
                        // Apply any existing search filter
                        if (payeeSearch.value.trim() !== '') {
                            payeeSearch.dispatchEvent(new Event('input'));
                        }
                        
                    } catch (e) {
                        payeeTableBody.innerHTML = `
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px;">
                                <i class="bi bi-exclamation-triangle text-danger me-2" style="font-size: 24px;"></i>
                                <p>Error loading payee data</p>
                            </td>
                        </tr>`;
                        console.error('Error parsing payee data:', e);
                    }
                } else {
                    payeeTableBody.innerHTML = `
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 30px;">
                            <i class="bi bi-exclamation-triangle text-danger me-2" style="font-size: 24px;"></i>
                            <p>Error loading payee data</p>
                        </td>
                    </tr>`;
                }
            };
            
            xhr.onerror = function() {
                payeeTableBody.innerHTML = `
                <tr>
                    <td colspan="4" style="text-align: center; padding: 30px;">
                        <i class="bi bi-wifi-off text-danger me-2" style="font-size: 24px;"></i>
                        <p>Error connecting to server</p>
                    </td>
                </tr>`;
            };
            
            xhr.send();
        }
    });

    // About Modal Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const aboutBtn = document.getElementById('aboutBtn');
        const aboutModal = document.getElementById('aboutModal');
        const closeAboutModal = document.getElementById('closeAboutModal');
        
        // Open About modal
        aboutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            aboutModal.style.display = 'flex';
        });
        
        // Close About modal
        closeAboutModal.addEventListener('click', function() {
            aboutModal.style.display = 'none';
        });
        
        // Close when clicking outside modal
        window.addEventListener('click', function(event) {
            if (event.target === aboutModal) {
                aboutModal.style.display = 'none';
            }
        });
    });
</script>

</html>