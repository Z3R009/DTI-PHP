<?php
include '../DBConnection.php';


//Add users

if (isset($_POST['submit'])) {
    $approver_name = $_POST['approver_name'];
    $designation = $_POST['designation'];



    $sql = "INSERT INTO approver (approver_name, designation) VALUES (?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ss", $approver_name, $designation);

    if ($stmt->execute()) {
        header('Location: approver.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}


// retrieve users

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
            <h1>Approver</h1>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">Add Approver</button>
                    </h5>
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
                    <table class="table datatable">
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
                                        <button type="button" class="btn btn-primary"
                                            onclick="editUser(<?php echo $row['approver_name']; ?>, '<?php echo $row['designation']; ?>')">
                                            <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Edit"></i>
                                        </button>

                                        <button type="button" class="btn btn-danger"
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