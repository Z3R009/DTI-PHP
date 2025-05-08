<?php
session_start();
include '../DBConnection.php';

// Initialize variables
$fullname = '';
$username = '';

// Fetch current user information
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT fullname, username FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($fullname, $username);
    $stmt->fetch();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    $currentPassword = $_POST['password']; // Retrieve current password from form input

    if ($newPassword !== $confirmPassword) {
        echo "New passwords do not match.";
        exit;
    }

    // Fetch existing password
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($currentPassword, $hashedPassword)) {
        echo "Current password is incorrect.";
        exit;
    }

    // Update user
    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET fullname = ?, username = ?, password = ?, updated_at = NOW() WHERE user_id = ?");
    $stmt->bind_param("sssi", $fullname, $username, $newHashedPassword, $user_id);

    if ($stmt->execute()) {
        echo "Account settings updated successfully.";
    } else {
        echo "Error updating account settings: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Account Settings</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <link href="../NiceAdmin/assets/img/favicon.png" rel="icon">
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
    
    <!-- Add Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include "Includes/header.php"; ?>
    <?php include "Includes/sidebar.php"; ?>

    <main id="main" class="main">
        <div class="pagetitle page-header d-flex justify-content-between align-items-center">
                <div>
                    <h1>Account Settings <?php echo date('Y'); ?></h1>
                    <nav>
                        <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Account Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="modal-body">
            <form method="post" id="editUserForm" action="">
                <div class="mb-3">
                    <label for="fullname" class="form-label">FullName:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="fullname" name="fullname" 
                            value="<?php echo htmlspecialchars($fullname); ?>" required autocomplete="off">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="edit_username" class="form-label">UserName:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="edit_username" name="username" 
                            value="<?php echo htmlspecialchars($username); ?>" required autocomplete="off">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Current Password:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required autocomplete="off">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="new_password" name="new_password" required autocomplete="off">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password:</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="off">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Close
                    </button>
                    
                    <button type="submit" id="update" name="update" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
        

</body>

</html>


