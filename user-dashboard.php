<?php
session_start();
include 'db-connect.php';

// Initialize variables
$filter = "";
$search = "";
$items_per_page = 20; // Number of items per page

// Get the current page or set default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Check if filter is applied
if (isset($_POST['filter'])) {
    $filter = $_POST['filter'];
}

// Check if search term is provided
if (isset($_POST['search'])) {
    $search = $_POST['search'];
}

// Base SQL query to fetch combined product data with filter and search
$sql = "SELECT source, name, category, descriptions, color, imei, sn, custodian, status
        FROM (
            SELECT 'Vendor Owned' AS source, item_name AS name, categories_id AS category, NULL AS descriptions, NULL AS color, NULL AS imei, NULL AS sn, contact_person AS custodian, 
                   (SELECT status FROM borrowed_items WHERE vendor_owned.vendor_id = borrowed_items.user_id LIMIT 1) AS status
            FROM vendor_owned
            UNION ALL
            SELECT 'Office Supplies', office_name AS name, categories_id AS category, NULL AS descriptions, NULL AS color, emei AS imei, sn, custodian, 
                   (SELECT status FROM borrowed_items WHERE office_supplies.office_id = borrowed_items.user_id LIMIT 1) AS status
            FROM office_supplies
            UNION ALL
            SELECT 'Gadget Monitor', gadget_name AS name, categories_id AS category, NULL AS descriptions, color, emei AS imei, sn, custodian,
                   (SELECT status FROM borrowed_items WHERE gadget_monitor.gadget_id = borrowed_items.user_id LIMIT 1) AS status
            FROM gadget_monitor
            UNION ALL
            SELECT 'Creative Tools', creative_name AS name, categories_id AS category, descriptions, NULL AS color, emei AS imei, sn, custodian,
                   (SELECT status FROM borrowed_items WHERE creative_tools.creative_id = borrowed_items.user_id LIMIT 1) AS status
            FROM creative_tools
        ) AS combined";

// Add filter condition
if ($filter != "") {
    $sql .= " WHERE source = ?";
}

// Add search condition
if ($search != "") {
    if ($filter != "") {
        $sql .= " AND (name LIKE ? OR category LIKE ? OR descriptions LIKE ? OR color LIKE ? OR imei LIKE ? OR sn LIKE ? OR custodian LIKE ? OR status LIKE ?)";
    } else {
        $sql .= " WHERE (name LIKE ? OR category LIKE ? OR descriptions LIKE ? OR color LIKE ? OR imei LIKE ? OR sn LIKE ? OR custodian LIKE ? OR status LIKE ?)";
    }
}

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
if ($filter != "" && $search != "") {
    $search_param = "%$search%";
    $stmt->bind_param('sssssssss', $filter, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
} elseif ($filter != "" && $search == "") {
    $stmt->bind_param('s', $filter);
} elseif ($filter == "" && $search != "") {
    $search_param = "%$search%";
    $stmt->bind_param('ssssssss', $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();
$total_items = $result->num_rows;

// Add LIMIT clause for pagination
$sql .= " LIMIT ?, ?";
$stmt = $conn->prepare($sql);
if ($filter != "" && $search != "") {
    $stmt->bind_param('ssssssssii', $filter, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $offset, $items_per_page);
} elseif ($filter != "" && $search == "") {
    $stmt->bind_param('sii', $filter, $offset, $items_per_page);
} elseif ($filter == "" && $search != "") {
    $stmt->bind_param('sssssssii', $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $offset, $items_per_page);
} else {
    $stmt->bind_param('ii', $offset, $items_per_page);
}
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Invalid query: " . $conn->error);
}

// Calculate totals
$total_office_supplies = $conn->query("SELECT COUNT(*) FROM office_supplies")->fetch_row()[0];
$total_creative_tools = $conn->query("SELECT COUNT(*) FROM creative_tools")->fetch_row()[0];
$total_vendor_owned = $conn->query("SELECT COUNT(*) FROM vendor_owned")->fetch_row()[0];
$total_gadget_monitor = $conn->query("SELECT COUNT(*) FROM gadget_monitor")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/category.css">
    <title>User Dashboard</title>
    <style>

body {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    font-family: Arial, sans-serif;
}

.wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    margin-left: 200px;
    margin-top: 100px;
    margin-bottom: 10px;
}

.main-content {
    width: 80%;
    max-width: 1200px;
    margin: 0 auto;
    background-color: #C7E8CA;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow-y: hidden;
}

.table-container {
    max-height: 400px;
    overflow-y: auto;
}

.pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
    margin-top: 20px; /* Adjust margin as needed */
}

