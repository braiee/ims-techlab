<?php
session_start();
include 'db-connect.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Fetch borrowed items status for the logged-in user
$user_id = $_SESSION["user_id"];
$sql = "
    SELECT 
        bi.borrow_id,
        bi.item_name,
        bi.status,
        bi.return_date AS duration,
        bi.borrow_date,
        CASE 
            WHEN ct.creative_id IS NOT NULL THEN 'Creative Tools'
            WHEN gm.gadget_id IS NOT NULL THEN 'Gadget Monitor'
            WHEN os.office_id IS NOT NULL THEN 'Office Supplies'
            WHEN vo.vendor_id IS NOT NULL THEN 'Vendor Owned'
            ELSE 'Unknown'
        END AS category
    FROM 
        borrowed_items bi
    LEFT JOIN creative_tools ct ON bi.item_name = ct.creative_name
    LEFT JOIN gadget_monitor gm ON bi.item_name = gm.gadget_name
    LEFT JOIN office_supplies os ON bi.item_name = os.office_name
    LEFT JOIN vendor_owned vo ON bi.item_name = vo.item_name
    WHERE 
        bi.user_id = ?
        AND bi.status IN ('Approved', 'Not Approved', 'Returned')
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
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
    <link rel="stylesheet" href="css/category.css">
</head>
<body>
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
        <a href="user-dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
        <a href="user-borrow.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
        <a href="user-pendingborrow.php" class="nav-item"><span class="icon-placeholder"></span>Pending</a>
        <a href="user-resultborrow.php" class="nav-item"><span class="icon-placeholder"></span>Result</a>
        <span class="non-clickable-item">Settings</span>
        <a href="users.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>
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
 
    <h1>Borrowed Items Result</h1>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Status</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['item_name']; ?></td>
                    <td><?php echo $row['category']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['borrow_date']; ?></td>
                    <td><?php echo $row['duration']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
