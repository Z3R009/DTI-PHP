<?php
include '../DBConnection.php';

$alert = "";

if (isset($_POST['submit'])) {
    $payee_name = $_POST['payee_name'];
    $bank_acc_no = $_POST['bank_acc_no'];
    $tin_no = $_POST['tin_no'];
    $address = $_POST['address'];
    $nature = $_POST['nature'];
    $contact_no = $_POST['contact_no'];
    $payee_type = $_POST['payee_type'];

    $check_sql = "SELECT COUNT(*) FROM payee WHERE payee_name = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("s", $payee_name);
    $check_stmt->execute();
    $check_stmt->store_result();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();

    if ($count > 0) {
        $alert = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Payee',
                        text: 'Payee name already exists!',
                        confirmButtonColor: '#d33'
                    });
                });
            </script>
        ";
    } else {
        $insert_sql = "INSERT INTO payee (payee_name, bank_acc_no, tin_no, address, nature, contact_no, payee_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($insert_sql);

        if ($stmt === false) {
            $alert = "<script>alert('Error preparing the statement: " . $connection->error . "');</script>";
        } else {
            $stmt->bind_param("sssssss", $payee_name, $bank_acc_no, $tin_no, $address, $nature, $contact_no, $payee_type);
            if ($stmt->execute()) {
                $alert = "
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Payee has been added successfully.',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                window.location.href = 'payee.php';
                            });
                        });
                    </script>
                ";
            } else {
                $alert = "<script>alert('Error: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        }
    }

    $check_stmt->close();
}

