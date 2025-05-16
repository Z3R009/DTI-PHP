<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav nav flex-column" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-grid"></i>
                <span>DASHBOARD</span>
            </a>
        </li>

        <li class="nav-heading mt-3">CASH ALLOTMENT</li>
        <li class="nav-item">
         <a class="nav-link" href="#">
                <i class="bi bi-bar-chart"></i><span>REPORT OF DISBURSEMENT</span>
            </a>
            <li>
               <ul class="nav-content show">
                    <li>
                        <a href="rapidGOP.php">
                            <i class="bi bi-circle"></i><span>RAPID GOP</span>
                        </a>
                    </li>

                    <li>    
                        <a href="yamanGensan.php">
                            <i class="bi bi-circle"></i><span>YAMAN GENSAN</span>
                        </a>
                    </li>

                    <li>
                        <a href="roxii.php">
                            <i class="bi bi-circle"></i><span>RO XI</span>
                        </a>
                    </li>

                    <li>
                        <a href="bsmed.php">
                            <i class="bi bi-circle"></i><span>BSMED CFIDP LCCA</span>
                        </a>
                    </li>

                    <li>
                        <a href="msmedc.php">
                            <i class="bi bi-circle"></i><span>MSMED</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="trust_fund.php">
                            <i class="bi bi-circle"></i><span>TRUST FUND</span>
                        </a>
                    </li>

                    <li>
                        <a href="coconut.php">
                            <i class="bi bi-circle"></i><span>COCOLEVY</span>
                        </a>
                    </li>

                    <li>
                        <a href="rapidLoan.php">
                            <i class="bi bi-circle"></i><span>RAPID LOAN</span>
                        </a>
                    </li>

                    <li>
                        <a href="rapidGrant.php">
                            <i class="bi bi-circle"></i><span>RAPID GRANT</span>
                        </a>
                    </li>

                </ul>
            </li>
            
            <ul id="status-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
            </ul>
        </li>

        <li class="nav-heading mt-3">Disbursement Vouchers</li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pending_dv.php' ? 'active' : ''; ?>" href="pending_dv.php">
                <i class="bi bi-file-text"></i>
                <span>PENDING DV</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'endorsed_dv.php' ? 'active' : ''; ?>" href="endorsed_dv.php">
                <i class="bi bi-check-circle"></i>
                <span>ENDORSED DV</span>
            </a>
        </li>

        <li class="nav-heading mt-3">Reports</li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                <i class="bi bi-file-text"></i>
                <span>DV REPORTS</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payment_reports.php' ? 'active' : ''; ?>" href="payment_reports.php">
                <i class="bi bi-cash"></i>
                <span>PAYMENT REPORTS</span>
            </a>
        </li>
    </ul>
</aside>

<style>
    :root {
        --primary-color: #03045e;
        --primary-dark: #023e8a;
        --primary-light: #e0f2fe;
        --secondary-color: #8d99ae;
        --text-color: #2B2D42;
        --light-text: #6c757d;
        --border-color: #e2e8f0;
        --light-bg: #f8f9fa;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #dc3545;
        --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --hover-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
        --transition: all 0.3s ease;

    }

    .sidebar {
        position: fixed;
        top: 0;
         margin-top: 60px;
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
    .sidebar-nav .nav-link ul .active {
        background-color: #03045e;
        color: white;
    }

    .sidebar-nav .nav-link:hover i,
    .sidebar-nav .nav-link.active i {
        color: white;
    }

    .sidebar-nav .nav-heading {
        font-size: 13px;
        letter-spacing: 1px;
        color: #344767;
        margin: 10px 0;
        font-weight: 600;
        text-transform: uppercase;
        padding-left: 15px;
    }

    .user-profile {
        padding: 20px;
        background: rgba(2, 61, 138, 0.7);
        border-radius: 15px;
        margin-bottom: 20px;
    }

    .user-profile .avatar {
        width: 80px;
        height: 80px;
        margin: 0 auto;
    }

    .user-profile h5 {
        color: #344767;
        font-weight: 600;
        margin-top: 10px;
    }

    .user-profile p {
        color: #344767;
        opacity: 0.8;
    }

    .dropdown-divider {
        margin: 0.5rem 0;
        opacity: 0.5;
        border-top: 1px solid #ccc;
        height: 0;
    }
    .nav-divider {
        height: 1px;
        background-color: #dee2e6;
        margin: 8px 0;
        width: 100%;
    }

    .sidebar-nav .nav-content.show {
        display: block;
        padding-left: 20px;
    }
    
    .sidebar-nav .nav-content.show li {
        margin-bottom: 5px;
    }
    
    .sidebar-nav .nav-content.show a {
        padding: 8px 0 8px 15px;
        font-size: 13px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        transition: all 0.3s;
    }
    
    .sidebar-nav .nav-content.show a:hover {
        background-color: rgba(2, 61, 138, 0.1);
    }
    
    .sidebar-nav .nav-content.show i {
        font-size: 12px;
        margin-right: 8px;
    }

    @media (max-width: 1199px) {
        .sidebar {
            left: -280px;
            transition: all 0.3s ease-in-out;
        }

        .toggle-sidebar .sidebar {
            left: 0;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-link, .sidebar-nav .nav-content a');

        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
                
                // If the active link is in a collapsible menu, expand that menu
                const parent = link.closest('.nav-content');
                if (parent) {
                    parent.classList.add('show');
                    const parentToggle = document.querySelector(`a[href="#"][data-bs-target="#${parent.id}"]`);
                    if (parentToggle) {
                        parentToggle.classList.remove('collapsed');
                    }
                }
            }
        });
        const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
        if (toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener('click', function() {
                document.body.classList.toggle('toggle-sidebar');
            });
        }
    });
</script>
 <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>
    <script src="../NiceAdmin/assets/js/main.js"></script>
