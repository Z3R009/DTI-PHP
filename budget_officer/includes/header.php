<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="dashboard.php" class="logo d-flex align-items-center">
            <img src="../img/DTI_short.png" alt="DTI Logo">
            <span class="d-none d-lg-block fw-bold">DTI Region 12</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar ms-auto me-4 d-none d-md-flex">
        <!-- <form class="search-form d-flex align-items-center" method="POST" action="#">
            <input type="text" name="query" placeholder="Search" title="Enter search keyword">
            <button type="submit" title="Search"><i class="bi bi-search"></i></button>
        </form> -->
    </div><!-- End Search Bar -->

    <nav class="header-nav">
        <ul class="d-flex align-items-center">
            <li class="nav-item d-block d-md-none">
                <a class="nav-link nav-icon search-bar-toggle" href="#">
                    <i class="bi bi-search"></i>
                </a>
            </li><!-- End Search Icon-->

            <nav class="header-nav">
            <ul class="d-flex align-items-center">
                <li class="nav-item d-block d-md-none">
                    <a class="nav-link nav-icon search-bar-toggle" href="#">
                        <i class="bi bi-search"></i>
                    </a>
                </li>

            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <img src="../img/DTI_short.png" alt="Profile" class="rounded-circle">
                    <span class="d-none d-md-block dropdown-toggle ps-2">Budget Officer</span>
                </a><!-- End Profile Image Icon -->
        

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="settings.php">
                            <i class="bi bi-gear"></i>
                            <span>Change Password</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                  
                  

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </a>
                    </li>
                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->
        </ul>
    </nav><!-- End Icons Navigation -->
</header><!-- End Header -->