<style>
    .sidebar {
        margin-top: 60px;
    }
</style>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <div class="user-profile text-center mb-4">
        <div class="avatar mb-2">
            <img src="../img/dti_logo.png " alt="Profile" class="rounded-circle"
                style="width: 80px; height: 80px; object-fit: cover; border: 3px solid rgba(0, 121, 107, 0.2);">
        </div>
        <h5 class="text-white fw-bold mb-0">BookKeeper</h5>
        <p class="text-white small">Department of Trade & Industry</p>
    </div>


    <ul class="sidebar-nav" id="sidebar-nav">


        <li class="nav-item">
            <a class="nav-link " href="dashboard.php">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>Forms</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                <li>
                    <a href="ors.php">
                        <i class="bi bi-circle"></i><span>Obligation Request and Status</span>
                    </a>
                </li>
                <li>
                    <a href="dv.php">
                        <i class="bi bi-circle"></i><span>Disbursement Voucher</span>
                    </a>
                </li>
                <li>
                    <a href="jev.php">
                        <i class="bi bi-circle"></i><span>JEV</span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-menu-button-wide"></i><span>UACS</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="components-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                <li>
                    <a href="account_title.php">
                        <i class="bi bi-circle"></i><span>Account Title</span>
                    </a>
                </li>
                <li>
                    <a href="fund_cluster.php">
                        <i class="bi bi-circle"></i><span>Fund Cluster</span>
                    </a>
                </li>
                <li>
                    <a href="responsibility.php">
                        <i class="bi bi-circle"></i><span>Responsibility Center</span>
                    </a>
                </li>

                <li>
                    <a href="oopap.php">
                        <i class="bi bi-circle"></i><span>OO/PAP</span>
                    </a>
                </li>

                <li>
                    <a href="services.php">
                        <i class="bi bi-circle"></i><span>Services</span>
                    </a>
                </li>

                <li>
                    <a href="payee.php">
                        <i class="bi bi-circle"></i><span>Payee</span>
                    </a>
                </li>

                <li>
                    <a href="approver.php">
                        <i class="bi bi-circle"></i><span>Approver</span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="reports.php">
                <i class="bi bi-journal-text"></i>
                <span>Reports</span>
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
    .sidebar-nav .nav-link ul .active {
        background-color: rgba(2, 61, 138, 0.7);
        color: white;
    }

    .sidebar-nav .nav-link:hover i,
    .sidebar-nav .nav-link.active i {
        color: white;
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

    @media (max-width: 1199px) {
        .sidebar {
            left: -280px;
        }

        .toggle-sidebar .sidebar {
            left: 0;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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