<?php
include '../DBConnection.php';

if (isset($_GET['payee_id']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $payee_id = $_GET['payee_id'];

    // Check if payee is being used in any DVs
    $check_sql = "SELECT COUNT(*) FROM dv WHERE payee_id = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("i", $payee_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();

    if ($count > 0) {
        echo "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cannot Delete',
                        text: 'This payee is being used in one or more DVs and cannot be deleted.',
                        confirmButtonColor: '#d33'
                    }).then(() => {
                        window.location.href = 'add_payee.php';
                    });
                });
            </script>
        ";
    } else {
        $delete_sql = "DELETE FROM payee WHERE payee_id = ?";
        $stmt = $connection->prepare($delete_sql);

        if ($stmt === false) {
            echo "<script>alert('Error preparing the statement: " . $connection->error . "');</script>";
        } else {
            $stmt->bind_param("i", $payee_id);
            if ($stmt->execute()) {
                echo "
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Payee has been deleted successfully.',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                window.location.href = 'add_payee.php';
                            });
                        });
                    </script>
                ";
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        }
    }

    $check_stmt->close();
} else {
    header("Location: add_payee.php");
    exit();
}
?> 