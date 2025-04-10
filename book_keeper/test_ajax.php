<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AJAX Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        .result { margin-top: 20px; padding: 10px; border: 1px solid #ddd; }
        pre { background-color: #f5f5f5; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>AJAX Test for Services</h1>
        
        <div class="form-group">
            <label for="oopap_id">Select OO/PAP:</label>
            <select id="oopap_id" class="form-control">
                <option value="">Select OO/PAP</option>
                <?php
                include '../DBConnection.php';
                $sql_oopap = "SELECT oopap_id, oopap_name FROM oopap";
                $result_oopap = $connection->query($sql_oopap);
                while ($row = $result_oopap->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['oopap_id']) . "'>" . 
                         htmlspecialchars($row['oopap_name']) . "</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <button id="fetchServices">Fetch Services</button>
        </div>
        
        <div class="result">
            <h3>Result:</h3>
            <pre id="result">No data fetched yet</pre>
        </div>
    </div>
    
    <script>
        document.getElementById('fetchServices').addEventListener('click', function() {
            const oopapId = document.getElementById('oopap_id').value;
            if (!oopapId) {
                alert('Please select an OO/PAP');
                return;
            }
            
            document.getElementById('result').textContent = 'Loading...';
            
            fetch('get_filtered_services.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `oopap_id=${oopapId}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('result').textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('result').textContent = 'Error: ' + error.message;
            });
        });
    </script>
</body>
</html> 