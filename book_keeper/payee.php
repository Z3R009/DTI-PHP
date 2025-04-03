<?php

include '../DBConnection.php';


if (isset($_POST['submit'])) {
    $payee_name = $_POST['payee_name'];
    $bank_acc_no = $_POST['bank_acc_no'];
    $tin_no = $_POST['tin_no'];
    $address = $_POST['address'];
    $nature = $_POST['nature'];
    $contact_no = $_POST['contact_no'];

    $sql = "INSERT INTO payee (payee_name, bank_acc_no, tin_no, address, nature, contact_no) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssssss", $payee_name, $bank_acc_no, $tin_no, $address, $nature, $contact_no);

    if ($stmt->execute()) {
        header('Location: payee.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

// retrieve payee

$select = mysqli_query($connection, "SELECT * FROM payee");

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
            <h1>Payee</h1>
            <nav>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">Add Payee</button>
                    </h5>
                    <p></p>

                    <!-- Modal for Add User Form -->
                    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addUserModalLabel">Add Payee
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="post" id="addCluster">

                                        <div class="mb-3">
                                            <label for="payee_name" class="form-label">Payee Name</label>
                                            <input type="text" class="form-control" id="payee_name" name="payee_name"
                                                placeholder="Enter Payee Name" required autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="bank_acc_no" class="form-label">Bank Account No.</label>
                                            <input type="text" class="form-control" id="bank_acc_no" name="bank_acc_no"
                                                placeholder="Enter Bank Account No." autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="tin_no" class="form-label">TIN/Employee No.</label>
                                            <input type="text" class="form-control" id="tin_no" name="tin_no"
                                                placeholder="Enter TIN/Employee No." autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <input type="text" class="form-control" id="address" name="address"
                                                placeholder="Enter Address" autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="nature" class="form-label">Nature of Business</label>
                                            <input type="text" class="form-control" id="nature" name="nature"
                                                placeholder="Enter Nature of Business" autocomplete="off">
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact_no" class="form-label">Contact Number</label>
                                            <input type="text" class="form-control" id="contact_no" name="contact_no"
                                                maxlength="13" autocomplete="off" placeholder="Enter Contact Number">
                                            <span id="addErrorMsg" style="color: red; display: none;">Please enter
                                                numbers
                                                only</span>
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
                                <th>Payee Name</th>
                                <th>Bank Account No.</th>
                                <th>TIN/Employee No.</th>
                                <th>Address</th>
                                <th>Nature of Business</th>
                                <th>Contact Number</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['payee_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['bank_acc_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tin_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nature']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary edit-btn" data-bs-toggle="modal"
                                            data-bs-target="#editUserModal" data-id="<?php echo $row['payee_id']; ?>"
                                            data-payee_name="<?= htmlspecialchars($row['payee_name']); ?>"
                                            data-bank_acc_no="<?= htmlspecialchars($row['bank_acc_no']); ?>"
                                            data-tin_no="<?= htmlspecialchars($row['tin_no']); ?>"
                                            data-address="<?= htmlspecialchars($row['address']); ?>"
                                            data-nature="<?= htmlspecialchars($row['nature']); ?>"
                                            data-contact_no="<?= htmlspecialchars($row['contact_no']); ?>">
                                            <i class="bi bi-pencil" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Edit"></i>
                                        </button>


                                        <button type="button" class="btn btn-danger"
                                            onclick="deleteUser(<?php echo $row['payee_id']; ?>)"><i class="bi bi-trash"
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
                    <h5 class="modal-title" id="editUserModalLabel">Edit Payee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="editUserForm" action="update_payee.php">
                        <input type="hidden" id="edit_payee_id" name="payee_id">
                        <div class="mb-3">
                            <label for="edit_payee_name" class="form-label">Payee Name</label>
                            <input type="text" class="form-control" id="edit_payee_name" name="payee_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_bank_acc_no" class="form-label">Bank Account No.</label>
                            <input type="number" class="form-control" id="edit_bank_acc_no" name="bank_acc_no" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tin_no" class="form-label">TIN/Employee No.</label>
                            <input type="text" class="form-control" id="edit_tin_no" name="tin_no" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="edit_address" name="address" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nature" class="form-label">Nature of Business</label>
                            <input type="text" class="form-control" id="edit_nature" name="nature" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_contact_no" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="edit_contact_no" name="contact_no" required
                                maxlength="13" autocomplete="off" placeholder="Enter Contact Number">
                            <span id="editErrorMsg" style="color: red; display: none;">Please enter
                                numbers
                                only</span>
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
                    const payee_name = this.getAttribute("data-payee_name");
                    const bank_acc_no = this.getAttribute("data-bank_acc_no");
                    const tin_no = this.getAttribute("data-tin_no");
                    const address = this.getAttribute("data-address");
                    const nature = this.getAttribute("data-nature");
                    const contact_no = this.getAttribute("data-contact_no");

                    document.getElementById("edit_payee_id").value = id;
                    document.getElementById("edit_payee_name").value = payee_name;
                    document.getElementById("edit_bank_acc_no").value = bank_acc_no;
                    document.getElementById("edit_tin_no").value = tin_no;
                    document.getElementById("edit_address").value = address;
                    document.getElementById("edit_nature").value = nature;
                    document.getElementById("edit_contact_no").value = contact_no;
                });
            });
        });
    </script>

    <!-- delete -->
    <script>
        function deleteUser(userID) {
            if (confirm("Are you sure you want to delete this user?")) {
                window.location.href = 'delete_payee.php?payee_id=' + userID + '&confirm=yes';
            }
        }
    </script>

    <!-- Contact Number -->
    <script>
        // Function to clear the form
        function clearForm() {
            document.getElementById('addCluster').reset();
        }

        // Contact number validation for Add Payee modal
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

        // Contact number validation for Edit Payee modal
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
    </script>

</body>

</html>