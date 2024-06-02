<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Initialize variables
$filter = "";
$search = "";

// Check if filter is applied
if (isset($_POST['filter'])) {
    $filter = $_POST['filter'];
}

// Check if search term is provided
if (isset($_POST['search'])) {
    $search = $_POST['search'];
}

// SQL query to fetch combined product data with filter and search
$sql = "SELECT source, name, category, descriptions, color, imei, sn, custodian,
             rnss_acc, remarks, `condition`, purpose, status
        FROM (
            SELECT 'Vendor Owned' AS source, item_name AS name, 
                   categories_id AS category, NULL AS descriptions, NULL AS color, NULL AS imei, 
                   NULL AS sn, contact_person AS custodian, NULL AS rnss_acc,
                   NULL AS remarks, NULL AS `condition`, NULL AS purpose, status
            FROM vendor_owned
            UNION ALL
            SELECT 'Office Supplies' AS source, office_name AS name, categories_id AS category, NULL AS descriptions, 
                   NULL AS color, emei AS imei, sn AS sn, custodian AS custodian, NULL AS rnss_acc, 
                   NULL AS remarks, NULL AS `condition`, NULL AS purpose, status
            FROM office_supplies
            UNION ALL
            SELECT 'Gadget Monitor' AS source, gadget_name AS name, categories_id AS category, 
                   NULL AS descriptions, color AS color, emei AS imei, sn AS sn, custodian AS custodian, 
                   NULL AS rnss_acc, NULL AS remarks, `condition` AS `condition`, purpose AS purpose, status
            FROM gadget_monitor
            UNION ALL
            SELECT 'Creative Tools' AS source, creative_name AS name, categories_id AS category, descriptions AS descriptions, 
                   NULL AS color, emei AS imei, sn AS sn, custodian AS custodian, rnss_acc AS rnss_acc, 
                   remarks AS remarks, NULL AS `condition`, NULL AS purpose, status
            FROM creative_tools
        ) AS combined_data";

// Apply filter if selected
$conditions = [];
$params = [];

if ($filter != "") {
    $conditions[] = "source = ?";
    $params[] = $filter;
}

// Apply search if provided
if ($search != "") {
    $conditions[] = "(name LIKE ? OR category LIKE ? OR descriptions LIKE ? OR color LIKE ? OR imei LIKE ? OR sn LIKE ? OR custodian LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params = array_merge($params, array_fill(0, 8, $searchTerm));
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($sql);

// Check for errors in preparing the statement
if (!$stmt) {
    die("Error: " . $conn->error);
}

// Bind parameters
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Check for errors in execution
if (!$result) {
    die("Error: " . $stmt->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/category.css">
    <title>Manage Gadget Monitor</title>
</head>
<body>
    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
        <a href="dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
        <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
        <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
        <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Legends</a>
        <a href="vendor_owned.php" class="nav-item active"><span class="icon-placeholder"></span>Vendor-Owned</a>
        <span class="non-clickable-item">Office</span>
        <a href="#" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
        <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
        <a href="gadgetmonitor.php" class="nav-item"><span class="icon-placeholder"></span>Gadget Monitor</a>
        <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Office Supplies</a>
        <a href="#" class="nav-item"><span class="icon-placeholder"></span>Gadget Supplies</a>
        <span class="non-clickable-item">Vendors</span>
        <a href="#" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
        <span class="non-clickable-item">Summary</span>
        <a href="product.php" class="nav-item"><span class="icon-placeholder"></span>Product</a>
        <span class="non-clickable-item">Settings</span>
        <a href="users.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>
    </div>

    <!-- Header box container -->
    <div class="header-box">
        <div class="header-box-content">
            <!-- Navigation links -->
            <ul class="nav-links">
                <!-- Display greeting message -->
                <?php
                if (isset($_SESSION["user_id"])) {
                    echo '<li>Hello, ' . htmlspecialchars($_SESSION["username"]) . '!</li>';
                    echo '<li><a href="logout.php">Logout</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
    <div class="container">
        <div class="search-filter-container">
            <h1>Products</h1>
            <form method="post" action="">
                <label for="filter">Filter:</label>
                <select name="filter" id="filter">
                    <option value="">All</option>
                    <option value="Vendor Owned">Vendor Owned</option>
                    <option value="Office Supplies">Office Supplies</option>
                    <option value="Gadget Monitor">Gadget Monitor</option>
                    <option value="Creative Tools">Creative Tools</option>
                </select>
                <input type="text" name="search" placeholder="Search...">
                <button type="submit" class="apply-button">Apply</button>
            </form>
        </div>

        <table class="product-table">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Color</th>
                    <th>IMEI</th>
                    <th>SN</th>
                    <th>Custodian</th>
                    <th>RNSS Acc</th>
                    <th>Remarks</th>
                    <th>Condition</th>
                    <th>Purpose</th>
                    <th>Status</th> <!-- New column for status -->
                </tr>
            </thead>
            <tbody>
                <?php
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["source"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["category"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["descriptions"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["color"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["imei"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["sn"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["custodian"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["rnss_acc"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["remarks"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["condition"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["purpose"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <style>
        .apply-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 20px;
            cursor: pointer;
        }

        .apply-button:hover {
            background-color: #45a049;
        }
    </style>

</body>
</html>
