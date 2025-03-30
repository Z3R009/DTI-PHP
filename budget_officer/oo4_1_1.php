<?php
include '../DBConnection.php';


//Add users

if (isset($_POST['submit'])) {
    $project_id = $_POST['project_id'];
    $oopap_id = $_POST['oopap_id'];
    $account_id = $_POST['account_id'];
    $allotment = $_POST['allotment'];
    $balances = $_POST['balances'];

    $sql = "INSERT INTO project (project_id, oopap_id, account_id, allotment, balances) VALUES (?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iiiss", $project_id, $oopap_id, $account_id, $allotment, $balances);

    if ($stmt->execute()) {
        header('Location: oo4_1_1.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}


// retrieve 

$select = mysqli_query(
    $connection,


    "SELECT project.*,
            account_title.account_title,
            account_title.account_code 
            FROM project

            LEFT JOIN account_title ON project.account_id = account_title.account_id
            WHERE oopap_id = 8"
);

// account name

$query_account = "SELECT account_id, account_title, account_code FROM account_title ORDER BY account_title ASC";
$result_account = $connection->query($query_account);


?>

<?php
// Fetch total allotment
$total_allotment_query = "SELECT SUM(allotment) AS total_allotment FROM project WHERE oopap_id = 8";
$total_allotment_result = mysqli_query($connection, $total_allotment_query);
$total_allotment = mysqli_fetch_assoc($total_allotment_result)['total_allotment'];

// Fetch total balances
$total_balances_query = "SELECT SUM(balances) AS total_balances FROM project WHERE oopap_id = 8";
$total_balances_result = mysqli_query($connection, $total_balances_query);
$total_balances = mysqli_fetch_assoc($total_balances_result)['total_balances'];
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

    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>OO4.1.1</h1>
        </div><!-- End Page Title -->

        <section class="section dashboard">

            <!-- Total Allotment Card -->
            <div class="row">
                <!-- Total Allotment Card -->
                <div class="col-md-6">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Allotment</h5>
                            <h3 class="card-text">₱<?php echo number_format($total_allotment, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <!-- Total Balances Card -->
                <div class="col-md-6">
                    <div class="card bg-white text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Balances</h5>
                            <h3 class="card-text">₱<?php echo number_format($total_balances, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">Add Project/Program/Activities</button>
                    </h5>
                    <p></p>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addUserModalLabel">Add Project/Program/Activities
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" id="addUserForm">
                                        <div class="mb-3">
                                            <input type="hidden" class="form-control" id="oopap_id" name="oopap_id"
                                                value="8" readonly required autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="account_id" class="form-label">Account Title</label>
                                            <select class="form-control" name="account_id" id="account_id">
                                                <option selected disabled>Select Account</option>
                                                <?php
                                                while ($row = $result_account->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row['account_id']) . "' 
                                        data-code='" . htmlspecialchars($row['account_code']) . "'>"
                                                        . htmlspecialchars($row['account_title']) . " - " . htmlspecialchars($row['account_code']) . ""
                                                        . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="allotment" class="form-label">Allotment</label>
                                            <input type="number" class="form-control" id="allotment" name="allotment"
                                                placeholder="Enter Allotment" required autocomplete="off"
                                                oninput="updateBalances()">
                                        </div>
                                        <div class="mb-3">
                                            <input type="hidden" class="form-control" id="balances" name="balances"
                                                placeholder="Balances" readonly>
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
                                <th>Project/Activities/Program</th>
                                <th>Allotment</th>
                                <th>Balances</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['account_title']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['allotment'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['balances'], 2)); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary edit-btn" data-bs-toggle="modal"
                                            data-bs-target="#editModal" data-id="<?php echo $row['project_id']; ?>"
                                            data-account_id="<?php echo htmlspecialchars($row['account_id']); ?>"
                                            data-account_title="<?php echo htmlspecialchars($row['account_title']); ?>"
                                            data-allotment="<?php echo htmlspecialchars($row['allotment']); ?>">
                                            <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Edit"></i>
                                        </button>

                                        <button type="button" class="btn btn-danger"
                                            onclick="deleteUser(<?php echo $row['project_id']; ?>)"><i class="bi bi-trash"
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
                    <h5 class="modal-title" id="editModalLabel">Edit Project/Program/Activities</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm" action="update_oo4_1_1.php">
                        <input type="hidden" id="edit_project_id" name="project_id">
                        <input type="hidden" id="edit_account_id" name="edit_account_id">

                        <div class="mb-3">
                            <label for="edit_account_id" class="form-label">Project/Program/Activities</label>
                            <input type="text" class="form-control" id="edit_account_title" name="account_id" required
                                autocomplete="off" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="edit_allotment" class="form-label">Allotment</label>
                            <input type="number" class="form-control" id="edit_allotment" name="allotment" step="0.01"
                                required autocomplete="off">
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
                    const account_id = this.getAttribute("data-account_id");
                    const account_title = this.getAttribute("data-account_title");
                    const allotment = this.getAttribute("data-allotment");

                    document.getElementById("edit_project_id").value = id;
                    document.getElementById("edit_account_id").value = account_id;
                    document.getElementById("edit_account_title").value = account_title;
                    document.getElementById("edit_allotment").value = allotment;
                });
            });
        });
    </script>

    <!-- delete -->
    <script>
        function deleteUser(oo4_1_1ID) {
            if (confirm("Are you sure you want to delete this oo4_1_1?")) {
                window.location.href = 'delete_oo4_1_1.php?project_id=' + oo4_1_1ID + '&confirm=yes';
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

    <!-- balances -->
    <script>
        function updateBalances() {
            var allotmentValue = document.getElementById('allotment').value;
            document.getElementById('balances').value = allotmentValue;
        }

        function clearForm() {
            document.getElementById("addUserForm").reset();
            updateBalances();
        }
    </script>

</body>

</html>