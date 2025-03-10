<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Summary Report</title>
    <script type="module" src="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://cdn.jsdelivr.net/npm/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f5f7fa;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
            position: relative;
            transition: grid-template-columns 0.3s ease;
        }

        .dashboard-container.collapsed {
            grid-template-columns: 80px 1fr;
        }

        .sidebar {
            background-color: #EDF2F4;
            color: #2B2D42;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            width: 250px;
            cursor: pointer;
        }

        .dashboard-container.collapsed .sidebar {
            width: 80px;
            padding: 20px 10px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
            text-align: center;
        }

        .logo-container img {
            width: 60px;
            height: 60px;
            min-width: 50px;
        }

        .logo-container h2 {
            font-size: 24px;
            color: #2B2D42;
            white-space: nowrap;
        }

        .dashboard-container.collapsed .logo-text {
            display: none;
        }

        .admin-text {
            font-weight: 600;
            color: #0077b6;
            white-space: nowrap;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            margin: 8px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            background-color: #8d99ae24;
            color: #2B2D42;
            white-space: nowrap;
            overflow: hidden;
            text-decoration: none;
        }

        .nav-item ion-icon {
            min-width: 24px;
        }

        .nav-item:hover {
            background-color: #0077b6;
            color: #EDF2F4;
        }

        .nav-item.active {
            background-color: #0077b6;
            color: white;
        }

        .dashboard-container.collapsed .nav-text {
            display: none;
        }

        .main-content {
            padding: 0;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            background-color: #f5f7fa;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
            transition: all 0.3s ease;
        }

        .content-wrapper {
            padding: 30px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 600;
            color: #03045e;
        }

        .percentage {
            font-size: 0.7rem;
            color: #28a745;
            font-weight: 200;
            margin-top: 5px;
        }

        .percentage.negative {
            color: #dc3545;
        }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 400px;
            position: relative;
        }

        .chart-wrapper {
            position: relative;
            height: 320px;
            width: 100%;
        }

        .program-table-container {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .program-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .program-table th,
        .program-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .program-table th {
            color: #666;
            font-weight: 500;
        }

        .progress-container {
            width: 100%;
            background-color: #e9ecef;
            border-radius: 4px;
            height: 8px;
        }

        .progress-bar {
            height: 100%;
            border-radius: 4px;
            background-color: #0077b6;
        }

        .special-projects {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .project-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .project-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .project-card h4 {
            font-size: 1rem;
            margin-bottom: 8px;
            color: #2B2D42;
        }

        .project-card .budget {
            font-size: 1.2rem;
            font-weight: 600;
            color: #03045e;
            margin-bottom: 5px;
        }

        .project-card .status {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .header.scrolled {
            background-color: #EDF2F4;
            box-shadow: 3px 4px 4px rgba(0, 0, 0, 0.1);
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-header h1 {
            color: #03045e;
            margin-bottom: 5px;
        }

        .report-header .subtitle {
            color: #666;
            font-size: 1rem;
        }

        .report-dropdown {
            position: relative;
            display: inline-block;
            left: 52rem;
            margin-bottom: 20px;
        }

        .dropdown-button {
            display: flex;
            align-items: center;
            background-color: #0077b6;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            left: 0;
        }

        .dropdown-button:hover {
            background-color: #03045e;
        }

        .dropdown-button ion-icon {
            margin-left: 8px;
            font-size: 20px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 280px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            z-index: 101;
            margin-top: 5px;
            right: 0;
            /* Align dropdown to the right */
        }


        .dropdown-content.show {
            display: block;
        }

        .dropdown-item {
            padding: 12px 20px;
            display: block;
            font-size: 14px;
            color: #2B2D42;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: #f5f7fa;
        }

        .dropdown-item.active {
            background-color: #e6f3f8;
            color: #0077b6;
            font-weight: 500;
        }

        .dropdown-subtitle {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }


        .print-btn {
            padding: 10px 20px;
            background-color: #03045e;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .print-btn:hover {
            background-color: #0077b6;
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        @media print {

            .sidebar,
            .print-btn {
                display: none;
            }

            .dashboard-container {
                grid-template-columns: 1fr;
            }

            body {
                background-color: white;
            }

            .content-wrapper {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="logo-container">
                <img src="img/dti_logo.png" alt="Company Logo" width="100" height="100">
                <div class="logo-text">
                    <h2>DTI</h2>
                    <span class="admin-text">Admin</span>
                </div>
            </div>
            <a href="Dashboard.html" class="nav-item">
                <ion-icon name="home-outline"></ion-icon>
                <span class="nav-text">Dashboard</span>
            </a>
            <div class="nav-item">
                <ion-icon name="cash-outline"></ion-icon>
                <span class="nav-text">Disbursements</span>
            </div>
            <div class="nav-item active">
                <ion-icon name="document-text-outline"></ion-icon>
                <span class="nav-text">Reports</span>
            </div>
            <div class="nav-item">
                <ion-icon name="settings-outline"></ion-icon>
                <span class="nav-text">Settings</span>
            </div>
        </div>
        <div class="main-content">
            <div class="header">
                <h1>Budget Summary Report</h1>
                <button class="print-btn" onclick="window.print()">
                    <ion-icon name="print-outline"></ion-icon>
                    Print Report
                </button>
            </div>
            <div class="content-wrapper">
                <div class="report-dropdown">
                    <button class="dropdown-button" onclick="toggleDropdown()">
                        <span>FAR 1 - BUDGET SUMMARY REPORT</span>
                        <ion-icon name="chevron-down-outline"></ion-icon>
                    </button>
                    <div class="dropdown-content" id="reportDropdown">
                        <a href="#" class="dropdown-item active">
                            FAR 1 - BUDGET SUMMARY REPORT
                            <div class="dropdown-subtitle">AS OF SEPTEMBER 30, 2021 (3RD QUARTER)</div>
                        </a>
                        <a href="#" class="dropdown-item">
                            FAR 2 - STATEMENT OF ALLOTMENTS
                            <div class="dropdown-subtitle">AS OF SEPTEMBER 30, 2021</div>
                        </a>
                        <a href="#" class="dropdown-item">
                            FAR 3 - OBLIGATION REQUEST STATUS
                            <div class="dropdown-subtitle">AS OF SEPTEMBER 30, 2021</div>
                        </a>
                        <a href="#" class="dropdown-item">
                            FAR 4 - DISBURSEMENT STATUS
                            <div class="dropdown-subtitle">AS OF SEPTEMBER 30, 2021</div>
                        </a>
                    </div>
                </div>
                <div class="stats-container">
                    <div class="stat-card">
                        <h3>Total Allotment</h3>
                        <div class="value">₱170,948,045.08</div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Obligations</h3>
                        <div class="value">₱117,914,726.52</div>
                        <div class="percentage">69% of total allotment</div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Balances</h3>
                        <div class="value">₱53,033,318.56</div>
                        <div class="percentage">31% remaining</div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Discrepancy</h3>
                        <div class="value">₱72,288,233.96</div>
                        <div class="percentage negative">Allotment per FAR</div>
                    </div>
                </div>

                <div class="charts-container">
                    <div class="chart-card">
                        <h3>Budget Allocation by Category</h3>
                        <div class="chart-wrapper">
                            <canvas id="budgetAllocationChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3>Budget Execution Rate</h3>
                        <div class="chart-wrapper">
                            <canvas id="executionRateChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="program-table-container">
                    <h3>Major Programs Budget Summary</h3>
                    <table class="program-table">
                        <thead>
                            <tr>
                                <th>Program</th>
                                <th>Allotment</th>
                                <th>Obligations</th>
                                <th>Balance</th>
                                <th>Execution Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>PS (Personal Services)</td>
                                <td>₱62,389,630.62</td>
                                <td>₱48,686,774.69</td>
                                <td>₱13,702,855.93</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 78%"></div>
                                    </div>
                                    <div style="text-align: right; font-size: 0.8rem;">78%</div>
                                </td>
                            </tr>
                            <tr>
                                <td>MOOE</td>
                                <td>₱103,424,414.46</td>
                                <td>₱65,441,352.99</td>
                                <td>₱37,983,061.57</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 63%"></div>
                                    </div>
                                    <div style="text-align: right; font-size: 0.8rem;">63%</div>
                                </td>
                            </tr>
                            <tr>
                                <td>RLIP</td>
                                <td>₱5,134,000.00</td>
                                <td>₱3,786,598.84</td>
                                <td>₱1,347,401.06</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 74%"></div>
                                    </div>
                                    <div style="text-align: right; font-size: 0.8rem;">74%</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="special-projects">
                    <h3>Special Projects Summary</h3>
                    <div class="project-items">
                        <div class="project-card">
                            <h4>Youth Entrepreneurship Program</h4>
                            <div class="budget">₱576,200.00</div>
                            <span class="status status-active">Active</span>
                        </div>
                        <div class="project-card">
                            <h4>Coffee ICE</h4>
                            <div class="budget">₱40,000.00</div>
                            <span class="status status-active">Active</span>
                        </div>
                        <div class="project-card">
                            <h4>WERABALES ICE</h4>
                            <div class="budget">₱40,000.00</div>
                            <span class="status status-active">Active</span>
                        </div>
                        <div class="project-card">
                            <h4>Rubber ICE</h4>
                            <div class="budget">₱100,000.00</div>
                            <span class="status status-pending">Pending</span>
                        </div>
                        <div class="project-card">
                            <h4>Palm Oil Industry</h4>
                            <div class="budget">₱50,000.00</div>
                            <span class="status status-active">Active</span>
                        </div>
                        <div class="project-card">
                            <h4>Processed Fruits & Nuts</h4>
                            <div class="budget">₱50,000.00</div>
                            <span class="status status-active">Active</span>
                        </div>
                    </div>
                </div>

                <div class="program-table-container">
                    <h3>Rapid Growth Projects (GAA 2021)</h3>
                    <table class="program-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Allotment</th>
                                <th>Obligations</th>
                                <th>Balance</th>
                                <th>Execution Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>RAPID - LP (MOOE)</td>
                                <td>₱7,013,035.26</td>
                                <td>₱4,346,584.13</td>
                                <td>₱2,666,451.13</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 62%"></div>
                                    </div>
                                    <div style="text-align: right; font-size: 0.8rem;">62%</div>
                                </td>
                            </tr>
                            <tr>
                                <td>RAPID - LP (CAPITAL OUTLAY)</td>
                                <td>₱1,168,840.00</td>
                                <td>₱0.00</td>
                                <td>₱1,168,640.00</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 0%"></div>
                                    </div>
                                    <div style="text-align: right; font-size: 0.8rem;">0%</div>
                                </td>
                            </tr>
                            <tr>
                                <td>RAPID - GoP</td>
                                <td>₱2,280,991.15</td>
                                <td>₱322,434.75</td>
                                <td>₱1,958,556.40</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 14%"></div>
                                    </div>
                                    <div style="text-align: right; font-size: 0.8rem;">14%</div>
                                </td>
                            </tr>
                            <tr>
                                <td>RAPID - GoP (Salaries)</td>
                                <td>₱3,833,473.82</td>
                                <td>₱1,445,053.93</td>
                                <td>₱2,388,419.89</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 38%"></div>
                                    </div>
                                    <div style="text-align: right; font-size: 0.8rem;">38%</div>
                                </td>
                            </tr>
                            <tr>
                                <td>RAPID - CIG (LP-CO)</td>
                                <td>₱114,400.00</td>
                                <td>₱0.00</td>
                                <td>₱114,400.00</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 0%"></div>
                                    </div>
                                    <div style="text-align: right; font-size: 0.8rem;">0%</div>
                                </td>
                            </tr>
                            <tr>
                                <td>RAPID - CIG (GRANT)</td>
                                <td>₱1,031,189.00</td>
                                <td>₱0.00</td>
                                <td>₱1,031,189.00</td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: 0%"></div>
                                    </div>
                                    <div style="text-align: right; font-size: 0.8rem;">0%</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        const sidebar = document.querySelector('.sidebar');
        const dashboardContainer = document.querySelector('.dashboard-container');

        sidebar.addEventListener('click', function (e) {
            dashboardContainer.classList.toggle('collapsed');
        });

        // Add scroll event listener for header
        window.addEventListener('scroll', function () {
            const header = document.querySelector('.header');

            if (window.scrollY > 20) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });


        function toggleDropdown() {
            document.getElementById("reportDropdown").classList.toggle("show");
        }

        // Close the dropdown if the user clicks outside of it
        window.onclick = function (event) {
            if (!event.target.matches('.dropdown-button') && !event.target.matches('.dropdown-button *')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
        // Budget Allocation Chart
        const allocationCtx = document.getElementById('budgetAllocationChart').getContext('2d');
        new Chart(allocationCtx, {
            type: 'bar',
            data: {
                labels: ['PS', 'MOOE', 'RLIP', 'Special Projects', 'Rapid Growth'],
                datasets: [{
                    label: 'Allotment',
                    data: [62389630.62, 103424414.46, 5134000.00, 1138360.00, 15441729.23],
                    backgroundColor: '#0077b6'
                }, {
                    label: 'Obligations',
                    data: [48686774.69, 65441352.99, 3786598.84, 855848.00, 6114072.81],
                    backgroundColor: '#00b4d8'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return '₱' + value.toLocaleString('en-PH', { maximumFractionDigits: 0 });
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ₱' + context.raw.toLocaleString('en-PH', { maximumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        });
        const executionCtx = document.getElementById('executionRateChart').getContext('2d');
        new Chart(executionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Obligated', 'Remaining Balance'],
                datasets: [{
                    data: [69, 31],
                    backgroundColor: ['#0077b6', '#e9ecef']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.label + ': ' + context.raw + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>