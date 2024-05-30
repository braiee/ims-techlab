<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Store user_id and username in session if not already stored
if (!isset($_SESSION["user_id"]) && isset($_POST['user_id']) && isset($_POST['username'])) {
    $_SESSION["user_id"] = $_POST['user_id'];
    $_SESSION["username"] = $_POST['username'];
}

// Fetch all items from the returned_items_history table
$sql_history = "
SELECT 
    history_id,
    borrow_id,
    item_name,
    username,
    return_date,
    received_by,
    received_date
FROM 
    returned_items_history
WHERE 
    received_by = ?
";

// Fetch all items with the status "Returned"
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
    u.username,
    bi.decided_by
FROM 
    borrowed_items bi
LEFT JOIN creative_tools ct ON bi.item_id = ct.creative_id
LEFT JOIN gadget_monitor gm ON bi.item_id = gm.gadget_id
LEFT JOIN office_supplies os ON bi.item_id = os.office_id
LEFT JOIN vendor_owned vo ON bi.item_id = vo.vendor_id
LEFT JOIN users u ON bi.user_id = u.user_id
WHERE 
    bi.status = 'Returned'
";
$result = $conn->query($sql); // Execute the SQL query

$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("s", $_SESSION['username']);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returned Items (Admin)</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user-css.css">
</head>
<body>
<!-- Side Navigation -->
    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
        <a href="dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
        <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
        <a href="admin-borrow.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>

        <a href="category.php" class="nav-item "><span class="icon-placeholder"></span>Categories</a>
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
        <span class="non-clickable-item">Borrow</span>
        <a href="admin-requestborrow.php" class="nav-item"><span class="icon-placeholder"></span>Requests</a>
        <a href="admin-fetchrequest.php" class="nav-item"><span class="icon-placeholder"></span>Returned</a>
        <?php
        // Display settings link only for admin users
        if ($_SESSION['username'] === 'admin') {
            echo '<span class="non-clickable-item">Settings</span>';
            echo '<a href="users.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>';
        }
        ?>
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
        <h1>Returned Items</h1>
        <!-- Table for returned items -->
        <table>
            <thead>
            <tr>
            <th>User</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Borrow Date</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>

                        <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['duration']); ?></td>
                        <td>
    <form action="record_returned_item.php" method="POST">
        <input type="hidden" name="borrow_id" value="<?php echo $row['borrow_id']; ?>">
        <input type="hidden" name="item_name" value="<?php echo $row['item_name']; ?>">
        <input type="hidden" name="username" value="<?php echo $_SESSION['username']; ?>">
        <button type="submit" name="record">Record</button>
    </form>
    <form action="change_status.php" method="POST">
        <input type="hidden" name="borrow_id" value="<?php echo $row['borrow_id']; ?>">
        <button type="submit" name="received">Received</button>
    </form>
</td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Table for returned items history -->
        <h1>Returned Items History</h1>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Username</th>
                    <th>Received By</th>
                    <th>Received Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row_history = $result_history->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row_history['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($row_history['username']);?></td>
                        <td><?php echo htmlspecialchars($row_history['received_by']); ?></td>
                        <td><?php echo htmlspecialchars($row_history['received_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php
        // Display message if the item has already been recorded
        if (isset($_GET['message']) && $_GET['message'] === 'already_recorded') {
            echo '<p>Already recorded.</p>';
        }
        ?>
    </div>
</body>
</html>

