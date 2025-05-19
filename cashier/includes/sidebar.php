<style>
    .sidebar{
        margin-top:60px;
    }
</style>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <div class="user-profile text-center mb-4">
        <div class="avatar mb-2">
            <img src="../img/incognito-circle-icon-md.png" alt="Profile" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid rgba(0, 121, 107, 0.2);">
        </div>
        <h5 class="text-dark fw-bold mb-0">Cashier</h5>
        <p class="text-dark-emphasis small">Department of Trade & Industry</p>
    </div>

    <ul class="sidebar-nav nav flex-column" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-grid"></i>
                <span>DASHBOARD</span>
            </a>
        </li>

        <li class="nav-heading mt-3">Disbursement Vouchers</li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pending_payments.php' ? 'active' : ''; ?>" href="pending_payments.php">
                <i class="bi bi-file-text"></i>
                <span>PENDING PAYMENTS</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'completed_payments.php' ? 'active' : ''; ?>" href="completed_payments.php">
                <i class="bi bi-check-circle"></i>
                <span>COMPLETED PAYMENTS</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ada_records.php' ? 'active' : ''; ?>" href="ada_records.php">
                <i class="bi bi-bank"></i>
                <span>ADA RECORDS</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'add_payee.php' ? 'active' : ''; ?>" href="add_payee.php">
                <i class="bi bi-person-plus"></i>
                <span>ADD PAYEE</span>
            </a>
        </li>

        <li class="nav-heading mt-3">Reports</li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payment_reports.php' ? 'active' : ''; ?>" href="payment_reports.php">
                <i class="bi bi-file-text"></i>
                <span>PAYMENT REPORTS</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ada_report.php' ? 'active' : ''; ?>" href="ada_report.php">
                <i class="bi bi-bank"></i>
                <span>ADA BALANCE REPORT</span>
            </a>
        </li>
        
        <!-- <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'budget_report.php' ? 'active' : ''; ?>" href="budget_report.php">
                <i class="bi bi-cash-coin"></i>
                <span>CASH BUDGET REPORT</span>
            </a>
        </li> -->
    </ul>
</aside>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 280px;
    z-index: 996;
    transition: all 0.3s;
    padding: 20px;
    overflow-y: auto;
    background: #fff;
    box-shadow: 0 0 20px rgba(1, 41, 112, 0.1);
}

.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
    background-color: var(--accent-color);
    border-radius: 20px;
}

.sidebar-nav {
    padding: 0;
    margin: 0;
    list-style: none;
}

.sidebar-nav .nav-item {
    margin-bottom: 5px;
}

.sidebar-nav .nav-link {
    display: flex;
    align-items: center;
    font-size: 14px;
    font-weight: 500;
    color: #344767;
    padding: 12px 15px;
    border-radius: 8px;
    transition: all 0.3s;
}

.sidebar-nav .nav-link i {
    font-size: 16px;
    margin-right: 10px;
    color: #344767;
}

.sidebar-nav .nav-link:hover,
.sidebar-nav .nav-link.active,
.sidebar-nav .nav-link ul .active{
    background-color: rgba(10, 111, 253, 0.42);
    color: white;
}

.sidebar-nav .nav-link:hover i,
.sidebar-nav .nav-link.active i {
    color:white;
}

.sidebar-nav .nav-heading {
    font-size: 12px;
    letter-spacing: 1px;
    color: #344767;
    margin: 20px 0 10px;
    font-weight: 600;
}

.user-profile {
    padding: 20px;
    background: rgba(10, 111, 253, 0.42);
    border-radius: 10px;
    margin-bottom: 20px;
    border: 1px solid rgba(0, 0, 0, 0.05);
}
</style> 