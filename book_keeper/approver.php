<?php
include '../DBConnection.php';

$alert = "";

if (isset($_POST['submit'])) {
    $approver_name = $_POST['approver_name'];
    $designation = $_POST['designation'];

    $check_sql = "SELECT COUNT(*) FROM approver WHERE approver_name = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("s", $approver_name);
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
                        title: 'Duplicate Approver',
                        text: 'Approver already exists!',
                        confirmButtonColor: '#d33'
                    });
                });
            </script>
        ";
    } else {
        $insert_sql = "INSERT INTO approver (approver_name, designation) VALUES (?, ?)";
        $stmt = $connection->prepare($insert_sql);

        if ($stmt === false) {
            $alert = "<script>alert('Error preparing the statement: " . $connection->error . "');</script>";
        } else {
            $stmt->bind_param("ss", $approver_name, $designation);
            if ($stmt->execute()) {
                $alert = "
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Approver has been added successfully.',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                window.location.href = 'approver.php';
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
$select = mysqli_query($connection, "SELECT * FROM approver");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Book Keeper - Approver</title>
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
</head>

<body>

    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Approver</h1>
        </div>


        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title"></h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addUserModal"><i class="bi bi-plus-circle me-1"></i> Add Approver</button>
                    </div>
                    <p></p>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addUserModalLabel">Add Approver
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" id="addUserForm">
                                        <div class="mb-3">
                                            <label for="approver_name" class="form-label">Approver Name</label>
                                            <input type="text" class="form-control" id="approver_name"
                                                name="approver_name" placeholder="Enter Approver Name" required
                                                autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="designation" class="form-label">Designation</label>
                                            <input type="text" class="form-control" id="designation" name="designation"
                                                placeholder="Enter Designation" required autocomplete="off">
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
                    <table class="datatable">
                        <thead>
                            <tr>
                                <th>Approver Name</th>
                                <th>Designation</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['approver_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-btn"
                                            data-bs-toggle="modal" data-bs-target="#editUserModal"
                                            data-id="<?php echo $row['approver_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['approver_name']); ?>"
                                            data-designation="<?php echo htmlspecialchars($row['designation']); ?>"> <i
                                                class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Edit"></i>
                                        </button>


                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteUser(<?php echo $row['approver_id']; ?>)"><i class="bi bi-trash"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
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


    <!-- update modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit Approver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm" action="update_approver.php">
                        <input type="hidden" id="edit_approver_id" name="approver_id">
                        <div class="mb-3">
                            <label for="edit_approver_name" class="form-label">Approver Name</label>
                            <input type="text" class="form-control" id="edit_approver_name" name="approver_name"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_designation" class="form-label">Designation</label>
                            <input type="text" class="form-control" id="edit_designation" name="designation" required>
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

    <!-- Vendor JS Files -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>
    <script src="../NiceAdmin/assets/js/main.js"></script>


    <?php echo $alert; ?>

    <!-- Template Main JS File -->
    <script src="../NiceAdmin/assets/js/main.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const editButtons = document.querySelectorAll(".edit-btn");

            editButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const id = this.getAttribute("data-id");
                    const name = this.getAttribute("data-name");
                    const designation = this.getAttribute("data-designation");

                    document.getElementById("edit_approver_id").value = id;
                    document.getElementById("edit_approver_name").value = name;
                    document.getElementById("edit_designation").value = designation;
                });
            });
        });
    </script>

    <!-- delete confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteUser(userID) {
            Swal.fire({
                title: 'Delete Approver?',
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
                    window.location.href = 'delete_approver.php?approver_id=' + userID + '&confirm=yes';
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
                text: '<?php echo $_GET["deleted"] === "success" ? "Approver has been deleted successfully." : "There was a problem deleting the payee."; ?>',
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
                text: '<?php echo $_GET["updated"] === "success" ? "Approver has been updated successfully." : "There was a problem updating the payee."; ?>',
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php endif; ?>

    <script>

        function clearForm() {
            document.getElementById('addUserForm').reset();
        }
    </script>

</body>

</html>