<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Fetch borrow requests for the logged-in user based on the status filter
$user_id = $_SESSION["user_id"];
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$statusCondition = '';
$statusValues = ['Approved', 'Not Approved']; // Define valid status values excluding 'Returned'

if (!empty($statusFilter) && in_array($statusFilter, $statusValues)) {
    $statusCondition = "AND bi.status = ?";
}

$sql = "
SELECT 
bi.borrow_id,
CASE 
    WHEN ct.creative_id IS NOT NULL THEN ct.creative_name
    WHEN gm.gadget_id IS NOT NULL THEN gm.gadget_name
    WHEN os.office_id IS NOT NULL THEN os.office_name
    WHEN vo.vendor_id IS NOT NULL THEN vo.item_name
    ELSE 'Unknown'
END AS item_name,
bi.status,
bi.return_date AS duration,
bi.borrow_date,
bi.decided_by, -- Fetch decided_by directly from the borrowed_items table
CASE 
    WHEN ct.creative_id IS NOT NULL THEN 'Creative Tools'
    WHEN gm.gadget_id IS NOT NULL THEN 'Gadget Monitor'
    WHEN os.office_id IS NOT NULL THEN 'Office Supplies'
    WHEN vo.vendor_id IS NOT NULL THEN 'Vendor Owned'
    ELSE 'Unknown'
END AS category
FROM 
borrowed_items bi
LEFT JOIN creative_tools ct ON bi.item_id = ct.creative_id
LEFT JOIN gadget_monitor gm ON bi.item_id = gm.gadget_id
LEFT JOIN office_supplies os ON bi.item_id = os.office_id
LEFT JOIN vendor_owned vo ON bi.item_id = vo.vendor_id
WHERE 
bi.user_id = ? 
AND bi.status IN ('Approved', 'Not Approved')
$statusCondition
";
$stmt = $conn->prepare($sql);

if (!empty($statusFilter) && in_array($statusFilter, $statusValues)) {
    $stmt->bind_param("is", $user_id, $statusFilter);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Items Result</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user-css.css">
</head>
<body>
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
        <a href="user-dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
        <a href="user-borrow.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
        <a href="user-pendingborrow.php" class="nav-item"><span class="icon-placeholder"></span>Pending</a>
        <a href="user-resultborrow.php" class="nav-item active"><span class="icon-placeholder"></span>Result</a>
        <span class="non-clickable-item">Settings</span>
        <a href="user-users.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>
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

    <div class="container">
    <h1>Borrowed Items Result</h1>

    <form action="" method="GET">
        <label for="status">Filter by Status:</label>
        <select name="status" id="status">
            <option value="">All</option>
            <option value="Approved" <?php if ($statusFilter === 'Approved') echo 'selected'; ?>>Approved</option>
            <option value="Not Approved" <?php if ($statusFilter === 'Not Approved') echo 'selected'; ?>>Not Approved</option>
        </select>
        <button type="submit">Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Status</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Decided By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr id="row-<?php echo $row['borrow_id']; ?>">
                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['duration']); ?></td>
                    <td><?php echo htmlspecialchars($row['decided_by']); ?></td>
                    <td>
                        <?php if ($row['status'] === 'Approved'): ?>
                            <form action="user-returnrequest.php" method="POST">
                                <input type="hidden" name="borrow_id" value="<?php echo $row['borrow_id']; ?>">
                                <button type="submit">Returned</button>
                            </form>
                        <?php elseif ($row['status'] === 'Not Approved'): ?>
                            <form action="user-cancelrequest.php" method="POST">
                                <input type="hidden" name="borrow_id" value="<?php echo $row['borrow_id']; ?>">
                                <button type="submit">Cancel</button>
                            </form>
                        <?php elseif ($row['status'] === 'Returned'): ?>
                            <button type="button" onclick="markAsDone(<?php echo $row['borrow_id']; ?>)">Done</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php
    // Display items with status 'Returned'
    $returned_sql = "
    SELECT 
    bi.borrow_id,
    CASE 
        WHEN ct.creative_id IS NOT NULL THEN ct.creative_name
        WHEN gm.gadget_id IS NOT NULL THEN gm.gadget_name
        WHEN os.office_id IS NOT NULL THEN os.office_name
        WHEN vo.vendor_id IS NOT NULL THEN vo.item_name
        ELSE 'Unknown'
    END AS item_name,
    bi.status,
    bi.return_date AS duration,
    bi.borrow_date,
    bi.decided_by, 
    CASE 
        WHEN ct.creative_id IS NOT NULL THEN 'Creative Tools'
        WHEN gm.gadget_id IS NOT NULL THEN 'Gadget Monitor'
        WHEN os.office_id IS NOT NULL THEN 'Office Supplies'
        WHEN vo.vendor_id IS NOT NULL THEN 'Vendor Owned'
        ELSE 'Unknown'
    END AS category
    FROM 
    borrowed_items bi
    LEFT JOIN creative_tools ct ON bi.item_id = ct.creative_id
    LEFT JOIN gadget_monitor gm ON bi.item_id = gm.gadget_id
    LEFT JOIN office_supplies os ON bi.item_id = os.office_id
    LEFT JOIN vendor_owned vo ON bi.item_id = vo.vendor_id
    WHERE 
    bi.user_id = ? 
    AND bi.status = 'Returned'
    ";
    $returned_stmt = $conn->prepare($returned_sql);
    $returned_stmt->bind_param("i", $user_id);
    $returned_stmt->execute();
    $returned_result = $returned_stmt->get_result();

    if ($returned_result->num_rows > 0):
    ?>
    <h2>Returned Items</h2>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Status</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Decided By</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($returned_row = $returned_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($returned_row['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($returned_row['category']); ?></td>
                    <td><?php echo htmlspecialchars($returned_row['status']); ?></td>
                    <td><?php echo htmlspecialchars($returned_row['borrow_date']); ?></td>
                    <td><?php echo htmlspecialchars($returned_row['duration']); ?></td>
                    <td><?php echo htmlspecialchars($returned_row['decided_by']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<script>
    function markAsDone(borrowId) {
        document.getElementById('row-' + borrowId).style.display = 'none';
    }
</script>
</body>
</html>
