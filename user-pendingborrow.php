<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

function getBorrowedItemCount($conn, $user_id) {
    $sql = "SELECT COUNT(*) AS total FROM borrowed_items WHERE user_id = ? AND status IN ('Approved', 'Not Approved')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'];

    if ($total > 0) {
        return '<span class="notification-badge">' . $total . '</span>';
    } else {
        return ''; // Return an empty string if there are no pending requests
    }
}

function getPendingItemCount($conn, $user_id) {
    $sql = "SELECT COUNT(*) AS total FROM borrowed_items WHERE user_id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'];

    if ($total > 0) {
        return '<span class="notification-badge">' . $total . '</span>';
    } else {
        return ''; // Return an empty string if there are no pending requests
    }
}

// Fetch pending borrow requests for the logged-in user
// Fetch pending borrow requests for the logged-in user
$user_id = $_SESSION["user_id"];
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
    u.username  -- This fetches the username associated with the user_id
FROM 
    borrowed_items bi
LEFT JOIN creative_tools ct ON bi.item_id = ct.creative_id
LEFT JOIN gadget_monitor gm ON bi.item_id = gm.gadget_id
LEFT JOIN office_supplies os ON bi.item_id = os.office_id
LEFT JOIN vendor_owned vo ON bi.item_id = vo.vendor_id
LEFT JOIN users u ON bi.user_id = u.user_id  -- Joining the users table to fetch the username
WHERE 
    bi.user_id = ?
    AND bi.status = 'Pending'
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // Bind the user ID parameter
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Borrow Requests</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user-css.css">

    <style>
.notification-badge {
    background-color: red;
    color: white;
    padding: 4px 8px;
    border-radius: 50%;
    margin-left: 4px;
}

    .center-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; /* Adjust as needed */
    margin-left: 200px;
    }

    .container button[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.container button[type="submit"]:hover {
    background-color: #45a049;
}

    </style>

</head>
<body>
    <!-- Side Navigation -->
    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/techno.png" alt="Logo" class="logo"></a>
        <a href="user-dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
        <a href="user-borrow.php" class="nav-item "><span class="icon-placeholder"></span>My Request</a>
        <a href="user-pendingborrow.php" class="nav-item active">
    <span class="icon-placeholder"></span>Pending Requests
    <?php echo getPendingItemCount($conn, $_SESSION["user_id"]); ?>
</a>

<a href="user-resultborrow.php" class="nav-item  ">
    <span class="icon-placeholder"></span>My Accountability
    <?php echo getBorrowedItemCount($conn, $_SESSION["user_id"]); ?>
</a>

</div>
<!-- Header box container -->
<div class="header-box">
    <div class="header-box-content">
        <!-- Navigation links -->
        <ul class="nav-links">
            <!-- Display greeting message -->
            <?php if (isset($_SESSION["user_id"])): ?>
                <li>
                    <a href="user-users.php">
                        Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
                    </a>
                </li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

    <div class="center-container">

    <div class="container">
        <h1>Pending Borrow Requests</h1>
        <?php if ($result->num_rows > 0): ?>
        <form action="cancel_request.php" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Borrow Date</th>
                        <th>Duration</th>
                        <th>Username</th> <!-- Changed from User ID to Username -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                        <td><?php echo htmlspecialchars($row['item_name']); ?></td>
<td><?php echo htmlspecialchars($row['category']); ?></td>
<td><?php echo htmlspecialchars($row['status']); ?></td>
<td><?php echo htmlspecialchars($row['borrow_date']); ?></td>
<td><?php echo htmlspecialchars($row['duration']); ?></td>
<td><?php echo htmlspecialchars($row['username']); ?></td> <!-- Displaying the username -->
<td>
    <input type="checkbox" name="borrow_ids[]" value="<?php echo $row['borrow_id']; ?>">
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<button type="submit">Cancel Selected</button>
</form>
<?php else: ?>
<p>No pending borrow requests</p>
<?php endif; ?>
</div>
</div>

</body>
</html>
