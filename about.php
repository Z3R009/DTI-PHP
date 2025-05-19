<?php
session_start();
include 'DBConnection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - DTI Region 12</title>
    <link href="bootstrap-5.2.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .about-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #03045e 0%, #D90429 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .page-header p {
            opacity: 0.9;
            margin: 0;
        }

        .about-content {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .about-section {
            padding: 30px;
            border-bottom: 1px solid #e1e1e1;
        }

        .about-section:last-child {
            border-bottom: none;
        }

        .about-section h2 {
            color: #03045e;
            font-size: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .about-section h2 i {
            color: #D90429;
        }

        .about-section p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .feature-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .feature-card i {
            font-size: 24px;
            color: #03045e;
            margin-bottom: 15px;
        }

        .feature-card h3 {
            color: #03045e;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .contact-item i {
            font-size: 24px;
            color: #D90429;
        }

        .contact-item div {
            flex: 1;
        }

        .contact-item h4 {
            color: #03045e;
            font-size: 16px;
            margin: 0 0 5px;
        }

        .contact-item p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }

        .back-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #03045e;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #D90429;
            transform: translateY(-2px);
            color: white;
        }

        @media (max-width: 768px) {
            .about-container {
                margin-top: 80px;
                padding: 0 15px;
            }

            .page-header {
                padding: 20px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .about-section {
                padding: 20px;
            }

            .about-section h2 {
                font-size: 20px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .contact-info {
                grid-template-columns: 1fr;
            }

            .back-btn {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
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
        }
    </style>
</head>
<body>
    <!-- Header -->
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
                    <a href="about.php" class="nav-link active">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>About</span>
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <div class="about-container">
        <div class="page-header">
            <h1><i class="bi bi-info-circle-fill"></i> About the System</h1>
            <p>Learn more about the DTI Region 12 Financial Processing System</p>
        </div>

        <div class="about-content">
            <div class="about-section">
                <h2><i class="bi bi-building"></i> Overview</h2>
                <p>The DTI Region 12 Financial Processing System is a comprehensive platform designed to streamline and modernize the financial processes of the Department of Trade and Industry Region 12. This system provides a secure and efficient way to manage financial transactions, supplier information, and other related administrative tasks.</p>
            </div>

            <div class="about-section">
                <h2><i class="bi bi-gear-fill"></i> Key Features</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <i class="bi bi-shield-lock-fill"></i>
                        <h3>Secure Authentication</h3>
                        <p>Role-based access control and secure user authentication system</p>
                    </div>
                    <div class="feature-card">
                        <i class="bi bi-people-fill"></i>
                        <h3>Supplier Management</h3>
                        <p>Comprehensive registry of suppliers with detailed information</p>
                    </div>
                    <div class="feature-card">
                        <i class="bi bi-cash-stack"></i>
                        <h3>Financial Processing</h3>
                        <p>Efficient handling of financial transactions and records</p>
                    </div>
                    <div class="feature-card">
                        <i class="bi bi-graph-up"></i>
                        <h3>Real-time Tracking</h3>
                        <p>Live monitoring and reporting of financial activities</p>
                    </div>
                </div>
            </div>

            <div class="about-section">
                <h2><i class="bi bi-telephone-fill"></i> Contact Information</h2>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <h4>Address</h4>
                            <p>DTI Region 12 Office, Koronadal City</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <h4>Phone</h4>
                            <p>(083) 228-0016</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-envelope-fill"></i>
                        <div>
                            <h4>Email</h4>
                            <p>ro12@dti.gov.ph</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="index.php" class="back-btn">
        <i class="bi bi-arrow-left"></i>
    </a>

    <script src="bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
</body>
</html> 