<?php
session_start();
include 'DBConnection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registry of Suppliers - DTI Region 12</title>
    <link href="bootstrap-5.2.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <style>
        .suppliers-container {
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

        .search-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .search-box {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid #e1e1e1;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            border-color: #03045e;
            box-shadow: 0 0 0 3px rgba(3, 4, 94, 0.1);
            outline: none;
        }

        .search-box i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 18px;
        }

        .suppliers-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #03045e;
            font-weight: 600;
            padding: 15px 20px;
            border-bottom: 2px solid #e1e1e1;
        }

        .table tbody td {
            padding: 15px 20px;
            vertical-align: middle;
            border-bottom: 1px solid #e1e1e1;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .supplier-name {
            font-weight: 500;
            color: #03045e;
        }

        .supplier-category {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .category-internal {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .category-external {
            background-color: #fff3e0;
            color: #f57c00;
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
            .suppliers-container {
                margin-top: 80px;
                padding: 0 15px;
            }

            .page-header {
                padding: 20px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .table thead {
                display: none;
            }

            .table tbody tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
            }

            .table tbody td {
                display: block;
                text-align: right;
                padding: 10px 15px;
                border-bottom: 1px solid #e1e1e1;
            }

            .table tbody td:before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
                color: #03045e;
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
                    <a href="suppliers.php" class="nav-link active">
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

    <div class="suppliers-container">
        <div class="page-header">
            <h1><i class="bi bi-people-fill"></i> Registry of Suppliers</h1>
            <p>Comprehensive list of all registered suppliers and vendors</p>
        </div>

        <div class="search-section">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="supplierSearch" placeholder="Search suppliers by name, address, or category...">
            </div>
        </div>

        <div class="suppliers-table">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>Address</th>
                            <th>Category</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody id="suppliersTableBody">
                        <!-- Data will be loaded here via AJAX -->
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="index.php" class="back-btn">
        <i class="bi bi-arrow-left"></i>
    </a>

    <script src="bootstrap-5.2.3/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('supplierSearch');
            const tableBody = document.getElementById('suppliersTableBody');

            // Fetch suppliers data
            function fetchSuppliers() {
                fetch('get_payees.php')
                    .then(response => response.json())
                    .then(suppliers => {
                        if (suppliers.length === 0) {
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-exclamation-circle text-warning" style="font-size: 24px;"></i>
                                        <p class="mt-2">No suppliers found</p>
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        let html = '';
                        suppliers.forEach(supplier => {
                            html += `
                                <tr>
                                    <td data-label="Supplier Name">
                                        <span class="supplier-name">${supplier.payee_name || 'N/A'}</span>
                                    </td>
                                    <td data-label="Address">${supplier.address || 'N/A'}</td>
                                    <td data-label="Category">
                                        <span class="supplier-category ${supplier.nature === 'Internal' ? 'category-internal' : 'category-external'}">
                                            ${supplier.nature || 'N/A'}
                                        </span>
                                    </td>
                                    <td data-label="Contact Number">${supplier.contact_no || 'N/A'}</td>
                                    <td data-label="Email">${supplier.email || 'N/A'}</td>
                                </tr>
                            `;
                        });
                        tableBody.innerHTML = html;
                    })
                    .catch(error => {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 24px;"></i>
                                    <p class="mt-2">Error loading suppliers data</p>
                                </td>
                            </tr>
                        `;
                        console.error('Error:', error);
                    });
            }

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchText = this.value.toLowerCase();
                const rows = tableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchText) ? '' : 'none';
                });
            });

            // Initial load
            fetchSuppliers();
        });
    </script>
</body>
</html> 