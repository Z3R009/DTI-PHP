<?php

include '../DBConnection.php';

$alert = "";

if (isset($_POST['submit'])) {
    $services_name = $_POST['services_name'];
    $code = $_POST['code'];
    $oopap_id = $_POST['oopap_id'];


    $check_sql = "SELECT COUNT(*) FROM services WHERE services_name = ? AND oopap_id = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("si", $services_name, $oopap_id);
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
                        title: 'Duplicate Services',
                        text: 'Services already exists!',
                        confirmButtonColor: '#d33'
                    });
                });
            </script>
        ";
    } else {
        $insert_sql = "INSERT INTO services (services_name, code, oopap_id) VALUES (?, ?, ?)";
        $stmt = $connection->prepare($insert_sql);

        if ($stmt === false) {
            $alert = "<script>alert('Error preparing the statement: " . $connection->error . "');</script>";
        } else {
            $stmt->bind_param("sss", $services_name, $code, $oopap_id);
            if ($stmt->execute()) {
                $alert = "
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Services has been added successfully.',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                window.location.href = 'services.php';
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

$select = mysqli_query(
    $connection,

    "SELECT services.*, oopap.oopap_name FROM services 
            LEFT JOIN oopap ON services.oopap_id = oopap.oopap_id"
);

$sql_oopap = "SELECT oopap_id, oopap_name FROM oopap";
$result_oopap = $connection->query($sql_oopap);

// Store results in an array
$oopap_options = [];
while ($row = $result_oopap->fetch_assoc()) {
    $oopap_options[] = $row;
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Book Keeper - Service</title>
    <meta content="" name="code">
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

</head>

<body>

    <!-- ======= Header ======= -->
    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Service</h1>
        </div>

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title"></h5>
                        <button type="button " class="btn btn-primary " data-bs-toggle="modal"
                            data-bs-target="#addUserModal">Add Services</button>
                    </div>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addUserModalLabel">Add Service
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" id="addCluster">

                                        <div class="mb-3">
                                            <label for="services_name" class="form-label">Service
                                                Name</label>
                                            <input type="text" class="form-control" id="services_name"
                                                name="services_name" placeholder="Enter Service Name" required
                                                autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="code" class="form-label">Code</label>
                                            <input type="code" class="form-control" id="code" name="code"
                                                placeholder="Enter Code" required autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">OO/PAP</label>
                                            <select class="form-control" name="oopap_id">
                                                <option selected disabled>Select OO/PAP</option>
                                                <?php
                                                foreach ($oopap_options as $row) {
                                                    echo "<option value='" . htmlspecialchars($row['oopap_id']) . "'>" . htmlspecialchars($row['oopap_name']) . "</option>";
                                                }
                                                ?>

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
                    <div class="table-responsive">
                        <table class="datatable">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">OO/PAP</th>
                                    <th scope="col">Service Name</th>
                                    <th scope="col">Code</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['oopap_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['services_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['code']); ?></td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-btn"
                                                    data-bs-toggle="modal" data-bs-target="#editModal"
                                                    data-id="<?php echo $row['services_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['services_name']); ?>"
                                                    data-code="<?php echo htmlspecialchars($row['code']); ?>"
                                                    data-oopap_id="<?php echo htmlspecialchars($row['oopap_id']); ?>">
                                                    <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Edit"></i>
                                                </button>

                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteUser(<?php echo $row['services_id']; ?>)"><i
                                                        class="bi bi-trash" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Delete"></i></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>

                        </table>
                    </div>
                </div>

        </section>

    </main><!-- End #main -->

    <!-- update modal -->

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm" action="update_services.php">
                        <input type="hidden" id="edit_services_id" name="services_id">
                        <div class="mb-3">
                            <label for="edit_services_name" class="form-label">Service Name</label>
                            <input type="text" class="form-control" id="edit_services_name" name="services_name"
                                autocomplete="off" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_code" class="form-label">Code</label>
                            <input type="text" class="form-control" id="edit_code" name="code" required
                                autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">OO/PAP</label>
                            <select class="form-control" name="oopap_id" id="edit_oopap_id">
                                <option selected disabled>Select OO/PAP</option>
                                <?php
                                foreach ($oopap_options as $row) {
                                    echo "<option value='" . htmlspecialchars($row['oopap_id']) . "'>" . htmlspecialchars($row['oopap_name']) . "</option>";
                                }
                                ?>

                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="update" name="update" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>



    <?php echo $alert; ?>


    <!-- clear -->
    <script>
        // Function to clear form
        function clearForm() {
            document.getElementById('addUserForm').reset();
        }
    </script>

    <!-- show update -->

    <script>
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const code = this.dataset.code;
                const oopapId = this.dataset.oopap_id;

                document.getElementById('edit_services_id').value = id;
                document.getElementById('edit_services_name').value = name;
                document.getElementById('edit_code').value = code;

                // Set selected option
                const select = document.getElementById('edit_oopap_id');
                if (select) {
                    select.value = oopapId;
                }
            });
        });

    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

    </script>

    <!-- delete confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteUser(userID) {
            Swal.fire({
                title: 'Delete Services?',
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
                    window.location.href = 'delete_services.php?services_id=' + userID + '&confirm=yes';
                }
            })
        }
    </script>

    <!-- alert for delete -->
    <?php if (isset($_GET['deleted'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: '<?php echo $_GET["deleted"] === "success" ? "success" : "error"; ?>',
                title: '<?php echo $_GET["deleted"] === "success" ? "Deleted!" : "Error!"; ?>',
                text: '<?php echo $_GET["deleted"] === "success" ? "Services has been deleted successfully." : "There was a problem deleting the payee."; ?>',
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
                text: '<?php echo $_GET["updated"] === "success" ? "Services has been updated successfully." : "There was a problem updating the payee."; ?>',
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php endif; ?>


</body>

</html>