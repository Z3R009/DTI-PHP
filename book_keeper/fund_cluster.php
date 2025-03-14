<?php

include '../DBConnection.php';


if (isset($_POST['submit'])) {
    $fund_cluster_name = $_POST['fund_cluster_name'];
    $uacs_code = $_POST['uacs_code'];
    $status = $_POST['status'];


    $sql = "INSERT INTO fund_cluster (fund_cluster_name, uacs_code, status) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sss", $fund_cluster_name, $uacs_code, $status);

    if ($stmt->execute()) {
        header('Location: fund_cluster.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

// retrieve cluster

$select = mysqli_query($connection, "SELECT * FROM fund_cluster ");

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

    <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

    <!-- ======= Header ======= -->
    <?php include "Includes/header.php";?>
    <?php include "Includes/sidebar.php";?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Fund Cluster</h1>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">Add Fund Clusters</button>
                    </h5>
                    <p></p>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addUserModalLabel">Add Fund Cluster
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" id="addCluster">

                                        <div class="mb-3">
                                            <label for="fund_cluster_name" class="form-label">Fund Cluster
                                                Name</label>
                                            <input type="text" class="form-control" id="fund_cluster_name"
                                                name="fund_cluster_name" placeholder="Enter Fund Cluster Name" required
                                                autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="uacs_code" class="form-label">UACS Code</label>
                                            <input type="uacs_code" class="form-control" id="uacs_code" name="uacs_code"
                                                placeholder="Enter UACS Code" required autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option selected disabled>Select Status</option>
                                                <option value="Active">Active</option>
                                                <option value="Inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <!-- <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button> -->
                                            <button type="button" class="btn btn-secondary"
                                                onclick="clearForm()">Clear</button>
                                            <button type="submit" id="submit" name="submit"
                                                class="btn btn-primary">Save</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Table with stripped rows -->
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Fund Cluster Name</th>
                                <th>UACS Code</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['fund_cluster_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['uacs_code']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary"
                                            onclick="editUser(<?php echo $row['fund_cluster_name']; ?>, '<?php echo $row['uacs_code']; ?>', '<?php echo $row['status']; ?>')">
                                            <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Edit"></i>
                                        </button>

                                        <button type="button" class="btn btn-danger"
                                            onclick="deleteUser(<?php echo $row['fund_cluster_id']; ?>)"><i
                                                class="bi bi-trash" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Delete"></i></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
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

    <!-- clear -->
    <script>
        // Function to clear form
        function clearForm() {
            document.getElementById('addUserForm').reset();
        }
    </script>

    <!-- delete -->
    <script>
        function deleteUser(fundClusterID) {
            if (confirm("Are you sure you want to delete this Fund Cluster?")) {
                window.location.href = 'delete_fund_cluster.php?fund_cluster_id=' + fundClusterID + '&confirm=yes';
            }
        }
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

    </script>

</body>

</html>