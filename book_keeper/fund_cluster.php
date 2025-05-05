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


$select = mysqli_query($connection, "SELECT * FROM fund_cluster ");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Bookkepper - Fund Cluster Management</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <link href="img/dti_logo.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"  rel="stylesheet">

    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="css/UACS.css">
    <link rel="stylesheet" href="css/table.css">

</head>

<body>

    <!-- ======= Header ======= -->
    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Fund Cluster Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Fund Cluster</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Fund Clusters List</h5>
                        <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Fund Cluster
                        </button>
                    </div>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title" id="addUserModalLabel">Add New Fund Cluster</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-0">
                                    <form method="post" id="addCluster" class="needs-validation" novalidate>
                                        <div class="mb-3">
                                            <label for="fund_cluster_name" class="form-label">Fund Cluster Name</label>
                                            <input type="text" class="form-control" id="fund_cluster_name" 
                                                name="fund_cluster_name" placeholder="Enter Fund Cluster Name" required autocomplete="off">
                                            <div class="invalid-feedback">Please enter a fund cluster name.</div>
                                        </div>
                                        <div class="mb-4">
                                            <label for="uacs_code" class="form-label">UACS Code</label>
                                            <input type="text" class="form-control" id="uacs_code" 
                                                name="uacs_code" placeholder="Enter UACS Code" required autocomplete="off">
                                            <div class="invalid-feedback">Please enter a UACS code.</div>
                                        </div>
                                        <div class="mb-4">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="" selected disabled>Select Status</option>
                                                <option value="Active">Active</option>
                                                <option value="Inactive">Inactive</option>
                                            </select>
                                            <div class="invalid-feedback">Please select a status.</div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light" onclick="clearForm()">
                                                <i class="bi bi-x-circle me-1"></i> Clear
                                            </button>
                                            <button type="submit" id="submit" name="submit" class="btn btn-primary">
                                                <i class="bi bi-check-circle me-1"></i> Save
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table with stripped rows -->
                    <div class="table-responsive">
                        <table class="datatable">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Fund Cluster Name</th>
                                    <th scope="col">UACS Code</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['fund_cluster_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['uacs_code']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] === 'Active' ? 'success' : 'danger'; ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#editModal" 
                                                    data-id="<?php echo $row['fund_cluster_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['fund_cluster_name']); ?>"
                                                    data-uacs="<?php echo htmlspecialchars($row['uacs_code']); ?>"
                                                    data-status="<?php echo htmlspecialchars($row['status']); ?>">
                                                    <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteUser(<?php echo $row['fund_cluster_id']; ?>)">
                                                    <i class="bi bi-trash" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Fund Cluster</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm" action="update_fund_cluster.php" class="needs-validation" novalidate>
                        <input type="hidden" id="edit_fund_cluster_id" name="fund_cluster_id">
                        <div class="mb-4">
                            <label for="edit_fund_cluster_name" class="form-label">Fund Cluster Name</label>
                            <input type="text" class="form-control" id="edit_fund_cluster_name" 
                                name="fund_cluster_name" required>
                            <div class="invalid-feedback">Please enter a fund cluster name.</div>
                        </div>
                        <div class="mb-4">
                            <label for="edit_uacs_code" class="form-label">UACS Code</label>
                            <input type="text" class="form-control " id="edit_uacs_code" 
                                name="uacs_code" required>
                            <div class="invalid-feedback">Please enter a UACS code.</div>
                        </div>
                        <div class="mb-4">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="" selected disabled>Select Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i> Close
                            </button>
                            <button type="submit" id="update" name="update" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

    <!-- show update -->

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const editButtons = document.querySelectorAll(".edit-btn");

            editButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const id = this.getAttribute("data-id");
                    const name = this.getAttribute("data-name");
                    const uacs = this.getAttribute("data-uacs");
                    const status = this.getAttribute("data-status");

                    document.getElementById("edit_fund_cluster_id").value = id;
                    document.getElementById("edit_fund_cluster_name").value = name;
                    document.getElementById("edit_uacs_code").value = uacs;
                    document.getElementById("edit_status").value = status;
                });
            });
        });
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

    <!-- Add form validation script -->
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Enhanced delete confirmation
        function deleteUser(userID) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_fund_cluster.php?id=' + userID;
                }
            })
        }
    </script>

</body>

</html>