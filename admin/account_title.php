<?php
//
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "dti-php";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch categories
$categories = $conn->query("SELECT * FROM financial_categories");
// Fetch subcategories
$subcategories = $conn->query("SELECT * FROM financial_subcategories");
// Fetch submodules
$submodules = $conn->query("SELECT * FROM financial_submodules");

// Retrieve data
$sql = "
    SELECT 
        fc.category_name, 
        fsc.subcategory_name, 
        fsm.submodule_name, 
        foc.object_name, 
        foc.uacs_code, 
        foc.status 
    FROM financial_categories fc
    LEFT JOIN financial_subcategories fsc ON fc.category_id = fsc.category_id
    LEFT JOIN financial_submodules fsm ON fsc.subcategory_id = fsm.subcategory_id
    LEFT JOIN financial_object_code foc ON fsm.submodule_id = foc.submodule_id
";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - NiceAdmin Bootstrap Template</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">

    <script>
        function updateForm() {
            var type = document.getElementById("type").value;
            document.getElementById("categoryInput").style.display = type === "category" ? "block" : "none";
            document.getElementById("subcategoryInput").style.display = type === "subcategory" ? "block" : "none";
            document.getElementById("submoduleInput").style.display = type === "submodule" ? "block" : "none";
            document.getElementById("objectCodeInput").style.display = type === "object_code" ? "block" : "none";
        }
    </script>
</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <img src="../img/DTI_short.png" alt="">
                <span class="d-none d-lg-block">Region 12</span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->


        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">



                <li class="nav-item dropdown pe-3">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <i class="ri-account-circle-fill fs-2"></i>
                        <span class="d-none d-md-block dropdown-toggle ps-2"></span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>Kevin Anderson</h6>
                            <span>Web Designer</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                                <i class="bi bi-person"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                                <i class="bi bi-gear"></i>
                                <span>Account Settings</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                                <i class="bi bi-question-circle"></i>
                                <span>Need Help?</span>
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

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

<li class="nav-item">
    <a class="navbar-brand ps-3" href="">
        <img src="../img/DTI_w12.png" alt="Logo" style="height: 100px; width: auto; max-width: 100%; ">
    </a>
</li>
<li class="nav-item">
    <a class="nav-link " href="index.html">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-bar-chart"></i><span>Financial Management</span><i
            class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
            <a href="account_title.php">
                <i class="bi bi-circle"></i><span>Account Titles</span>
            </a>
        </li>
        <li>
            <a href="charts-apexcharts.html">
                <i class="bi bi-circle"></i><span>ApexCharts</span>
            </a>
        </li>
        <li>
            <a href="charts-echarts.html">
                <i class="bi bi-circle"></i><span>ECharts</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item">
    <a class="nav-link collapsed" href="reports_copy.php">
        <i class="bi bi-journal-text"></i>
        <span>Reports</span>
    </a>
</li>

<li class="nav-heading">User Management</li>

<li class="nav-item">
    <a class="nav-link collapsed" href="manage_users.php">
        <i class="bi bi-person"></i>
        <span>Manage Users</span>
    </a>
</li><!-- End Profile Page Nav -->



</ul>

    </aside><!-- End Sidebar-->

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Account Title</h1>
        </div><!-- End Page Title -->



        <section class="section dashboard">

            <div class="card">
                <div class="card-body">

                    <h5 class="card-title">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#formModal">
                            Add
                        </button>
                    </h5>

                    <!-- Modal -->
                    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="formModalLabel">Add Financial Data</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="process.php">
                                        <label>Select Type:</label>
                                        <select id="type" name="type" class="form-select" onchange="updateForm()">
                                            <option value="">--Select--</option>
                                            <option value="category">Financial Category</option>
                                            <option value="subcategory">Financial Subcategory</option>
                                            <option value="submodule">Financial Submodule</option>
                                            <option value="object_code">Financial Object Code</option>
                                        </select>

                                        <div id="categoryInput" style="display:none;" class="mt-3">
                                            <label>Category Name:</label>
                                            <input type="text" name="category_name" class="form-control"
                                                autocomplete="off">
                                        </div>

                                        <div id="subcategoryInput" style="display:none;" class="mt-3">
                                            <label>Select Category:</label>
                                            <select name="category_id" class="form-select">
                                                <?php while ($row = $categories->fetch_assoc()) { ?>
                                                    <option value="<?php echo $row['category_id']; ?>">
                                                        <?php echo $row['category_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <label>Subcategory Name:</label>
                                            <input type="text" name="subcategory_name" class="form-control"
                                                autocomplete="off">
                                        </div>

                                        <div id="submoduleInput" style="display:none;" class="mt-3">
                                            <label>Select Subcategory:</label>
                                            <select name="subcategory_id" class="form-select">
                                                <?php while ($row = $subcategories->fetch_assoc()) { ?>
                                                    <option value="<?php echo $row['subcategory_id']; ?>">
                                                        <?php echo $row['subcategory_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <label>Submodule Name:</label>
                                            <input type="text" name="submodule_name" class="form-control"
                                                autocomplete="off">
                                        </div>

                                        <div id="objectCodeInput" style="display:none;" class="mt-3">
                                            <label>Select Submodule:</label>
                                            <select name="submodule_id" class="form-select">
                                                <?php while ($row = $submodules->fetch_assoc()) { ?>
                                                    <option value="<?php echo $row['submodule_id']; ?>">
                                                        <?php echo $row['submodule_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <label>Object Code Name:</label>
                                            <input type="text" name="object_name" class="form-control"
                                                autocomplete="off">
                                            <label>UACS Code:</label>
                                            <input type="text" name="uacs_code" class="form-control" autocomplete="off">
                                        </div>

                                        <div class="modal-footer mt-3">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th colspan="4" style="text-align: center;">Object Code</th>
                                <th>UACS Code</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $currentCategory = $currentSubcategory = $currentSubmodule = "";
                            $rowNumber = 1;

                            while ($row = $result->fetch_assoc()) {
                                // Display category only if it's a new one
                                if ($row['category_name'] !== $currentCategory) {
                                    echo "<tr>";
                                    echo "<td colspan='3'><strong>{$row['category_name']}</strong></td>";
                                    echo "<td></td>";
                                    echo "<td></td>";
                                    echo "<td></td>";
                                    echo "</tr>";
                                    $currentCategory = $row['category_name'];
                                }

                                // Display subcategory only if it's a new one
                                if ($row['subcategory_name'] !== $currentSubcategory) {
                                    echo "<tr>";
                                    echo "<td></td>";
                                    echo "<td colspan='2'>{$row['subcategory_name']}</td>";
                                    echo "<td></td>";
                                    echo "<td></td>";
                                    echo "<td></td>";
                                    echo "</tr>";
                                    $currentSubcategory = $row['subcategory_name'];
                                }

                                // Display submodule only if it's a new one
                                if ($row['submodule_name'] !== $currentSubmodule) {
                                    echo "<tr>";
                                    echo "<td></td>";
                                    echo "<td></td>";
                                    echo "<td colspan='2'>{$row['submodule_name']}</td>";
                                    echo "<td></td>";
                                    echo "<td></td>";
                                    echo "</tr>";
                                    $currentSubmodule = $row['submodule_name'];
                                }

                                // Display object code
                                echo "<tr>";
                                echo "<td></td>";
                                echo "<td></td>";
                                echo "<td></td>";
                                echo "<td>{$row['object_name']}</td>";
                                echo "<td>{$row['uacs_code']}</td>";
                                echo "<td>{$row['status']}</td>";
                                echo "</tr>";

                                $rowNumber++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main><!-- End #main -->


    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>

</body>

</html>