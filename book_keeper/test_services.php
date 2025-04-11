<?php
include '../DBConnection.php';

// Get all OO/PAPs
$sql_oopap = "SELECT oopap_id, oopap_name FROM oopap";
$result_oopap = $connection->query($sql_oopap);

// Get all services
$sql_services = "SELECT services_id, services_name, code, oopap_id FROM services";
$result_services = $connection->query($sql_services);

// Create a mapping of OO/PAP to services
$services_by_oopap = [];
while ($row = $result_services->fetch_assoc()) {
    $oopap_id = $row['oopap_id'];
    if (!isset($services_by_oopap[$oopap_id])) {
        $services_by_oopap[$oopap_id] = [];
    }
    $services_by_oopap[$oopap_id][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .oopap { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; }
        .services { margin-left: 20px; }
        .service { padding: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Services by OO/PAP</h1>
        
        <?php while ($oopap = $result_oopap->fetch_assoc()): ?>
            <div class="oopap">
                <h3>OO/PAP: <?php echo htmlspecialchars($oopap['oopap_name']); ?> (ID: <?php echo $oopap['oopap_id']; ?>)</h3>
                <div class="services">
                    <?php if (isset($services_by_oopap[$oopap['oopap_id']])): ?>
                        <?php foreach ($services_by_oopap[$oopap['oopap_id']] as $service): ?>
                            <div class="service">
                                <?php echo htmlspecialchars($service['services_name']); ?> 
                                (Code: <?php echo htmlspecialchars($service['code']); ?>)
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No services found for this OO/PAP</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html> 