<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "DBConnection.php";

// Test ORS table
$ors_query = "SHOW COLUMNS FROM `ors`";
$ors_result = $connection->query($ors_query);
if (!$ors_result) {
    echo "Error in ORS query: " . $connection->error . "<br>";
} else {
    echo "ORS Table Columns:<br>";
    while ($row = $ors_result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
}

echo "<br>";

// Test DV table
$dv_query = "SHOW COLUMNS FROM `dv`";
$dv_result = $connection->query($dv_query);
if (!$dv_result) {
    echo "Error in DV query: " . $connection->error . "<br>";
} else {
    echo "DV Table Columns:<br>";
    while ($row = $dv_result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
}

echo "<br>";

// Test fund_cluster table
$fc_query = "SHOW COLUMNS FROM `fund_cluster`";
$fc_result = $connection->query($fc_query);
if (!$fc_result) {
    echo "Error in fund_cluster query: " . $connection->error . "<br>";
} else {
    echo "Fund Cluster Table Columns:<br>";
    while ($row = $fc_result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
}

// Test a simple query on ORS table
$test_query = "SELECT * FROM `ors` LIMIT 1";
$test_result = $connection->query($test_query);
if (!$test_result) {
    echo "Error in test query: " . $connection->error . "<br>";
} else {
    echo "<br>Test query successful. First row from ORS table:<br>";
    $row = $test_result->fetch_assoc();
    if ($row) {
        foreach ($row as $key => $value) {
            echo "$key: $value<br>";
        }
    } else {
        echo "No rows found in ORS table.<br>";
    }
}
?> 