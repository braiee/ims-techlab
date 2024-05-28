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
$sql = "SELECT source, name, owner, category, descriptions, color, imei, sn, custodian
        FROM (
            SELECT 'Vendor Owned' AS source, item_name AS name, vendor_name AS owner, 
                   categories_id AS category, NULL AS descriptions, NULL AS color, NULL AS imei, 
                   NULL AS sn, contact_person AS custodian
            FROM vendor_owned
            UNION ALL
            SELECT 'Office Supplies', office_name, owner, categories_id, NULL, NULL, 
                   emei, sn, custodian
            FROM office_supplies
            UNION ALL
            SELECT 'Gadget Monitor', gadget_name, owner, categories_id, NULL, color, 
                   emei, sn, custodian
            FROM gadget_monitor
            UNION ALL
            SELECT 'Creative Tools', creative_name, owner, categories_id, descriptions, 
                   NULL, emei, sn, custodian
            FROM creative_tools
        ) AS combined_data";

// Apply filter if selected
if ($filter != "") {
    $sql .= " WHERE source LIKE '%$filter%'";
}

// Apply search if provided
if ($search != "") {
    if ($filter != "") {
        $sql .= " AND";
    } else {
        $sql .= " WHERE";
    }
    $sql .= " (name LIKE '%$search%' OR owner LIKE '%$search%' OR category LIKE '%$search%' OR descriptions LIKE '%$search%' OR color LIKE '%$search%' OR imei LIKE '%$search%' OR sn LIKE '%$search%' OR custodian LIKE '%$search%')";
}

$result = $conn->query($sql);

// Check for errors
if (!$result) {
    die("Error: " . $conn->error);
}

// Calculate total values for each category
$total_office_supplies = 0;
$total_creative_tools = 0;
$total_vendor_owned = 0;
$total_gadget_monitor = 0;

while ($row = $result->fetch_assoc()) {
    switch ($row["source"]) {
        case 'Vendor Owned':
            $total_vendor_owned++;
            break;
        case 'Office Supplies':
            $total_office_supplies++;
            break;
        case 'Gadget Monitor':
            $total_gadget_monitor++;
            break;
        case 'Creative Tools':
            $total_creative_tools++;
            break;
        default:
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/category.css">
    <link rel="stylesheet" href="css/user-dashboard.css">

    <title>User Dashboard</title>
</head>
<body>
    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
        <a href="user-dashboard.php" class="nav-item active"><span class="icon-placeholder"></span>Dashboard</a>
        <a href="user-ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
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
                    echo '<li>Hello, ' . $_SESSION["username"] . '!</li>';
                    echo '<li><a href="logout.php">Logout</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="container">
        <div class="card-row">
            <div class="card">
                <h2>Total Office Supplies: <?php echo $total_office_supplies; ?></h2>
            </div>
            <div class="card">
                <h2>Total Creative Tools: <?php echo $total_creative_tools; ?></h2>
            </div>
        </div>
        <div class="card-row">
            <div class="card">
                <h2>Total Vendor Owned: <?php echo $total_vendor_owned; ?></h2>
            </div>
            <div class="card">
                <h2>Total Gadget Monitor: <?php echo $total_gadget_monitor; ?></h2>
            </div>
        </div>

        <div class="table-container">
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
                        <th>Owner</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Color</th>
                        <th>IMEI</th>
                        <th>SN</th>
                        <th>Custodian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Output data of each row
                    $result->data_seek(0); // Reset result pointer
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["source"] . "</td>";
                        echo "<td>" . $row["name"] . "</td>";
                        echo "<td>" . $row["owner"] . "</td>";
                        echo "<td>" . $row["category"] . "</td>";
                        echo "<td>" . $row["descriptions"] . "</td>";
                        echo "<td>" . $row["color"] . "</td>";
                        echo "<td>" . $row["imei"] . "</td>";
                        echo "<td>" . $row["sn"] . "</td>";
                        echo "<td>" . $row["custodian"] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .card-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .card {
            width: 45%; /* Adjust as needed */
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card h2 {
            margin: 5px 0;
        }
        .table-container {
            margin-top: 20px;
        }
    </style>
</body>
</html>
