<?php
include '../DBConnection.php';


// retrieve ors
$select = mysqli_query($connection, "
    SELECT ors.*, jev.*,
    payee.payee_name,
    ors.total_amount

    FROM jev
    LEFT JOIN ors ON jev.ors_id = ors.ors_id
    LEFT JOIN payee ON ors.payee_id = payee.payee_id

");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Bookkeeper - Journal Entry Voucher </title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <link href="img/dti_logo.png" rel="icon">
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
    <link rel="stylesheet" href="css/table.css">
    <link rel="stylesheet" href="css/ors.css">
    <link rel="stylesheet" href="css/processed_jev.css">
</head>

<body>

    <?php include "Includes/header.php"; ?>

    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle d-flex align-items-center">
            <h1 class="mb-0">Journal Entry Voucher</h1>

            <!-- Buttons Container with right alignment -->
            <div class="ms-auto">
                <button class="btn btn-primary" onclick="window.location.href='jev.php'"> <i
                        class="bi bi-file-earmark-plus me-1"></i>
                    JEV Form
                </button>
            </div>
        </div>


        <div class="content-wrapper">
            <div class="form-container">

                <div class="tab-content">
                    <div class="card-body">
                        <!-- Table with stripped rows -->
                        <table class="datatable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>JEV No.</th>
                                    <th>Payee Name</th>
                                    <th>Amount</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($select)) { ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $date = new DateTime($row['date']);
                                            echo htmlspecialchars($date->format('F j, Y'));
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['jev_no']); ?></td>
                                        <td><?php echo htmlspecialchars($row['payee_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                                        <td>
                                            <a href="jev_form.php?jev_no=<?php echo urlencode($row['jev_no']); ?>"
                                                class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
    </main>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Vendor JS Files -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>


</body>

</html>