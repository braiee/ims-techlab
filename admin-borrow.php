<?php
session_start();
include 'db-connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["identity"] != 1) {
    header("Location: login.php");
    exit();
}

// Fetch all borrow requests
$sql = "
    SELECT 
        bi.borrow_id,
        bi.status,
        bi.return_date AS duration,
        bi.borrow_date,
        COALESCE(ct.creative_name, gm.gadget_name, os.office_name, vo.item_name) AS item_name,
        CASE 
            WHEN ct.creative_id IS NOT NULL THEN 'Creative Tools'
            WHEN gm.gadget_id IS NOT NULL THEN 'Gadget Monitor'
            WHEN os.office_id IS NOT NULL THEN 'Office Supplies'
            WHEN vo.vendor_id IS NOT NULL THEN 'Vendor Owned'
            ELSE 'Unknown'
        END AS category,
        u.username  
    FROM 
        borrowed_items bi
    LEFT JOIN creative_tools ct ON bi.item_id = ct.creative_id
    LEFT JOIN gadget_monitor gm ON bi.item_id = gm.gadget_id
    LEFT JOIN office_supplies os ON bi.item_id = os.office_id
    LEFT JOIN vendor_owned vo ON bi.item_id = vo.vendor_id
    LEFT JOIN users u ON bi.user_id = u.user_id  
    ORDER BY bi.borrow_id DESC
";

$result = $conn->query($sql);

// Check for errors
if (!$result) {
    die("Error executing query: " . $conn->error);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user-css.css">

    <title>Admin Borrow Requests</title>
</head>
<body>
    <div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/techno.png" alt="Logo" class="logo">
</a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Borrow</span>
        <a href="admin-borrow.php" class="nav-item active"><span class="icon-placeholder"></span>Borrow</a>
        <a href="admin-requestborrow.php" class="nav-item"><span class="icon-placeholder"></span>Requests</a>
        <a href="admin-fetchrequest.php" class="nav-item"><span class="icon-placeholder"></span>Returned</a>
    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item"><span class="icon-placeholder"></span>Device Monitors</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
        <span class="non-clickable-item">Settings</span>
    <a href="users.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>
    <a href="deleted_items.php" class="nav-item"><span class="icon-placeholder"></span>Bin</a>

    </div>

    <div class="header-box">
        <div class="header-box-content">
            <ul class="nav-links">
                <?php
                if (isset($_SESSION["user_id"])) {
                    echo '<li>Hello, ' . $_SESSION["username"] . '!</li>';
                    echo '<li><a href="logout.php">Logout</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="center-container">
    <div class="container">
    <h1>Borrow Requests</h1>
    <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Status</th>
                <th>Username</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['item_name']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>

    </div>
    </div>


</body>
</html>
