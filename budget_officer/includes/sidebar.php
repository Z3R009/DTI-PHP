<style>
    .sidebar{
        margin-top:60px;
    }
</style>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
<div class="user-profile text-center mb-4">
        <div class="avatar mb-2">
            <img src="../img/incognito-circle-icon-md.png" alt="Profile" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid rgba(255,255,255,0.2);">
        </div>
        <h5 class="text-dark fw-bold mb-0">Budget Officer</h5>
        <p class="text-dark-emphasis small">Department of Trade & Industry</p>
    </div>

    <ul class="sidebar-nav" id="sidebar-nav">
        
      
        
        <li class="nav-item">
            <a class="nav-link " href="dashboard.php">
                <i class="bi bi-grid"></i>
                <span>DASHBOARD</span>
            </a>
        </li>

        <li class="nav-heading mt-3">Financial Management</li>

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#status-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>STATUS OF FUND</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="status-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <!-- Personnel Services Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#personal-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>PERSONNEL SERVICES</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="personal-nav" class="nav-content collapse" data-bs-parent="#status-nav">
                        <li>
                            <a href="oo1_personalServices.php">
                                <i class="bi bi-circle"></i><span>001-Personnel Services</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2_tida.php">
                                <i class="bi bi-circle"></i><span>002-Tida Contractual</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- MOOE Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#mooe-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>MOOE</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="mooe-nav" class="nav-content collapse" data-bs-parent="#status-nav">
                        <li>
                            <a href="gas.php">
                                <i class="bi bi-circle"></i><span>GAS</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo1.php">
                                <i class="bi bi-circle"></i><span>OO1</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2.php">
                                <i class="bi bi-circle"></i><span>OO2</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3.php">
                                <i class="bi bi-circle"></i><span>OO3</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_1.php">
                                <i class="bi bi-circle"></i><span>OO3.1</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_2.php">
                                <i class="bi bi-circle"></i><span>OO3.2</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_3.php">
                                <i class="bi bi-circle"></i><span>OO3.3</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_1.php">
                                <i class="bi bi-circle"></i><span>OO4.1.1</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_2.php">
                                <i class="bi bi-circle"></i><span>OO4.1.2</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- CARP Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#carp-fund-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>CARP</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="carp-fund-nav" class="nav-content collapse" data-bs-parent="#status-nav">
                        <li>
                            <a href="oo3_carp.php">
                                <i class="bi bi-circle"></i><span>OO3-Carp</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- RAPID Growth Project Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#rapid-fund-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>RAPID GROWTH PROJECT</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="rapid-fund-nav" class="nav-content collapse" data-bs-parent="#status-nav">
                        <li>
                            <a href="o1_rapidRO12.php">
                                <i class="bi bi-circle"></i><span>O1-Rapid RO 12</span>
                            </a>
                        </li>
                        <li>
                            <a href="o2_rapid.php">
                                <i class="bi bi-circle"></i><span>O2-Rapid</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#obligation-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>OBLIGATION</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="obligation-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <!-- Personnel Services Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#personal-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>PERSONNEL SERVICES</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="personal-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="oo1_personalServicesObligation.php">
                                <i class="bi bi-circle"></i><span>001-Personnel Services</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2_tidaObligation.php">
                                <i class="bi bi-circle"></i><span>002-Tida Contractual</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- MOOE Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#mooe-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>MOOE</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="mooe-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="gas_obligation.php">
                                <i class="bi bi-circle"></i><span>GAS</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo1_obligation.php">
                                <i class="bi bi-circle"></i><span>OO1</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2_obligation.php">
                                <i class="bi bi-circle"></i><span>OO2</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_obligation.php">
                                <i class="bi bi-circle"></i><span>OO3</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_1_obligation.php">
                                <i class="bi bi-circle"></i><span>OO3.1</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_2_obligation.php">
                                <i class="bi bi-circle"></i><span>OO3.2</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_3_obligation.php">
                                <i class="bi bi-circle"></i><span>OO3.3</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_1_obligation.php">
                                <i class="bi bi-circle"></i><span>OO4.1.1</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_2_obligation.php">
                                <i class="bi bi-circle"></i><span>OO4.1.2</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- CARP Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#carp-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>CARP</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="carp-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="oo3_carpObligation.php">
                                <i class="bi bi-circle"></i><span>OO3-Carp</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- RAPID Growth Project Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#rapid-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>RAPID GROWTH PROJECT</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="rapid-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="o1_rapidRO12Obligation.php">
                                <i class="bi bi-circle"></i><span>O1-Rapid RO 12</span>
                            </a>
                        </li>
                        <li>
                            <a href="o2_rapidObligation.php">
                                <i class="bi bi-circle"></i><span>O2-Rapid</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="nav-heading mt-3">Reports & Analytics</li>
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#reports-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>REPORTS</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="reports-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                <!-- Personnel Services Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#personal-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>PERSONNEL SERVICES</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="personal-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="oo1_personnelReport.php">
                                <i class="bi bi-circle"></i><span>001-Personnel Services</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2_tidaReport.php">
                                <i class="bi bi-circle"></i><span>002-Tida Contractual</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- MOOE Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#mooe-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>MOOE</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="mooe-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="gasReport.php">
                                <i class="bi bi-circle"></i><span>GAS REPORT</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo1Report.php">
                                <i class="bi bi-circle"></i><span>OO1</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo2Report.php">
                                <i class="bi bi-circle"></i><span>OO2</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3Report.php">
                                <i class="bi bi-circle"></i><span>OO3</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_1Report.php">
                                <i class="bi bi-circle"></i><span>OO3.1</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_2Report.php">
                                <i class="bi bi-circle"></i><span>OO3.2</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo3_3Report.php">
                                <i class="bi bi-circle"></i><span>OO3.3</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_1Report.php">
                                <i class="bi bi-circle"></i><span>OO4.1.1</span>
                            </a>
                        </li>
                        <li>
                            <a href="oo4_1_2Report.php">
                                <i class="bi bi-circle"></i><span>OO4.1.2</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- CARP Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#carp-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>CARP</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="carp-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="oo3_carpReport.php">
                                <i class="bi bi-circle"></i><span>OO3-Carp</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- RAPID Growth Project Submenu -->
                <li>
                    <a class="nav-link collapsed" data-bs-target="#rapid-obligation-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-bar-chart"></i><span>RAPID GROWTH PROJECT</span><i class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="rapid-obligation-nav" class="nav-content collapse" data-bs-parent="#obligation-nav">
                        <li>
                            <a href="o1_rapid12Report.php">
                                <i class="bi bi-circle"></i><span>O1-Rapid RO 12</span>
                            </a>
                        </li>
                        <li>
                            <a href="o2_rapidReport.php">
                                <i class="bi bi-circle"></i><span>O2-Rapid</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li> 
        </ul>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.querySelector('.toggle-sidebar-btn'); 
    const sidebarState = localStorage.getItem('sidebarState');
    
    if (sidebarState === 'hidden') {
        sidebar.classList.add('toggle-sidebar');
        document.body.classList.add('toggle-sidebar'); 
    } else{
        sidebar.classList.remove('toggle-sidebar');
        document.body.classList.remove('toggle-sidebar');
    }
    
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            if (sidebar.classList.contains('toggle-sidebar')) {
                localStorage.setItem('sidebarState', 'shown');
            } else {
                localStorage.setItem('sidebarState', 'hidden');
            }
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