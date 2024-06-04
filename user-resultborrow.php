<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

function getReminderCount($conn, $user_id) {
    // Define the intervals in days
    $intervals = [10, 5, 3, 1, 0]; // 0 represents the same day
    
    // Initialize reminder count
    $reminder_count = 0;

    // Iterate through intervals and check for reminders
    foreach ($intervals as $interval) {
        // Calculate the date after the interval
        $reminder_date = date('Y-m-d', strtotime("+$interval days"));

        // Prepare the SQL query to count reminders within the interval
        $sql = "SELECT COUNT(*) AS reminder_count 
                FROM borrowed_items 
                WHERE user_id = ? 
                AND return_date = DATE_ADD(CURDATE(), INTERVAL ? DAY)";
        
        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $interval);
        $stmt->execute();
        
        // Get the result
        $result = $stmt->get_result();
        
        // Fetch the count from the result
        $row = $result->fetch_assoc();
        $reminder_count += $row['reminder_count'];
    }
    
    // Return the total reminder count
    return $reminder_count;
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


// Fetch borrow requests for the logged-in user based on the status filter
$user_id = $_SESSION["user_id"];
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$statusCondition = '';
$statusValues = ['Approved', 'Not Approved']; // Define valid status values excluding 'Returned'

if (!empty($statusFilter) && in_array($statusFilter, $statusValues)) {
    $statusCondition = "AND bi.status = ?";
}

$user_id = $_SESSION["user_id"];
$pending_requests_count = getBorrowedItemCount($conn, $user_id);

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

    <style>
            /* Style for select element */
    select {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 150px; /* Adjust width as needed */
        cursor: pointer;
    }

    /* Style for filter button */
    button[type="submit"] {
        padding: 8px 16px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    /* Hover effect for filter button */
    button[type="submit"]:hover {
        background-color: #45a049;
    }

.notification-badge {
    background-color: red;
    color: white;
    padding: 4px 8px;
    border-radius: 50%;
    margin-left: 4px;
}
        .container{
            width: 125%;
        }

        .table-container {
    max-height: 400px; /* Set a maximum height for the table container */
    overflow-y: auto; /* Enable vertical scrolling */
}

    </style>

</head>
<body>
    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/techno.png" alt="Logo" class="logo"></a>
        <a href="user-dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
        <a href="user-borrow.php" class="nav-item "><span class="icon-placeholder"></span>My Request</a>
        <a href="user-pendingborrow.php" class="nav-item ">
    <span class="icon-placeholder"></span>Pending Requests
    <?php echo getPendingItemCount($conn, $_SESSION["user_id"]); ?>
</a>

<a href="user-resultborrow.php" class="nav-item active ">
    <span class="icon-placeholder"></span>My Accountability
    <?php echo getBorrowedItemCount($conn, $_SESSION["user_id"]); ?>
</a>

</div>

    </div>
<!-- Header box container -->
<div class="header-box">
        <div class="header-box-content">
            <!-- Navigation links -->
            <ul class="nav-links">
                <li>
                    <a href="user-users.php">
                        Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
                    </a>
                </li>
                <li><a href="logout.php">Logout</a></li>
                        </ul>
        </div>
    </div>
    <div class="center-container">

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
    <div class="table-container">

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
    </div>

    <!-- Modal -->
<div id="reminderModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Reminder: Item Deadlines</h2>
        <div id="reminder-content">
            <!-- Reminders will be dynamically added here -->
        </div>
    </div>
</div>

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
    <div class="table-container">

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

</div>
</div>



<script>
    function markAsDone(borrowId) {
        document.getElementById('row-' + borrowId).style.display = 'none';
    }

    // Get the modal
var modal = document.getElementById("reminderModal");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on the notification count, open the modal
document.getElementById("notification-count").onclick = function() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var reminderContent = this.responseText;
            document.getElementById("reminder-content").innerHTML = reminderContent;
            modal.style.display = "block";
        }
    };
    xhttp.open("GET", "get_reminder_content.php", true);
    xhttp.send();
}


// Inside the modal-content div, replace the placeholder with actual reminders


// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}


</script>
</body>
</html>
