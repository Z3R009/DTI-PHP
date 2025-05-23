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

    // Check for duplicate payee name
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
                                window.location.href = 'add_payee.php';
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

// Retrieve existing payees
$select = mysqli_query($connection, "SELECT * FROM payee ORDER BY payee_name ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Add Payee - DTI PHP</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../book_keeper/img/dti_logo.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="css/table.css">
    <style>
          .datatable-top .datatable-search {
                            display: none !important;
                        }
    </style>
</head>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<body>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Payee</h1>
           
        </div>

         <section class="section dashboard">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <!-- <div>
                                    <h5 class="card-title fs-4 text-primary mb-1">Payee Management</h5>
                                    <p class="text-muted">Add and manage payees in the system.</p>
                                </div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPayeeModal">
                                    <i class="bi bi-plus-circle me-1"></i> Add New Payee
                                </button> -->
                            </div>
                             <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="directTableSearch" class="form-control"
                                placeholder="Search in table...">
                        </div>
                    </div>

                            <!-- Add Payee Modal -->
                            <div class="modal fade" id="addPayeeModal" tabindex="-1" aria-labelledby="addPayeeModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="addPayeeModalLabel">
                                                <i class="bi bi-person-plus me-2"></i>Add New Payee
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <form method="post" id="addPayeeForm">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" id="payee_name" name="payee_name" placeholder="Enter Payee Name" required autocomplete="off">
                                                            <label for="payee_name">Payee Name</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" id="bank_acc_no" name="bank_acc_no" placeholder="Enter Bank Account No." autocomplete="off">
                                                            <label for="bank_acc_no">Bank Account No.</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" id="tin_no" name="tin_no" placeholder="Enter TIN/Employee No." autocomplete="off">
                                                            <label for="tin_no">TIN/Employee No.</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" id="address" name="address" placeholder="Enter Address" autocomplete="off">
                                                            <label for="address">Address</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" id="nature" name="nature" placeholder="Enter Nature of Business" autocomplete="off">
                                                            <label for="nature">Nature of Business</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" id="contact_no" name="contact_no" maxlength="13" autocomplete="off" placeholder="Enter Contact Number">
                                                            <label for="contact_no">Contact Number</label>
                                                            <span id="addErrorMsg" class="text-danger small" style="display: none;">Please enter numbers only</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-floating mb-3">
                                                            <select class="form-select" id="payee_type" name="payee_type" required>
                                                                <option value="">Select payee type</option>
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

                            <!-- Payees Table -->
                          <div class="table-responsive">
                        <table class="datatable">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Payee Name</th>
                                    <th scope="col">Bank Account No.</th>
                                    <th scope="col">TIN/Employee No.</th>
                                    <th scope="col">Address</th>
                                    <th scope="col" class="d-none expandable-col">Nature of Business</th>
                                    <th scope="col" class="d-none expandable-col">Contact Number</th>
                                    <th scope="col" class="d-none expandable-col">Payee Type</th>
                                    <!-- <th scope="col">Actions</th> -->
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
                                        <!-- <td class="text-end">
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
                                            </button> -->
                                            <!-- <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deleteUser(<?php echo $row['payee_id']; ?>)"><i class="bi bi-trash"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Delete"></i></button> 
                                        </td> -->
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
                </div>
            </div>
        </section>
    </main>

    <!-- Edit Payee Modal -->
    <div class="modal fade" id="editPayeeModal" tabindex="-1" aria-labelledby="editPayeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editPayeeModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>Edit Payee
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="post" id="editPayeeForm" action="update_payee.php">
                        <input type="hidden" id="edit_payee_id" name="payee_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_payee_name" name="payee_name" required>
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
                                    <input type="text" class="form-control" id="edit_nature" name="nature">
                                    <label for="edit_nature">Nature of Business</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="edit_contact_no" name="contact_no" maxlength="13" autocomplete="off">
                                    <label for="edit_contact_no">Contact Number</label>
                                    <span id="editErrorMsg" class="text-danger small" style="display: none;">Please enter numbers only</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="edit_payee_type" name="payee_type" required>
                                        <option value="">Select payee type</option>
                                        <option value="Internal">Internal</option>
                                        <option value="External">External (supplier)</option>
                                    </select>
                                    <label for="edit_payee_type">Payee Type</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                            <button type="submit" name="update" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Update Payee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Vendor JS Files -->

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Initialize DataTable
        document.addEventListener('DOMContentLoaded', function() {
            new simpleDatatables.DataTable(".datatable", {
                searchable: true,
                fixedHeight: true,
                perPage: 10
            });
        });

        // Clear form function
        function clearForm() {
            document.getElementById('addPayeeForm').reset();
        }

        // Contact number validation for add form
        const contactNoInput = document.getElementById('contact_no');
        const errorMessage = document.getElementById('addErrorMsg');

        if (contactNoInput) {
            contactNoInput.addEventListener('input', function(e) {
                let input = e.target.value;
                let numericInput = input.replace(/\D/g, '');
                if (numericInput.length > 11) {
                    numericInput = numericInput.slice(0, 11);
                }
                e.target.value = numericInput;

                if (input !== numericInput) {
                    errorMessage.style.display = 'block';
                } else {
                    errorMessage.style.display = 'none';
                }
            });
        }

        // Edit button click handler
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-btn');
            const editModal = new bootstrap.Modal(document.getElementById('editPayeeModal'));

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const payee_name = this.getAttribute('data-payee_name');
                    const bank_acc_no = this.getAttribute('data-bank_acc_no');
                    const tin_no = this.getAttribute('data-tin_no');
                    const address = this.getAttribute('data-address');
                    const nature = this.getAttribute('data-nature');
                    const contact_no = this.getAttribute('data-contact_no');
                    const payee_type = this.getAttribute('data-payee_type');

                    document.getElementById('edit_payee_id').value = id;
                    document.getElementById('edit_payee_name').value = payee_name;
                    document.getElementById('edit_bank_acc_no').value = bank_acc_no;
                    document.getElementById('edit_tin_no').value = tin_no;
                    document.getElementById('edit_address').value = address;
                    document.getElementById('edit_nature').value = nature;
                    document.getElementById('edit_contact_no').value = contact_no;
                    document.getElementById('edit_payee_type').value = payee_type;

                    editModal.show();
                });
            });
        });

        // Delete button click handler
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const deleteUrl = this.getAttribute('href');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = deleteUrl;
                        }
                    });
                });
            });
        });

        // Contact number validation for edit form
        const editContactNoInput = document.getElementById('edit_contact_no');
        const editErrorMessage = document.getElementById('editErrorMsg');

        if (editContactNoInput) {
            editContactNoInput.addEventListener('input', function(e) {
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
        }
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

    <?php echo $alert; ?>
</body>
</html> 