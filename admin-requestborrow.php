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

function getTotalFetchRequestCount($conn) {
    $sql = "SELECT COUNT(*) AS total FROM borrowed_items WHERE status = 'Returned'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total = $row['total'];

    if ($total > 0) {
        return '<span class="notification-badge">' . $total . '</span>';
    } else {
        return ''; // Return an empty string if there are no items awaiting approval
    }
}


function getTotalItemCount($conn, $table_name) {
    $sql = "SELECT COUNT(*) AS total FROM $table_name WHERE status = 'Pending'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total = $row['total'];

    if ($total > 0) {
        return '<span class="notification-badge">' . $total . '</span>';
    } else {
        return ''; // Return an empty string if there are no items awaiting approval
    }
}



// Fetch all pending borrow requests with user names
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
    bi.status = 'Pending'
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation Borrow Requests (Admin)</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user-css.css">
    <style>
        .container{
            width: 100%;
            margin: 0 auto;
            margin-left: 100px;
        }
        .notification-badge {
    background-color: red;
    color: white;
    padding: 4px 8px;
    border-radius: 50%;
    margin-left: 4px;
}
        .notification-badge {
    background-color: red;
    color: white;
    padding: 4px 8px;
    border-radius: 50%;
    margin-left: 4px;
}
                /* Button styles */
                .container button[type="submit"] {
            background-color: #4CAF50; /* Green */
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            transition-duration: 0.4s;
            cursor: pointer;
            border-radius: 8px;
        }

        /* Button hover effect */
        .container button[type="submit"]:hover {
            background-color: #45a049; /* Darker green */
            color: white;
        }

        .dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-btn {
    background-color: #C7E8CA;
    color: #5D9C59;
    padding: 14px 20px;
    border: none;
    cursor: pointer;
    font-size: 20px;
    text-align: center;
    width: 100%;
    margin-top: -10px;
    margin-right: 10px;
}

.dropdown-btn:hover {
    transform: translateY(-5px); /* Slight lift effect on hover */
    transition: transform 0.2s;
}

.nav-links {
    list-style-type: none;
    margin: 0;
    padding: 0;
    display: none;
    flex-direction: column;
    align-items: flex-start;
    background-color: #C7E8CA;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    z-index: 1;
}

.nav-links li {
    margin: 0;
    padding: 0;
    width: 70%;
}

.nav-links a {
    display: block;
    color: #5D9C59;
    text-align: center;
    padding: 14px 20px;
    text-decoration: none;
    transition: transform 0.2s;
    width: 100%;
    font-size: 16px;
}

.nav-links a:hover {
    transform: translateY(-5px); /* Slight lift effect on hover */
}

.show {
    display: flex;
}


th
{
        color: #5D9C59;
    }
    </style>
</head>
<body>
   <!-- Side Navigation -->
   <div class="side-nav">
    <a href="#" class="logo-link">        <img src="assets/img/techno.png" alt="Logo" class="logo">
</a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Borrow</span>
        <a href="admin-borrow.php" class="nav-item "><span class="icon-placeholder"></span>Requests</a>
        <a href="admin-requestborrow.php" class="nav-item active ">
    <span class="icon-placeholder"></span>Approval
    <?php
    // Get the total count of items awaiting approval
    $totalItems = getTotalItemCount($conn, 'borrowed_items');
    // Display the total count with a notification badge
    echo $totalItems;
    ?>
</a>
<a href="admin-fetchrequest.php" class="nav-item <?php echo ($_SERVER['PHP_SELF'] == '/admin-fetchrequest.php') ? 'active' : ''; ?>">
    <span class="icon-placeholder"></span>Returned
    <?php echo getTotalFetchRequestCount($conn); ?>
</a>    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item"><span class="icon-placeholder"></span>Gadgets/Devices</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
        <span class="non-clickable-item">Settings</span>
    <a href="deleted_items.php" class="nav-item"><span class="icon-placeholder"></span>Bin</a>

</div>
<!-- Header box container -->
<div class="header-box">
    <div class="header-box-content">
        <!-- Navigation links -->
        <!-- Navigation links -->
        <div class="dropdown">
        <button class="dropdown-btn">Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
</button>
        <ul class="nav-links dropdown-content">
            <!-- Display greeting message -->
            <?php if (isset($_SESSION["user_id"])): ?>
                <li>
                    <a href="users.php">
                        Settings    
                    </a>
                </li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
    </div>
</div>


<div class="center-container" style=" height:540px; "> 
    <div class="container" >
    
        <h1 style="color:#5D9C59;">Approval of Requests</h1>
        <div class="message-container">
                <?php
                if (!empty($successMessage)) {
                    echo '<div class="success-message">' . $successMessage . '</div>';
                } elseif (!empty($errorMessage)) {
                    echo '<div class="error-message">' . $errorMessage . '</div>';
                }
                ?>
        </div>

        <!-- Modify the table -->
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                    <th>Actions</th> <!-- New column for actions -->
                </tr>
            </thead>
            <tbody>
            <?php
// Flag to indicate if any records were found
$recordsFound = false;

while ($row = $result->fetch_assoc()):
  $recordsFound = true; // Set flag to true if there's at least one record
  ?>
  <tr>
    <td><?php echo htmlspecialchars($row['username']); ?></td>
    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
    <td><?php echo htmlspecialchars($row['category']); ?></td>
    <td><?php echo htmlspecialchars($row['status']); ?></td>
    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['borrow_date']))); ?></td>
    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['duration']))); ?></td>
    <td>
      <?php if ($row['status'] === 'Pending'): ?>
        <form action="update_status.php" method="POST">
          <input type="hidden" name="borrow_id" value="<?php echo $row['borrow_id']; ?>">
          <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
          <input type="hidden" name="username" value="<?php echo $_SESSION['username']; ?>">
          <button type="submit" name="approve">Approve</button>
          <button type="submit" name="reject">Decline</button>
        </form>
      <?php endif; ?>
    </td>
  </tr>
<?php endwhile; ?>

<?php if (!$recordsFound): ?>
  <tr>
    <td colspan="7" style="color: red; font-weight: bold; text-align: center;">No approval requests found.</td>
  </tr>
<?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
    const dropdownBtn = document.querySelector('.dropdown-btn');
    const dropdownContent = document.querySelector('.dropdown-content');

    dropdownBtn.addEventListener('click', () => {
        dropdownContent.classList.toggle('show');
    });

    // Close the dropdown if the user clicks outside of it
    window.addEventListener('click', (event) => {
        if (!event.target.matches('.dropdown-btn')) {
            if (dropdownContent.classList.contains('show')) {
                dropdownContent.classList.remove('show');
            }
        }
    });
});

</script>

</body>
</html>