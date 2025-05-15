<style>
    .sidebar {
        margin-top: 60px;
    }
</style>

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <!-- <div class="user-profile text-center mb-4">
        <div class="avatar mb-2">
            <img src="../img/dti_logo.png " alt="Profile" class="rounded-circle"
                style="width: 80px; height: 80px; object-fit: cover; border: 3px solid rgba(0, 121, 107, 0.2);">
        </div>
        <h5 class="text-white fw-bold mb-0">Book Keeper</h5>
        <p class="text-white small">Department of Trade & Industry</p>
    </div> -->


    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link " href="dashboard.php">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <div class="nav-divider"></div>

        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>Forms</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="charts-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
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
                        <i class="bi bi-circle"></i><span>Journal Entry Voucher</span>
                    </a>
                </li>
            </ul>
        </li>
        <div class="nav-divider"></div>

        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="bi bi-menu-button-wide"></i><span>Library</span>
            </a>
            <ul class="nav-content show">
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
            </ul>
        </li>
        <div class="nav-divider"></div>

        <li class="nav-item">
            <a class="nav-link collapsed" href="payee.php">
                <i class="bi bi-person"></i><span>Registry of Supplier</span>
            </a>
        </li>
        <div class="nav-divider"></div>

        <li class="nav-item">
            <a class="nav-link collapsed" href="approver.php">
                <i class="bi bi-people"></i><span>Approver</span>
            </a>
        </li>
        <div class="nav-divider"></div>
        
        <li class="nav-item">
            <a class="nav-link collapsed" href="reports.php">
                <i class="bi bi-journal-text"></i>
                <span>Reports</span>
            </a>
        </li>
        <div class="nav-divider"></div>

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
    
    /* Additional styling for nav item dividers */
    .nav-divider {
        height: 1px;
        background-color: #dee2e6;
        margin: 8px 0;
        width: 100%;
    }
    
    /* Custom styling for permanently expanded dropdown */
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
                if (parent && parent.classList.contains('collapse')) {
                    parent.classList.add('show');
                    const parentToggle = document.querySelector(`[data-bs-target="#${parent.id}"]`);
                    if (parentToggle) {
                        parentToggle.classList.remove('collapsed');
                    }
                }
            }
        });
        
        // Initialize the toggle sidebar button
        const toggleSidebarBtn = document.querySelector('.toggle-sidebar-btn');
        if (toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener('click', function() {
                document.body.classList.toggle('toggle-sidebar');
            });
        }
        
        // Add click event listener to all collapsible items using Bootstrap's Collapse
        const collapsibleItems = document.querySelectorAll('.nav-link[data-bs-toggle="collapse"]');
        collapsibleItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-bs-target');
                const targetElement = document.querySelector(target);
                
                if (targetElement) {
                    // Create and use Bootstrap's Collapse instance
                    const bsCollapse = new bootstrap.Collapse(targetElement, {
                        toggle: false
                    });
                    
                    if (targetElement.classList.contains('show')) {
                        bsCollapse.hide();
                    } else {
                        // Close other open menus first
                        document.querySelectorAll('.nav-content.collapse.show').forEach(menu => {
                            if (menu.id !== targetElement.id.replace('#', '')) {
                                const menuCollapse = new bootstrap.Collapse(menu);
                                menuCollapse.hide();
                            }
                        });
                        
                        bsCollapse.show();
                    }
                }
            });
        });
        
        // Initialize all Bootstrap collapse elements
        const collapseElementList = [].slice.call(document.querySelectorAll('.collapse'));
        collapseElementList.map(function (collapseEl) {
            return new bootstrap.Collapse(collapseEl, {
                toggle: false
            });
        });
    });
</script>

