<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="navbar-brand ps-3" href="">
                <img src="../img/DTI_w12.png" alt="Logo" style="height: 100px; width: auto; max-width: 100%; ">
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link " href="dashboard.php">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Budget Officer Section -->
        <li class="nav-heading">Budget Management</li>
        
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#budget-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-cash-coin"></i><span>Budget Operations</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="budget-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="allotment_management.php">
                        <i class="bi bi-circle"></i><span>Allotment Management</span>
                    </a>
                </li>
                <li>
                    <a href="ors_management.php">
                        <i class="bi bi-circle"></i><span>ORS Management</span>
                    </a>
                </li>
                <li>
                    <a href="budget_reports.php">
                        <i class="bi bi-circle"></i><span>Budget Reports</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Chief Accountant Section -->
        <li class="nav-heading">Accounting Management</li>
        
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#accounting-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-journal-text"></i><span>Voucher Processing</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="accounting-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="review_dv.php">
                        <i class="bi bi-circle"></i><span>Review Vouchers</span>
                    </a>
                </li>
                <li>
                    <a href="endorsed_dv.php">
                        <i class="bi bi-circle"></i><span>Endorsed Vouchers</span>
                    </a>
                </li>
                <li>
                    <a href="accounting_reports.php">
                        <i class="bi bi-circle"></i><span>Accounting Reports</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Cashier Section -->
        <li class="nav-heading">Payment Management</li>
        
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#payment-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-credit-card"></i><span>Payment Operations</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="payment-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="pending_payments.php">
                        <i class="bi bi-circle"></i><span>Pending Payments</span>
                    </a>
                </li>
                <li>
                    <a href="completed_payments.php">
                        <i class="bi bi-circle"></i><span>Completed Payments</span>
                    </a>
                </li>
                <li>
                    <a href="ada_records.php">
                        <i class="bi bi-circle"></i><span>ADA Records</span>
                    </a>
                </li>
                <li>
                    <a href="payment_reports.php">
                        <i class="bi bi-circle"></i><span>Payment Reports</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Book Keeper Section -->
        <li class="nav-heading">Financial Records</li>
        
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#financial-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-book"></i><span>Financial Management</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="financial-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <li>
                    <a href="account_title.php">
                        <i class="bi bi-circle"></i><span>Account Titles</span>
                    </a>
                </li>
                <li>
                    <a href="journal_entries.php">
                        <i class="bi bi-circle"></i><span>Journal Entries</span>
                    </a>
                </li>
                <li>
                    <a href="financial_reports.php">
                        <i class="bi bi-circle"></i><span>Financial Reports</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Reports Section -->
        <li class="nav-heading">System Management</li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="reports.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>System Reports</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="manage_users.php">
                <i class="bi bi-people"></i>
                <span>User Management</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="system_settings.php">
                <i class="bi bi-gear"></i>
                <span>System Settings</span>
            </a>
        </li>
    </ul>

</aside><!-- End Sidebar-->