<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $account_id = $_POST['account_id'];
    $account_title = $_POST['account_title'];
    $account_code = $_POST['account_code'];

    $sql = "UPDATE account_title SET account_title = ?, account_code = ? WHERE account_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ssi", $account_title, $account_code, $account_id);

    if ($stmt->execute()) {
        header('Location: account_title.php?updated=success');

    } else {
        echo "Error: " . $stmt->error;
    }
}

?>