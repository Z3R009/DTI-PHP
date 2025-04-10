<?php
include '../DBConnection.php';


//Add users

if (isset($_POST['submit'])) {
    $oopap_name = $_POST['oopap_name'];
    $description = $_POST['description'];



    $sql = "INSERT INTO oopap (oopap_name, description) VALUES (?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ss", $oopap_name, $description);

    if ($stmt->execute()) {
        header('Location: oopap.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}


// retrieve users

$select = mysqli_query($connection, "SELECT * FROM oopap");
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

</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>OO/PAP</h1>
              <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">OO/PAP</li>
                </ol>
            </nav>
        </div>
        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">OO/PAP</h5>
                        <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle me-1"></i>Add OO/PAP</button>
                </div>


                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="addUserModalLabel">Add OO/PAP</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" id="addUserForm">
                                        <div class="mb-4">
                                            <label for="oopap_name" class="form-label">OO/PAP</label>
                                            <input type="text" class="form-control" id="oopap_name" name="oopap_name"
                                                placeholder="Enter OO/PAP" required autocomplete="off">
                                        </div>
                                        <div class="mb-4">
                                            <label for="description" class="form-label">Description</label>
                                            <input type="text" class="form-control" id="description" name="description"
                                                placeholder="Enter Description" required autocomplete="off">
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
                                <th>OO/PAP</th>
                                <th>Description</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['oopap_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-btn" data-bs-toggle="modal"
                                            data-bs-target="#editModal" data-id="<?php echo $row['oopap_id']; ?>"
                                            data-oopap_name="<?php echo htmlspecialchars($row['oopap_name']); ?>"
                                            data-description="<?php echo htmlspecialchars($row['description']); ?>">
                                            <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Edit"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteUser(<?php echo $row['oopap_id']; ?>)"><i class="bi bi-trash"
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

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit OO/PAP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm" action="update_oopap.php">
                        <input type="hidden" id="edit_oopap_id" name="oopap_id">
                        <div class="mb-3">
                            <label for="edit_oopap_name" class="form-label">OO/PAP</label>
                            <input type="text" class="form-control" id="edit_oopap_name" name="oopap_name" required
                                autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="edit_description" name="description" required
                                autocomplete="off">
                        </div>

                        <script>
                            document.getElementById("edit_oopap").addEventListener("blur", function () {
                                // Ensure the value is formatted to 2 decimal places
                                this.value = parseFloat(this.value).toFixed(2);
                            });
                        </script>

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
                    const oopap_name = this.getAttribute("data-oopap_name");
                    const description = this.getAttribute("data-description");

                    document.getElementById("edit_oopap_id").value = id;
                    document.getElementById("edit_oopap_name").value = oopap_name;
                    document.getElementById("edit_description").value = description;
                });
            });
        });
    </script>

    <!-- delete -->
    <script>
        function deleteUser(oopapID) {
            if (confirm("Are you sure you want to delete this OO/PAP?")) {
                window.location.href = 'delete_oopap.php?oopap_id=' + oopapID + '&confirm=yes';
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