// retrieve payee
$select = mysqli_query($connection, "SELECT * FROM payee ORDER BY LOWER(payee_name) ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Book Keeper - Registry of Supplier</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <link href="img/dti_logo.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <style>
        .expandable-row {
            background-color: #f8f9fa;
        }

        .expandable-row .card-body {
            padding: 1rem;
        }

        .expand-row:focus {
            box-shadow: none;
        }

        .expand-row {
            padding: 0.25rem 0.5rem;
        }

        .modal-content {
            border-radius: 0.5rem;
        }

        .modal-header {
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: #0d6efd;
        }

        .form-floating>.form-control:focus,
        .form-floating>.form-control:not(:placeholder-shown) {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .btn-close-white {
            opacity: 0.8;
        }

        .btn-close-white:hover {
            opacity: 1;
        }

        .modal-footer {
            background-color: #f8f9fa;
        }

        .expandable-row .card {
            border: 1px solid #0d6efd;
            border-radius: 0.375rem;
            margin: 0.5rem 0;
            box-shadow: 0 0.125rem 0.25rem rgba(13, 110, 253, 0.1);
        }

        .expandable-row .card-body {
            background-color: #f0f7ff;
            border-radius: 0.375rem;
        }

        .expandable-row .card-body strong {
            color: #0d6efd;
        }

        /* Hide the original search input from the library */
        .datatable-top .datatable-search {
            display: none !important;
        }

        .modal-header {
            background-color: #03045e;
        }

        .modal-title {
            color: #fff;
        }
    </style>

</head>

<body>

    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Registry of Supplier</h1>
        </div>

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title"></h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addUserModal"><i class="bi bi-plus-circle me-1"></i> Add Payee</button>
                    </div>

                    <!-- Add custom search bar -->
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="directTableSearch" class="form-control"
                                placeholder="Search in table..." autocomplete="off">
                        </div>
                    </div>

                    <!-- Style to hide the library's search box -->
                    <style>
                        /* Hide the original search input from the library */
                        .datatable-top .datatable-search {
                            display: none !important;
                        }
                    </style>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header  text-black">
                                    <h5 class="modal-title" id="addUserModalLabel">
                                        <i class="bi bi-person-plus-fill me-2"></i>Add New Payee
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <form method="post" id="addCluster">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" id="payee_name"
                                                        name="payee_name" placeholder="Enter Payee Name" required
                                                        autocomplete="off">
                                                    <label for="payee_name">Payee Name</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" id="bank_acc_no"
                                                        name="bank_acc_no" placeholder="Enter Bank Account No."
                                                        autocomplete="off">
                                                    <label for="bank_acc_no">Bank Account No.</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" id="tin_no" name="tin_no"
                                                        placeholder="Enter TIN/Employee No." autocomplete="off">
                                                    <label for="tin_no">TIN/Employee No.</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" id="address" name="address"
                                                        placeholder="Enter Address" autocomplete="off">
                                                    <label for="address">Address</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <select id="categoryFilter" class="form-select" name="nature">
                                                        <option value="" selected disabled>All Categories</option>
                                                        <option value="Office Supplies">Office Supplies</option>
                                                        <option value="Other Supplies and Materials">Other Supplies and
                                                            Materials</option>
                                                        <option value="Printing and Publication Services">Printing and
                                                            Publication Services</option>
                                                        <option value="Vehicle Rental">Vehicle Rental</option>
                                                        <option value="Food/Catering Services">Food/Catering Services
                                                        </option>
                                                        <option value="Repairs and Maintenance">Repairs and Maintenance
                                                        </option>
                                                        <option value="Other Services">Other Services</option>
                                                        <option value="Venue/Accomodation">Venue/Accomodation</option>
                                                        <option value="Rents">Rents</option>
                                                        <option value="Others">Others</option>
                                                    </select>
                                                    <label for="nature">Category</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <input type="text" class="form-control" id="contact_no"
                                                        name="contact_no" maxlength="13" autocomplete="off"
                                                        placeholder="Enter Contact Number">
                                                    <label for="contact_no">Contact Number</label>
                                                    <span id="addErrorMsg" class="text-danger small"
                                                        style="display: none;">Please enter numbers only</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3">
                                                    <select class="form-select" id="payee_type" name="payee_type"
                                                        required>
                                                        <option value="" selected disabled>Select payee type</option>
                                                        <option value="Internal">Internal</option>
                                                        <option value="External">External (supplier)</option>
                                                    </select>
                                                    <label for="payee_type">Payee Type</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-4">
                                            <button type="button" class="btn btn-light" onclick="clearForm()">
                                                <i class="bi bi-x-circle me-1"></i>Clear
                                            </button>
                                            <button type="submit" id="submit" name="submit" class="btn btn-primary">
                                                <i class="bi bi-save me-1"></i>Save Payee
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table with stripped rows -->
                    <div class="table-responsive" style="height: 400px; overflow-y: auto;">
                        <table class="datatable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Payee Name</th>
                                    <th scope="col">Bank Account No.</th>
                                    <th scope="col">TIN/Employee No.</th>
                                    <th scope="col">Address</th>
                                    <th scope="col" class="d-none expandable-col">Nature of Business</th>
                                    <th scope="col" class="d-none expandable-col">Contact Number</th>
                                    <th scope="col" class="d-none expandable-col">Payee Type</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                    <tr>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary expand-row" type="button"
                                                onclick="toggleExpand(this, 'expand<?php echo $row['payee_id']; ?>')"
                                                aria-expanded="false">
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['payee_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['bank_acc_no']); ?></td>
                                        <td><?php echo htmlspecialchars($row['tin_no']); ?></td>
                                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                                        <td class="d-none expandable-col"><?php echo htmlspecialchars($row['nature']); ?>
                                        </td>
                                        <td class="d-none expandable-col">
                                            <?php echo htmlspecialchars($row['contact_no']); ?>
                                        </td>
                                        <td class="d-none expandable-col">
                                            <?php echo htmlspecialchars($row['payee_type']); ?>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                data-id="<?php echo $row['payee_id']; ?>"
                                                data-payee_name="<?= htmlspecialchars($row['payee_name']); ?>"
                                                data-bank_acc_no="<?= htmlspecialchars($row['bank_acc_no']); ?>"
                                                data-tin_no="<?= htmlspecialchars($row['tin_no']); ?>"
                                                data-address="<?= htmlspecialchars($row['address']); ?>"
                                                data-nature="<?= htmlspecialchars($row['nature']); ?>"
                                                data-contact_no="<?= htmlspecialchars($row['contact_no']); ?>"
                                                data-payee_type="<?= htmlspecialchars($row['payee_type']); ?>">
                                                <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deleteUser(<?php echo $row['payee_id']; ?>)"><i class="bi bi-trash"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Delete"></i></button>
                                        </td>
                                    </tr>
                                    <tr class="expandable-row">
                                        <td colspan="9" class="p-0">
                                            <div class="collapse" id="expand<?php echo $row['payee_id']; ?>">
                                                <div class="card card-body border-custom">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <p class="mb-1"><strong>Category:</strong></p>
                                                            <p><?php echo htmlspecialchars($row['nature']); ?></p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p class="mb-1"><strong>Contact Number:</strong></p>
                                                            <p><?php echo htmlspecialchars($row['contact_no']); ?></p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <p class="mb-1"><strong>Payee Type:</strong></p>
                                                            <p><?php echo htmlspecialchars($row['payee_type']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
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

    </main><!-- End #main -->

    <!-- update modal -->

    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editUserModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Edit Payee
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="post" id="editUserForm" action="update_payee.php">
                        <input type="hidden" id="edit_payee_id" name="payee_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_payee_name" name="payee_name"
                                        required>
                                    <label for="edit_payee_name">Payee Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_bank_acc_no" name="bank_acc_no">
                                    <label for="edit_bank_acc_no">Bank Account No.</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_tin_no" name="tin_no">
                                    <label for="edit_tin_no">TIN/Employee No.</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_address" name="address">
                                    <label for="edit_address">Address</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select id="categoryFilter" class="form-select" name="nature">
                                        <option value="" selected disabled>All Categories</option>
                                        <option value="Office Supplies">Office Supplies</option>
                                        <option value="Other Supplies and Materials">Other Supplies and Materials
                                        </option>
                                        <option value="Printing and Publication Services">Printing and Publication
                                            Services</option>
                                        <option value="Vehicle Rental">Vehicle Rental</option>
                                        <option value="Food/Catering Services">Food/Catering Services</option>
                                        <option value="Repairs and Maintenance">Repairs and Maintenance</option>
                                        <option value="Other Services">Other Services</option>
                                        <option value="Venue/Accomodation">Venue/Accomodation</option>
                                        <option value="Rents">Rents</option>
                                        <option value="Others">Others</option>
                                    </select>
                                    <label for="edit_nature">Category</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_contact_no" name="contact_no"
                                        maxlength="13" autocomplete="off" placeholder="Enter Contact Number">
                                    <label for="edit_contact_no">Contact Number</label>
                                    <span id="editErrorMsg" class="text-danger small" style="display: none;">Please
                                        enter numbers only</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="edit_payee_type" name="payee_type" required>
                                        <option value="" selected disabled>Select payee type</option>
                                        <option value="Internal">Internal</option>
                                        <option value="External">External (supplier)</option>
                                    </select>
                                    <label for="edit_payee_type">Payee Type</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Close
                            </button>
                            <button type="submit" id="update" name="update" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Update Payee
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


    <!-- Direct search implementation -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Clear any existing search script
            window.dataTableSearchInitialized = false;

            function directTableSearch() {
                const searchInput = document.getElementById('directTableSearch');
                if (!searchInput) return;

                searchInput.addEventListener('input', function () {
                    const searchText = this.value.toLowerCase();
                    const tableRows = document.querySelectorAll('.datatable tbody tr');
                    if (!tableRows.length) return;

                    tableRows.forEach(function (row) {
                        if (row.classList.contains('expandable-row')) return;

                        let rowText = row.textContent.toLowerCase();
                        let matchFound = rowText.includes(searchText);

                        row.style.display = matchFound ? '' : 'none';
                        const rowIndex = row.rowIndex;
                        const expandableRow = document.querySelector('.datatable tbody tr.expandable-row:nth-of-type(' + (rowIndex + 1) + ')');
                        if (expandableRow) {
                            expandableRow.style.display = matchFound ? '' : 'none';
                        }
                    });
                });

                console.log('Direct table search activated');
            }
            directTableSearch();

            setTimeout(directTableSearch, 1000);
        });
    </script>

    <!-- Remove any existing scripts related to datatable search -->
    <script>
        // Clean up any existing event listeners when the page loads
        document.addEventListener('DOMContentLoaded', function () {
            // Remove any existing scripts that might be conflicting
            const existingScripts = document.querySelectorAll('script[data-search-script]');
            existingScripts.forEach(function (script) {
                script.remove();
            });
        });
    </script>

    <?php echo $alert; ?>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const editButtons = document.querySelectorAll(".edit-btn");

            editButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const id = this.getAttribute("data-id");
                    const payee_name = this.getAttribute("data-payee_name");
                    const bank_acc_no = this.getAttribute("data-bank_acc_no");
                    const tin_no = this.getAttribute("data-tin_no");
                    const address = this.getAttribute("data-address");
                    const nature = this.getAttribute("data-nature");
                    const contact_no = this.getAttribute("data-contact_no");
                    const payee_type = this.getAttribute("data-payee_type");

                    document.getElementById("edit_payee_id").value = id;
                    document.getElementById("edit_payee_name").value = payee_name;
                    document.getElementById("edit_bank_acc_no").value = bank_acc_no;
                    document.getElementById("edit_tin_no").value = tin_no;
                    document.getElementById("edit_address").value = address;

                    // Set the category/nature select value
                    const natureSelect = document.querySelector("#editUserModal select[name='nature']");
                    if (natureSelect) {
                        natureSelect.value = nature;
                    }

                    document.getElementById("edit_contact_no").value = contact_no;

                    // Set the payee type select value
                    const payeeTypeSelect = document.getElementById("edit_payee_type");
                    if (payeeTypeSelect) {
                        payeeTypeSelect.value = payee_type;
                    }
                });
            });

            // Handle expand/collapse buttons
            const expandButtons = document.querySelectorAll('.expand-row');
            expandButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const icon = this.querySelector('i');
                    const targetId = this.getAttribute('data-bs-target');
                    const targetElement = document.querySelector(targetId);

                    // Toggle the collapse manually
                    if (targetElement.classList.contains('show')) {
                        targetElement.classList.remove('show');
                        targetElement.style.display = 'none';
                        icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
                    } else {
                        targetElement.classList.add('show');
                        targetElement.style.display = 'block';
                        icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
                    }
                });
            });
        });

        function toggleExpand(button, targetId) {
            const icon = button.querySelector('i');
            const targetElement = document.getElementById(targetId);

            if (targetElement.style.display === 'block') {
                targetElement.style.display = 'none';
                icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
            } else {
                targetElement.style.display = 'block';
                icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
            }
        }
    </script>

    <!-- delete confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteUser(userID) {
            Swal.fire({
                title: 'Delete Payee?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_payee.php?payee_id=' + userID + '&confirm=yes';
                }
            })
        }
    </script>


    <script>
        function clearForm() {
            document.getElementById('addCluster').reset();
        }

        const contact_noInput = document.getElementById('contact_no');
        const addErrorMessage = document.getElementById('addErrorMsg');

        contact_noInput.addEventListener('input', function (e) {
            let input = e.target.value;
            let numericInput = input.replace(/\D/g, '');
            if (numericInput.length > 11) {
                numericInput = numericInput.slice(0, 11);
            }
            e.target.value = numericInput;

            if (input !== numericInput) {
                addErrorMessage.style.display = 'block';
            } else {
                addErrorMessage.style.display = 'none';
            }
        });

        const editContact_noInput = document.getElementById('edit_contact_no');
        const editErrorMessage = document.getElementById('editErrorMsg');

        editContact_noInput.addEventListener('input', function (e) {
            let input = e.target.value;
            let numericInput = input.replace(/\D/g, '');
            if (numericInput.length > 11) {
                numericInput = numericInput.slice(0, 11);
            }
            e.target.value = numericInput;

            if (input !== numericInput) {
                editErrorMessage.style.display = 'block';
            } else {
                editErrorMessage.style.display = 'none';
            }
        });

        const payee_type = this.getAttribute("data-payee_type");
        document.getElementById("edit_payee_type").value = payee_type;
    </script>

    <!-- alert for delete -->
    <?php if (isset($_GET['deleted'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: '<?php echo $_GET["deleted"] === "success" ? "success" : "error"; ?>',
                title: '<?php echo $_GET["deleted"] === "success" ? "Deleted!" : "Error!"; ?>',
                text: '<?php echo $_GET["deleted"] === "success" ? "Payee has been deleted successfully." : "There was a problem deleting the payee."; ?>',
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php endif; ?>

    <!-- alert for update -->
    <?php if (isset($_GET['updated'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: '<?php echo $_GET["updated"] === "success" ? "success" : "error"; ?>',
                title: '<?php echo $_GET["updated"] === "success" ? "Updated!" : "Error!"; ?>',
                text: '<?php echo $_GET["updated"] === "success" ? "Payee has been updated successfully." : "There was a problem updating the payee."; ?>',
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php endif; ?>



</body>

</html>