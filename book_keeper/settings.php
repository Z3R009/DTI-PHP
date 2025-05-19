<?php
session_start();
include '../DBConnection.php';

$error_old = "";
$error_new = "";
$error_con = "";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login first!";
    header('location: ../index.php');
    exit();
}

// Initialize variables
$user_id = $_SESSION['user_id'];
$errors = array();
$success = "";

// Fetch current user data
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    $_SESSION['error'] = "User not found!";
    header('location: ../index.php');
    exit();
}

// Handle form submission
if (isset($_POST['update_profile'])) {
    $new_fullname = mysqli_real_escape_string($connection, $_POST['fullname']);
    $new_username = mysqli_real_escape_string($connection, $_POST['username']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate current password
    if (!password_verify($current_password, $user['password'])) {
        $errors[] = "Current password is incorrect";
    }

    // Check if username already exists
    if ($new_username !== $user['username']) {
        $check_username = mysqli_prepare($connection, "SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        mysqli_stmt_bind_param($check_username, "si", $new_username, $user_id);
        mysqli_stmt_execute($check_username);
        $username_result = mysqli_stmt_get_result($check_username);
        if (mysqli_num_rows($username_result) > 0) {
            $errors[] = "Username already exists";
        }
    }

    // Validate new password if provided
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }

    // If no errors, update the user information
    if (empty($errors)) {
        if (!empty($new_password)) {
            // Update with new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET fullname=?, username=?, password=?, updated_at=CURRENT_TIMESTAMP WHERE user_id=?";
            $stmt = mysqli_prepare($connection, $update_query);
            mysqli_stmt_bind_param($stmt, "sssi", $new_fullname, $new_username, $hashed_password, $user_id);
        } else {
            // Update without password
            $update_query = "UPDATE users SET fullname=?, username=?, updated_at=CURRENT_TIMESTAMP WHERE user_id=?";
            $stmt = mysqli_prepare($connection, $update_query);
            mysqli_stmt_bind_param($stmt, "ssi", $new_fullname, $new_username, $user_id);
        }

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['fullname'] = $new_fullname;
            $_SESSION['username'] = $new_username;
            $success = "Profile updated successfully!";

            // Refresh user data
            $query = "SELECT * FROM users WHERE user_id = ?";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
        } else {
            $errors[] = "Error updating profile: " . mysqli_error($connection);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <!-- Bootstrap CSS -->
    <link href="../NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../NiceAdmin/assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php include "Includes/sidebar.php"; ?>
    <?php include "Includes/header.php"; ?>
    <main class="main" id="main">
        <section class="section dashboard">
            <div id="wrapper">
                <div id="content-wrapper" class="d-flex flex-column">
                    <div id="content">
                        <div class="container-fluid">
                            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                                <h1 class="h3 mb-0 text-gray-800">Account Settings</h1>
                            </div>

                            <div class="card">
                                <div class="card-body px-5 py-4">
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <?php foreach ($errors as $error): ?>
                                                <p class="mb-0"><?php echo $error; ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($success)): ?>
                                        <div class="alert alert-success">
                                            <?php echo $success; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" action="">
                                        <div class="mb-4 row align-items-center">
                                            <label for="fullname" class="col-md-3 col-form-label fw-semibold">Full
                                                Name</label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="fullname" name="fullname"
                                                    value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                                            </div>
                                        </div>

                                        <div class="mb-4 row align-items-center">
                                            <label for="username"
                                                class="col-md-3 col-form-label fw-semibold">Username</label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" id="username" name="username"
                                                    value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                            </div>
                                        </div>

                                        <div class="mb-4 row align-items-center">
                                            <label for="current_password"
                                                class="col-md-3 col-form-label fw-semibold">Current Password</label>
                                            <div class="col-md-9">
                                                <input type="password" class="form-control" id="current_password"
                                                    name="current_password" required>
                                                <small class="form-text text-muted">Required to make any changes</small>
                                            </div>
                                        </div>

                                        <div class="mb-4 row align-items-center">
                                            <label for="new_password" class="col-md-3 col-form-label fw-semibold">New
                                                Password</label>
                                            <div class="col-md-9">
                                                <input type="password" class="form-control" id="new_password"
                                                    name="new_password">
                                                <small class="form-text text-muted">Leave blank to keep current
                                                    password</small>
                                            </div>
                                        </div>

                                        <div class="mb-4 row align-items-center">
                                            <label for="confirm_password"
                                                class="col-md-3 col-form-label fw-semibold">Confirm New Password</label>
                                            <div class="col-md-9">
                                                <input type="password" class="form-control" id="confirm_password"
                                                    name="confirm_password">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="offset-md-3 col-md-9">
                                                <button type="submit" name="update_profile"
                                                    class="btn btn-primary me-2">Update Profile</button>
                                                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Bootstrap JS and dependencies -->
    <script src="../NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Vendor JS Files - Consolidated -->
    <script src="../NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="../NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS Files -->
    <script src="../NiceAdmin/assets/js/main.js"></script>
    <script src="js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');

            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger d-none';
            form.insertBefore(errorDiv, form.firstChild);

            function showErrors(errors) {
                if (errors.length > 0) {
                    errorDiv.innerHTML = errors.map(err => `<p class="mb-0">${err}</p>`).join('');
                    errorDiv.classList.remove('d-none');
                } else {
                    errorDiv.classList.add('d-none');
                    errorDiv.innerHTML = '';
                }
            }

            // Validate everything except password match on general input
            form.addEventListener('input', function (e) {
                if (e.target === confirmPassword) return; // skip password match check here
                const errors = [];

                if (currentPassword.value.trim() === '') {
                    errors.push("Current password is required");
                }

                if (newPassword.value.length > 0 && newPassword.value.length < 8) {
                    errors.push("New password must be at least 8 characters");
                }

                // No password match error here

                showErrors(errors);
            });

            // Validate password match specifically on confirmPassword input
            confirmPassword.addEventListener('input', function () {
                const errors = [];

                if (newPassword.value !== confirmPassword.value) {
                    errors.push("New passwords do not match");
                }

                showErrors(errors);
            });

            // Final check on form submit
            form.addEventListener('submit', function (e) {
                const errors = [];

                if (currentPassword.value.trim() === '') {
                    errors.push("Current password is required");
                }

                if (newPassword.value.length > 0 && newPassword.value.length < 8) {
                    errors.push("New password must be at least 8 characters");
                }

                if (newPassword.value !== confirmPassword.value) {
                    errors.push("New passwords do not match");
                }

                if (errors.length > 0) {
                    e.preventDefault();
                    showErrors(errors);
                }
            });
        });
    </script>



</body>

</html>