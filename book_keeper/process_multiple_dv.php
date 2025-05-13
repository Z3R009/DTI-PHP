<?php
include '../DBConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $connection->begin_transaction();

    try {
        // Get form data
        $date = $_POST['date'];
        $dv_no = $_POST['dv_no'];
        $fund_cluster = $_POST['fund_cluster'];
        $total_amount = $_POST['total_amount'];
        $vat = $_POST['vat'] ?? 0;
        $vat_amount = $_POST['vat_amount'] ?? 0;
        $tax_base = $_POST['tax_base'] ?? 0;
        $tax_1 = $_POST['tax_1'] ?? 0;
        $tax_1_amount = $_POST['tax_1_amount'] ?? 0;
        $tax_2 = $_POST['tax_2'] ?? 0;
        $tax_2_amount = $_POST['tax_2_amount'] ?? 0;
        $net_amount = $_POST['net_amount'] ?? 0;
        $chief_accountant = $_POST['chief_accountant'];
        $regional_director = $_POST['regional_director'];
        $account_titles = $_POST['account_titles'];
        $debit_amounts = $_POST['debit_amounts'];
        $credit_amounts = $_POST['credit_amounts'];
        $ors_ids = json_decode($_POST['ors_ids']);

        // Insert into dv table
        $sql = "INSERT INTO dv (date, dv_no, fund_cluster, total_amount, vat, vat_amount, tax_base, tax_1, tax_1_amount, tax_2, tax_2_amount, net_amount, chief_accountant, regional_director) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connection->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . htmlspecialchars($connection->error));
        }

        $stmt->bind_param(
            "sssddddddddss",
            $date,
            $dv_no,
            $fund_cluster,
            $total_amount,
            $vat,
            $vat_amount,
            $tax_base,
            $tax_1,
            $tax_1_amount,
            $tax_2,
            $tax_2_amount,
            $net_amount,
            $chief_accountant,
            $regional_director
        );

        if (!$stmt->execute()) {
            throw new Exception("Error inserting DV: " . $stmt->error);
        }

        $dv_id = $connection->insert_id;
        $stmt->close();

        // Insert into dv_ors table to link DV with ORS entries
        $link_sql = "INSERT INTO dv_ors (dv_id, ors_id) VALUES (?, ?)";
        $link_stmt = $connection->prepare($link_sql);
        if ($link_stmt === false) {
            throw new Exception('Prepare failed (DV-ORS link): ' . htmlspecialchars($connection->error));
        }

        foreach ($ors_ids as $ors_id) {
            $link_stmt->bind_param("ii", $dv_id, $ors_id);
            if (!$link_stmt->execute()) {
                throw new Exception("Error linking DV with ORS: " . $link_stmt->error);
            }

            // Update ORS status
            $update_sql = "UPDATE ors SET status = 'Endorsed' WHERE ors_id = ?";
            $update_stmt = $connection->prepare($update_sql);
            if ($update_stmt === false) {
                throw new Exception('Prepare failed (ORS update): ' . htmlspecialchars($connection->error));
            }
            $update_stmt->bind_param("i", $ors_id);
            if (!$update_stmt->execute()) {
                throw new Exception("Error updating ORS status: " . $update_stmt->error);
            }
            $update_stmt->close();
        }
        $link_stmt->close();

        // Insert accounting entries
        for ($i = 0; $i < count($account_titles); $i++) {
            if (empty($account_titles[$i])) continue;

            $account_id = $account_titles[$i];
            $debit = !empty($debit_amounts[$i]) ? $debit_amounts[$i] : 0;
            $credit = !empty($credit_amounts[$i]) ? $credit_amounts[$i] : 0;

            if ($debit == 0 && $credit == 0) continue;

            $type = ($debit > 0) ? 'debit' : 'credit';
            $amount = ($debit > 0) ? $debit : $credit;

            $history_sql = "INSERT INTO dv_history (dv_id, account_id, type, amount) VALUES (?, ?, ?, ?)";
            $history_stmt = $connection->prepare($history_sql);
            if ($history_stmt === false) {
                throw new Exception('Prepare failed (DV history): ' . htmlspecialchars($connection->error));
            }

            $history_stmt->bind_param("iisd", $dv_id, $account_id, $type, $amount);
            if (!$history_stmt->execute()) {
                throw new Exception("Error inserting DV history: " . $history_stmt->error);
            }
            $history_stmt->close();
        }

        $connection->commit();
        header("Location: dv_form.php?dv_no=" . urlencode($dv_no));
        exit();

    } catch (Exception $e) {
        $connection->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: dv.php");
    exit();
}
?> 