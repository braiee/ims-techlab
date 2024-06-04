<?php
session_start();

include 'db-connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch user's data
$sql = "SELECT user_id, username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Initialize message variables
$successMessage = isset($_GET['successMessage']) ? $_GET['successMessage'] : "";
$errorMessage = isset($_GET['errorMessage']) ? $_GET['errorMessage'] : "";
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



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings</title>
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
.container input[type="text"],
.container input[type="password"],
.container button[type="submit"] {
    padding: 12px;
    margin-bottom: 20px;
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
}

.container input[type="text"]:focus,
.container input[type="password"]:focus,
.container button[type="submit"]:focus {
    border-color: #4CAF50;
    outline: none;
}

.container button[type="submit"] {
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s;
}

.container button[type="submit"]:hover {
    background-color: #45a049;
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


    </style>
</head>
<body>

    <!-- Side Navigation -->
    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/techno.png" alt="Logo" class="logo"></a>
        <a href="user-dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
        <a href="user-borrow.php" class="nav-item "><span class="icon-placeholder"></span>My Request</a>
        <a href="user-pendingborrow.php" class="nav-item ">
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
            <div class="dropdown">
        <button class="dropdown-btn">Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
</button>
        <ul class="nav-links dropdown-content">
            <!-- Display greeting message -->
            <?php if (isset($_SESSION["user_id"])): ?>
                <li>
                    <a href="user-users.php">
                        Settings    
                    </a>
                </li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
        </div>
        </div>
    </div>

    <div class="center-container">

    <div class="container">
        <h1>User Settings</h1>

        <!-- Success and Error Messages -->
        <?php if ($successMessage != ""): ?>
            <div class="success-message"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if ($errorMessage != ""): ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- User Info Form -->
        <form action="update-user.php" method="POST">
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            <label for="password">New Password:</label><br>
            <input type="password" id="password" name="password" placeholder="Enter new password" required>
            <button type="submit">Save Changes</button>
        </form>
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