.pagination li {
    margin: 0 5px;
}

.pagination a {
    text-decoration: none;
    color: #555;
    background-color: #f9f9f9;
    padding: 8px 12px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.pagination a:hover {
    background-color: #5D9C59;
    color: #fff;
}

.pagination a.active {
        background-color: #4CAF50;
        color: white;
        border: 1px solid #4CAF50;
    }

.card-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    margin-bottom: 150px;
    margin-left: 30px;
    height: 10px;

}



.card {
    background-color: #DDF7E3;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    flex: 1 1 calc(50% - 20px);
    max-width: calc(50% - 20px);
    text-align: center;
    pointer-events: none; /* Disable pointer events */

}

@media (max-width: 768px) {
    .card {
        flex: 1 1 calc(100% - 20px);
        max-width: calc(100% - 20px);
    }
}

.search-filter-form {
    display: flex;
    align-items: center;
    margin-bottom: 20px; /* Adjust margin as needed */
}

.search-filter-form input[type="text"],
.search-filter-form select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-right: 10px; /* Adjust margin between elements */
}

.search-filter-form button {
    padding: 8px 12px;
    background-color: #DDF7E3; /* Blue */
    color: #5D9C59;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.search-filter-form button:hover {
    background-color: #ddf7e3ac;
}

    </style>
</head>
<body>
    <!-- Side Navigation -->
    <div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/techno.png" alt="Logo" class="logo"></a>
        <a href="user-dashboard.php" class="nav-item active"><span class="icon-placeholder"></span>Dashboard</a>
        <a href="user-borrow.php" class="nav-item "><span class="icon-placeholder"></span>Borrow</a>
        <a href="user-pendingborrow.php" class="nav-item"><span class="icon-placeholder"></span>Pending</a>
        <a href="user-resultborrow.php" class="nav-item"><span class="icon-placeholder"></span>Result</a>
        <span class="non-clickable-item">Settings</span>
        <a href="user-users.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>

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

    <div class="wrapper">
        <div class="main-content">
            <!-- Search and Filter Form -->
            <form method="POST" class="search-filter-form">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="filter">
                    <option value="">Select Source</option>
                    <option value="Vendor Owned" <?php if ($filter == 'Vendor Owned') echo 'selected'; ?>>Vendor Owned</option>
                    <option value="Office Supplies" <?php if ($filter == 'Office Supplies') echo 'selected'; ?>>Office Supplies</option>
                    <option value="Gadget Monitor" <?php if ($filter == 'Gadget Monitor') echo 'selected'; ?>>Gadget Monitor</option>
                    <option value="Creative Tools" <?php if ($filter == 'Creative Tools') echo 'selected'; ?>>Creative Tools</option>
                </select>
                <button type="submit">Apply</button>
            </form>
            <div class="table-container">

            <div class="table-container">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Tagging</th>
                            <th>Item</th>
                            <th>Asset Number</th>
                            <th>Status</th>
                            <th>Owner</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Output data of each row
                        $result->data_seek(0); // Reset result pointer
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["source"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["category"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["status"] ? $row["status"] : 'Available') . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            </div>

            <!-- Pagination Links -->
            <ul class="pagination">
                <?php
                // Calculate the total number of pages
                $total_pages = ceil($total_items / $items_per_page);

                // Display pagination links
                for ($page = 1; $page <= $total_pages; $page++) {
                    if ($page == $current_page) {
                        echo "<li><strong>$page</strong></li>";
                    } else {
                        echo "<li><a href=\"?page=$page\">$page</a></li>";
                    }
                }
                ?>
            </ul>
        </div>

    </div>
</body>
</html>
