<?php
include '../DBConnection.php';

if (isset($_POST['add_account'])) {
    $dv_id = $_POST['dv_id'];
    $account_id = $_POST['account_id'];
    $debit_amount = !empty($_POST['debit_amount']) ? $_POST['debit_amount'] : 0;
    $credit_amount = !empty($_POST['credit_amount']) ? $_POST['credit_amount'] : 0;

    // Start transaction
    $connection->begin_transaction();

    try {
        // Get DV number for redirect
        $dv_query = "SELECT dv_no FROM dv WHERE dv_id = ?";
        $dv_stmt = $connection->prepare($dv_query);
        if (!$dv_stmt) {
            throw new Exception("Query preparation failed: " . $connection->error);
        }
        $dv_stmt->bind_param("i", $dv_id);
        $dv_stmt->execute();
        $result = $dv_stmt->get_result();
        $dv_data = $result->fetch_assoc();
        $dv_no = $dv_data['dv_no'];
        $dv_stmt->close();

        // Insert debit entry if amount exists
        if ($debit_amount > 0) {
            $query = "INSERT INTO dv_history (dv_id, account_id, type, amount) VALUES (?, ?, 'debit', ?)";
            $stmt = $connection->prepare($query);
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $connection->error);
            }
            $stmt->bind_param("iid", $dv_id, $account_id, $debit_amount);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting debit record: " . $stmt->error);
            }
            $stmt->close();
        }

        // Insert credit entry if amount exists
        if ($credit_amount > 0) {
            $query = "INSERT INTO dv_history (dv_id, account_id, type, amount) VALUES (?, ?, 'credit', ?)";
            $stmt = $connection->prepare($query);
            if (!$stmt) {
                throw new Exception("Query preparation failed: " . $connection->error);
            }
            $stmt->bind_param("iid", $dv_id, $account_id, $credit_amount);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting credit record: " . $stmt->error);
            }
            $stmt->close();
        }

        // Commit transaction
        $connection->commit();

        // Redirect back to DV form
        header("Location: dv_form.php?dv_no=" . $dv_no);
        exit();

    } catch (Exception $e) {
        // Rollback on error
        $connection->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?> 