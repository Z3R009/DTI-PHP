<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="dashboard.php" class="logo d-flex align-items-center">
            <div class="logo-container">
                <img src="../img/dti_logo.png" alt="DTI Logo" class="logo-img">
            </div>
            <div class="logo-text">
                <span class="d-none d-lg-block fw-bold">DTI R12</span>
                <span class="d-none d-lg-block subtitle">Financial Processing System</span>
            </div>
        </a>
        <i class="bi bi-list toggle-sidebar-btn fs-4"></i>
    </div>

    <!-- search dapat unod ani -->
    <div class="search-bar ms-auto me-4 d-none d-md-flex">
        <!-- <form class="search-form d-flex align-items-center" method="POST" action="#">
            <input type="text" name="query" placeholder="Search..." title="Enter search keyword">
            <button type="submit" title="Search"><i class="bi bi-search"></i></button>
        </form> -->
    </div>

    <nav class="header-nav">
        <ul class="d-flex align-items-center">
            <!-- <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-bell fs-5 me-2"></i>
                </a>
            </li> -->

            <!-- change password -->
            <li class="nav-item dropdown pe-3">
                <a class="dropdown-item d-flex align-items-center" href="settings.php">
                    <i class="bi bi-gear fs-5"></i>
                </a>
            </li>

            <!-- logout -->
            <li class="nav-item dropdown pe-3">
                <a class="dropdown-item d-flex align-items-center" href="../logout.php">
                    <i class="bi bi-box-arrow-right text-danger fs-4 me-2"></i>
                </a>
            </li>
        </ul>
    </nav>
</header>

<style>
.header {
    background: #fff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 0.5rem 2rem;
    height: 60px;
}

.logo {
    text-decoration: none;
    transition: all 0.3s ease;
}

.logo:hover {
    transform: translateY(-1px);
}

.logo-container {
    background: #f8f9ff;
    padding: 5px;
    border-radius: 12px;
    margin-right: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;

    /* Increase size here */
  
}



.logo:hover .logo-container {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: scale(1.02);
}

.logo-img {
    height: 100px;
    width: 100px;
    height: auto;
    width: auto;
    display: block;
    object-fit: contain;
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-text span:first-child {
    color: #012970;
    font-size: 1.4rem;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

.logo-text .subtitle {
    color: #6c757d;
    font-size: 0.9rem;
    font-weight: 500;
    letter-spacing: 0.3px;
}

.search-form {
    position: relative;
    background: #f6f9ff;
    border-radius: 50px;
    padding: 0.5rem 1rem;
}

.search-form input {
    border: 0;
    padding: 0.5rem;
    font-size: 0.9rem;
    width: 200px;
    background: transparent;
}

.search-form input:focus {
    outline: none;
}

.search-form button {
    border: 0;
    font-size: 1rem;
    background: transparent;
    color: #012970;
    padding: 0 0.5rem;
}

.header-nav .nav-link {
    color: #012970;
    transition: 0.3s;
}

.header-nav .nav-link:hover {
    color: #4154f1;
}

.toggle-sidebar-btn {
    cursor: pointer;
    color: #012970;
    transition: 0.3s;
}

.toggle-sidebar-btn:hover {
    color: #4154f1;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>