

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
        <h5 class="text-dark fw-bold mb-0">Chief Accountant</h5>
        <p class="text-dark-emphasis small">Department of Trade & Industry</p>
    </div>

    <ul class="sidebar-nav nav flex-column" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-grid"></i>
                <span>DASHBOARD</span>
            </a>
        </li>

        <li class="nav-heading mt-3">CASH ALLOTMENT</li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#status-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>DRAFT</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <li>
                <ul id="status-nav" class="nav-content collapse" data-bs-parent="#status-nav">
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
                            <i class="bi bi-circle"></i><span>101 REG</span>
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
                        <a href="#">
                            <i class="bi bi-circle"></i><span>LP</span>
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="bi bi-circle"></i><span>RAPID_GRANT</span>
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
        background:  rgba(10, 111, 253, 0.42);
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

    @media (max-width: 1199px) {
        .sidebar {
            left: -280px;
        }
        
        .toggle-sidebar .sidebar {
            left: 0;
        }
    }
</style>
  <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>
    <script src="../NiceAdmin/assets/js/main.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
        const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
        const sidebar = document.querySelector('.sidebar');
        const main = document.querySelector('.main');
        const header = document.querySelector('.header');

        toggleSidebarBtn.addEventListener('click', () => {
            document.body.classList.toggle('toggle-sidebar');
        });
    });
</script> 
>>>>>>> bb65599a764611973b2ef0a6394426134f56bf37
