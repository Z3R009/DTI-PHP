<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "dti";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch categories
$categories = $conn->query("SELECT * FROM financial_categories");
// Fetch subcategories
$subcategories = $conn->query("SELECT * FROM financial_subcategories");
// Fetch submodules
$submodules = $conn->query("SELECT * FROM financial_submodules");

// Retrieve data
$sql = "
    SELECT 
        fc.category_name, 
        fsc.subcategory_name, 
        fsm.submodule_name, 
        foc.object_name, 
        foc.uacs_code, 
        foc.status 
    FROM financial_categories fc
    LEFT JOIN financial_subcategories fsc ON fc.category_id = fsc.category_id
    LEFT JOIN financial_submodules fsm ON fsc.subcategory_id = fsm.subcategory_id
    LEFT JOIN financial_object_code foc ON fsm.submodule_id = foc.submodule_id
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Financial Code Encoder</title>
    <link rel="stylesheet" type="text/css" href="add.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: auto;
            margin: 0 auto 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-height: 200px;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        select,
        input,
        button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 100px;
        }

        thead {
            background-color: #343a40;
            color: white;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
    </style>
    <script>
        function updateForm() {
            var type = document.getElementById("type").value;
            document.getElementById("categoryInput").style.display = type === "category" ? "block" : "none";
            document.getElementById("subcategoryInput").style.display = type === "subcategory" ? "block" : "none";
            document.getElementById("submoduleInput").style.display = type === "submodule" ? "block" : "none";
            document.getElementById("objectCodeInput").style.display = type === "object_code" ? "block" : "none";
        }
    </script>
</head>

<body>
    <div class="container">
        <!-- Form Section -->
        <form method="POST" action="process.php">
            <label>Select Type:</label>
            <select id="type" name="type" onchange="updateForm()">
                <option value="">--Select--</option>
                <option value="category">Financial Category</option>
                <option value="subcategory">Financial Subcategory</option>
                <option value="submodule">Financial Submodule</option>
                <option value="object_code">Financial Object Code</option>
            </select>

            <div id="categoryInput" style="display:none;">
                <label>Category Name:</label>
                <input type="text" name="category_name" autocomplete="off">
            </div>

            <div id="subcategoryInput" style="display:none;">
                <label>Select Category:</label>
                <select name="category_id">
                    <?php while ($row = $categories->fetch_assoc()) { ?>
                        <option value="<?php echo $row['category_id']; ?>"> <?php echo $row['category_name']; ?> </option>
                    <?php } ?>
                </select>
                <label>Subcategory Name:</label>
                <input type="text" name="subcategory_name" autocomplete="off">
            </div>

            <div id="submoduleInput" style="display:none;">
                <label>Select Category:</label>
                <select name="subcategory_id">
                    <?php $categories->data_seek(0);
                    while ($row = $subcategories->fetch_assoc()) { ?>
                        <option value="<?php echo $row['subcategory_id']; ?>"> <?php echo $row['subcategory_name']; ?>
                        </option>
                    <?php } ?>
                </select>
                <label>Submodule Name:</label>
                <input type="text" name="submodule_name" autocomplete="off">
            </div>

            <div id="objectCodeInput" style="display:none;">
                <label>Select Submodule:</label>
                <select name="submodule_id">
                    <?php while ($row = $submodules->fetch_assoc()) { ?>
                        <option value="<?php echo $row['submodule_id']; ?>">
                            <?php echo $row['submodule_name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Object Code Name:</label>
                <input type="text" name="object_code_name" autocomplete="off">

                <label>UACS Code:</label>
                <input type="text" name="uacs_code" autocomplete="off">

                <label>Status:</label>
                <select name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <button type="submit">Submit</button>
        </form>

        <!-- Table Section -->
        <table>
            <thead>
                <tr>
                    <th colspan="4" style="text-align: center;">Object Code</th>
                    <th>UACS Code</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $currentCategory = $currentSubcategory = $currentSubmodule = "";
                $rowNumber = 1;

                while ($row = $result->fetch_assoc()) {
                    // Display category only if it's a new one
                    if ($row['category_name'] !== $currentCategory) {
                        echo "<tr>";
                        echo "<td colspan='3'><strong>{$row['category_name']}</strong></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "</tr>";
                        $currentCategory = $row['category_name'];
                    }

                    // Display subcategory only if it's a new one
                    if ($row['subcategory_name'] !== $currentSubcategory) {
                        echo "<tr>";
                        echo "<td></td>";
                        echo "<td colspan='2'>{$row['subcategory_name']}</td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "</tr>";
                        $currentSubcategory = $row['subcategory_name'];
                    }

                    // Display submodule only if it's a new one
                    if ($row['submodule_name'] !== $currentSubmodule) {
                        echo "<tr>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td colspan='2'>{$row['submodule_name']}</td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "</tr>";
                        $currentSubmodule = $row['submodule_name'];
                    }

                    // Display object code
                    echo "<tr>";
                    echo "<td></td>";
                    echo "<td></td>";
                    echo "<td></td>";
                    echo "<td>{$row['object_name']}</td>";
                    echo "<td>{$row['uacs_code']}</td>";
                    echo "<td>{$row['status']}</td>";
                    echo "</tr>";

                    $rowNumber++;
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<?php $conn->close(); ?>