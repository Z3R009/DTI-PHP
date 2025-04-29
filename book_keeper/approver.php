<?php
include '../DBConnection.php';
if (isset($_POST['submit'])) {
    $approver_name = $_POST['approver_name'];
    $designation = $_POST['designation'];
    $sub_title = $_POST['sub_title'];



    $sql = "INSERT INTO approver (approver_name, designation, sub_title) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sss", $approver_name, $designation, $sub_title);

    if ($stmt->execute()) {
        header('Location: approver.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}
$select = mysqli_query($connection, "SELECT * FROM approver");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - NiceAdmin Bootstrap Template</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <link href="img/dti_logo.png" rel="icon">
    <link href="../NiceAdmin/assets/img/apple-touch-icon.png" rel="apple-touch-icon">
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"rel="stylesheet">
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

    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle">
 

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title">Approver</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">Add Approver</button>
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
                                        <div class="mb-3">
                                            <label for="sub_title" class="form-label">Designation2</label>
                                            <input type="text" class="form-control" id="sub_title" name="sub_title"
                                                placeholder="Enter Designation2" required autocomplete="off">
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
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-btn" data-bs-toggle="modal"
                                            data-bs-target="#editUserModal" data-id="<?php echo $row['approver_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['approver_name']); ?>"
                                            data-designation="<?php echo htmlspecialchars($row['designation']); ?>"
                                            data-sub_title="<?php echo htmlspecialchars($row['sub_title']); ?>">
                                            <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top"
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
                        <div class="mb-3">
                            <label for="edit_sub_title" class="form-label">Designation</label>
                            <input type="text" class="form-control" id="edit_sub_title" name="sub_title" required>
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
                    const sub_title = this.getAttribute("data-sub_title");

                    document.getElementById("edit_approver_id").value = id;
                    document.getElementById("edit_approver_name").value = name;
                    document.getElementById("edit_designation").value = designation;
                    document.getElementById("edit_sub_title").value = sub_title;
                });
            });
        });
    </script>

    <!-- delete -->
    <script>
        function deleteUser(userID) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = 'delete_approver.php?approver_id=' + userID + '&confirm=yes';
            }
        }
    </script>


</body>

</html>