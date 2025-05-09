<?php

include '../DBConnection.php';
if (isset($_POST['update'])) {
    $rc_id = $_POST['rc_id'];
    $code = $_POST['code'];
    $parent_code = $_POST['parent_code'];
    $type = $_POST['type'];
    $acronym = $_POST['acronym'];
    $description = $_POST['description'];

    $sql = "UPDATE responsibility_center SET code = ?, parent_code = ?, type = ?, acronym = ?, description = ? WHERE rc_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssssi", $code, $parent_code, $type, $acronym, $description, $rc_id);

    if ($stmt->execute()) {
        header('Location: responsibility.php?updated=success');
    } else {
        echo "Error: " . $stmt->error;
    }
}

?>