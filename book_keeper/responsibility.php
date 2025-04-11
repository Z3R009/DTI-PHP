<?php

include '../DBConnection.php';


if (isset($_POST['submit'])) {
    $code = $_POST['code'];
    $parent_code = $_POST['parent_code'];
    $type = $_POST['type'];
    $acronym = $_POST['acronym'];
    $description = $_POST['description'];

    $sql = "INSERT INTO responsibility_center (code, parent_code, type, acronym, description) VALUES (?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssss", $code, $parent_code, $type, $acronym, $description);

    if ($stmt->execute()) {
        header('Location: responsibility.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

// retrieve cluster

$select = mysqli_query($connection, "SELECT * FROM responsibility_center");

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

    <?php include "Includes/header.php";?>
    <?php include "Includes/sidebar.php";?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Responsibility Center Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active"> Responsibility Center Management</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Responsibility Centers List</h5>
                        <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Responsibility Center
                        </button>
                    </div>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0 pb-0 mb-3">
                                    <h5 class="modal-title fw-bold" id="addUserModalLabel">Add New Account Title</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-0">
                                    <form method="post" id="addCluster" class="needs-validation" novalidate>
                                        <div class="mb-3">
                                            <label for="code" class="form-label">Code</label>
                                            <input type="text" class="form-control " id="code" name="code" 
                                                placeholder="Enter Code" required autocomplete="off">
                                            <div class="invalid-feedback">Please enter a code.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="parent_code" class="form-label">Parent Code</label>
                                            <input type="text" class="form-control " id="parent_code" 
                                                name="parent_code" placeholder="Enter Parent Code" required autocomplete="off">
                                            <div class="invalid-feedback">Please enter a parent code.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Type</label>
                                            <input type="text" class="form-control" id="type" name="type" 
                                                placeholder="Enter Type" required autocomplete="off">
                                            <div class="invalid-feedback">Please enter a type.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="acronym" class="form-label">Acronym</label>
                                            <input type="text" class="form-control " id="acronym" name="acronym" 
                                                placeholder="Enter Acronym" required autocomplete="off">
                                            <div class="invalid-feedback">Please enter an acronym.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <input type="text" class="form-control" id="description" 
                                                name="description" placeholder="Enter Description" required autocomplete="off">
                                                <div class="invalid-feedback">Please enter an account code.</div>
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
                        <table class="table table-hover datatable">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">Parent Code</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Acronym</th>
                                    <th scope="col">Description</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['parent_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['acronym']); ?></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                                    data-id="<?php echo $row['rc_id']; ?>"
                                                    data-code="<?php echo htmlspecialchars($row['code']); ?>"
                                                    data-parent_code="<?php echo htmlspecialchars($row['parent_code']); ?>"
                                                    data-type="<?php echo htmlspecialchars($row['type']); ?>"
                                                    data-acronym="<?php echo htmlspecialchars($row['acronym']); ?>"
                                                    data-description="<?php echo htmlspecialchars($row['description']); ?>">
                                                    <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteUser(<?php echo $row['rc_id']; ?>)">
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
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="editUserModalLabel">Edit Responsibility Center</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0">
                    <form method="post" id="editUserForm" action="update_responsibility.php" class="needs-validation" novalidate>
                        <input type="hidden" id="edit_rc_id" name="rc_id">
                        <div class="mb-3">
                            <label for="edit_code" class="form-label">Code</label>
                            <input type="text" class="form-control " id="edit_code" name="code" required>
                            <div class="invalid-feedback">Please enter a code.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_parent_code" class="form-label">Parent Code</label>
                            <input type="text" class="form-control" id="edit_parent_code" name="parent_code" required>
                            <div class="invalid-feedback">Please enter a parent code.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_type" class="form-label">Type</label>
                            <input type="text" class="form-control" id="edit_type" name="type" required>
                            <div class="invalid-feedback">Please enter a type.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_acronym" class="form-label">Acronym</label>
                            <input type="text" class="form-control" id="edit_acronym" name="acronym" required>
                            <div class="invalid-feedback">Please enter an acronym.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="edit_description" name="description" required>
                            <div class="invalid-feedback">Please enter a description.</div>
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
                    window.location.href = 'delete_responsibility.php?id=' + userID;
                }
            })
        }
    </script>

</body>

</html>