<?php
include '../DBConnection.php';
if (isset($_POST['submit'])) {
    $account_title = $_POST['account_title'];
    $account_code = $_POST['account_code'];

    // Check for duplicates
    // $check_duplicate = mysqli_query($connection, "SELECT * FROM account_title WHERE account_title = '$account_title' OR account_code = '$account_code'");

    // if (mysqli_num_rows($check_duplicate) > 0) {
    //     echo "<script>
    //         alert('Error: Account Title or Account Code already exists!');
    //         window.location.href='account_title.php';
    //     </script>";
    // } else {
    $sql = "INSERT INTO account_title (account_title, account_code) VALUES (?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $account_title, $account_code);

    if ($stmt->execute()) {
        header('Location: account_title.php');
    } else {
        echo "Error: " . $stmt->error;
    }

}
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if (!empty($search)) {
    $search = mysqli_real_escape_string($connection, $search);
    $where = "WHERE account_title LIKE '%$search%' OR account_code LIKE '%$search%'";
}

$select = mysqli_query($connection, "SELECT * FROM account_title $where ORDER BY CAST(account_code AS UNSIGNED)");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>BookKeeper - Account Title Management</title>
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

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet"
        type="text/css">
    <link rel="stylesheet" href="css/UACS.css">
    <link rel="stylesheet" href="css/table.css">


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Account Title Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Account Title</li>
                </ol>
            </nav>
        </div>

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Account Titles List</h5>
                        <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Account Title
                        </button>
                    </div>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold" id="addUserModalLabel">Add New Account Title</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-0">
                                    <form method="post" id="addUserForm" class="needs-validation" novalidate>
                                        <div class="mb-3">
                                            <label for="account_title" class="form-label">Account Title</label>
                                            <input type="text" class="form-control" id="account_title"
                                                name="account_title" placeholder="Enter Account Title" required
                                                autocomplete="off">
                                            <div class="invalid-feedback">Please enter an account title.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="account_code" class="form-label">Account Code</label>
                                            <input type="number" class="form-control" id="account_code"
                                                name="account_code" placeholder="Enter Account Code" required
                                                autocomplete="off">
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
                        <table class="datatable">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Account Title</th>
                                    <th scope="col">Account Code</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['account_code']); ?></td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-btn"
                                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                                    data-id="<?php echo $row['account_id']; ?>"
                                                    data-account_title="<?php echo htmlspecialchars($row['account_title']); ?>"
                                                    data-account_code="<?php echo htmlspecialchars($row['account_code']); ?>">
                                                    <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteUser(<?php echo $row['account_id']; ?>)">
                                                    <i class="bi bi-trash" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Delete"></i>
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
                    <h5 class="modal-title fw-bold" id="editModalLabel">Edit Account Title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body mb-4">
                    <form method="post" id="editUserForm" action="update_account.php" class="needs-validation"
                        novalidate>
                        <input type="hidden" id="edit_account_id" name="account_id">
                        <div class="mb-4">
                            <label for="edit_account_title" class="form-label">Account Title</label>
                            <input type="text" class="form-control" id="edit_account_title" name="account_title"
                                required autocomplete="off">
                            <div class="invalid-feedback">Please enter an account title.</div>
                        </div>
                        <div class="mb-4">
                            <label for="edit_account_code" class="form-label">Account Code</label>
                            <input type="number" class="form-control" id="edit_account_code" name="account_code"
                                required autocomplete="off">
                            <div class="invalid-feedback">Please enter an account code.</div>
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
        document.addEventListener("DOMContentLoaded", function () {
            try {
                const datatable = new simpleDatatables.DataTable(".datatable", {
                    searchable: true,
                    fixedHeight: true,
                    perPage: 10,
                    sortable: true,
                    search: {
                        return: true,
                        smart: true
                    },
                    labels: {
                        placeholder: "Search...",
                        perPage: "{select} entries per page",
                        noRows: "No entries found",
                        info: "Showing {start} to {end} of {rows} entries",
                    }
                });
                console.log("Datatable initialized successfully");
                const searchInput = document.querySelector('.datatable-search');
                if (searchInput) {
                    searchInput.addEventListener('input', function (e) {
                        console.log("Search input:", e.target.value);
                    });
                }
            } catch (error) {
                console.error("Error initializing datatable:", error);
            }
        });
    </script>

    <script>

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
                    const account_title = this.getAttribute("data-account_title");
                    const account_code = this.getAttribute("data-account_code");

                    document.getElementById("edit_account_id").value = id;
                    document.getElementById("edit_account_title").value = account_title;
                    document.getElementById("edit_account_code").value = account_code;
                });
            });
        });
    </script>

    <!-- Initialize tooltips -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <!-- Form validation and delete confirmation -->
    <script>

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

        function deleteUser(userID) {
            Swal.fire({
                title: 'Delete Account Title?',
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
                    window.location.href = 'delete_account.php?account_id=' + userID + '&confirm=yes';
                }
            })
        }
    </script>

</body>

</html>