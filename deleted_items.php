<?php

date_default_timezone_set('Asia/Manila');

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/category.css">
    <title>Deleted Items</title>

    <style>
           .table-container {
    max-height: 400px; /* Set a maximum height for the table container */
    overflow-y: auto; /* Enable vertical scrolling */
}
    </style>
</head>
<body>
    <!-- Side Navigation -->
    <div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/techno.png" alt="Logo" class="logo">
</a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Borrow</span>
    <a href="admin-borrow.php" class="nav-item"><span class="icon-placeholder"></span>Requests</a>
        <a href="admin-requestborrow.php" class="nav-item"><span class="icon-placeholder"></span>Approval</a>
        <a href="admin-fetchrequest.php" class="nav-item"><span class="icon-placeholder"></span>Returned</a>

    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item"><span class="icon-placeholder"></span>Device Monitors</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item "><span class="icon-placeholder"></span>Owned Gadgets</a>
    <span class="non-clickable-item">Settings</span>
    <a href="users.php" class="nav-item "><span class="icon-placeholder"></span>Users</a>
    <a href="deleted_items.php" class="nav-item active"><span class="icon-placeholder"></span>Bin</a>

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

    <!-- Content Section -->
    <div class="main-content">
    <div class="table-container">

        <div class="container">
            <?php
            // Include database connection
            include 'db-connect.php';

            // Define an array of tables and their corresponding column names
            $tables = [
                "creative_tools" => ["id" => "creative_id", "name" => "creative_name"],
                "gadget_monitor" => ["id" => "gadget_id", "name" => "gadget_name"],
                "office_supplies" => ["id" => "office_id", "name" => "office_name"],
                "vendor_owned" => ["id" => "vendor_id", "name" => "item_name"],
            ];

            // Loop through each table and fetch deleted items
            foreach ($tables as $table => $columns) {
                $sql = "SELECT * FROM $table WHERE status = 'Deleted'";
                $result = $conn->query($sql);

                // Check if there are deleted items
                if ($result->num_rows > 0) {
                    // Display table header
                    echo '<h2>Deleted Items from ' . ucfirst(str_replace('_', ' ', $table)) . '</h2>';
                    echo '<table>';
                    echo '<thead>';
                    echo '<tr><th>Item Name</th><th>Status</th><th>Deleted By</th><th>Delete Timestamp</th></tr>';
                    echo '</thead>';
                    echo '<tbody>';

                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row[$columns['name']] . '</td>';
                        echo '<td>' . $row["status"] . '</td>';
                        echo '<td>' . $row["deleted_by"] . '</td>';
                        echo '<td>' . $row["delete_timestamp"] . '</td>';
                        echo '</tr>';
                    }

                    echo '</tbody>';
                    echo '</table>';
                    echo '</table>';

                    
                } else {
                    echo "<p>No deleted items found in " . ucfirst(str_replace('_', ' ', $table)) . ".</p>";
                }
            }

            // Close database connection
            $conn->close();
            ?>
                    </div>

        </div>
    </div>
</body>
</html>
