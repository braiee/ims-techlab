<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Check if the user is an admin
if ($_SESSION['identity'] != 1) {
    // If not an admin, redirect to the dashboard or show an error message
    // header("Location: dashboard.php");
    // exit();
    echo "You do not have permission to access this page.";
    exit();
}

// Include database connection
include 'db-connect.php';

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Fetch users data
$sql = "SELECT id, user_id, username, identity FROM users";
$usersResult = $conn->query($sql);

// Check if form is submitted (for changing user credentials)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_credentials'])) {
    // Handle change credentials action
    // Get user ID and new credentials from the form
    $user_id = $_POST['user_id'];
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the user's credentials in the database
    $sql = "UPDATE users SET username='$new_username', password='$hashed_password' WHERE id='$user_id'";
    if ($conn->query($sql) === TRUE) {
        $successMessage = "User credentials updated successfully.";
    } else {
        $errorMessage = "Error updating user credentials: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
        <a href="dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
        <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
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
        <h1>User Management</h1>
        <div class="message-container">
            <?php
            if (!empty($successMessage)) {
                echo '<div class="success-message">' . $successMessage . '</div>';
            } elseif (!empty($errorMessage)) {
                echo '<div class="error-message">' . $errorMessage . '</div>';
            }
            ?>
        </div>
        <table class="user-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <?php
                    // Display action column only for admin users
                    if ($_SESSION['username'] === 'admin') {
                        echo '<th>Action</th>';
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Output data of each user
                while($user = $usersResult->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $user["user_id"] . '</td>';
                    echo '<td>' . $user["username"] . '</td>';
                    echo '<td>' . ($user["identity"] == 1 ? 'Admin' : 'Regular User') . '</td>';
                    // Display action buttons only for admin users
                    if ($_SESSION['username'] === 'admin') {
                        echo '<td>';
                        // Allow admin to change credentials of other users
                        if ($user["id"] != $_SESSION['id']) {
                            echo '<form action="" method="post">';
                            echo '<input type="hidden" name="user_id" value="' . $user["id"] . '">';
                            echo '<input type="text" name="new_username" placeholder="New Username">';
                            echo '<input type="password" name="new_password" placeholder="New Password">';
                            echo '<button type="submit" name="change_credentials">Change Credentials</button>';
                            echo '</form>';
                        }
                        echo '</td>';
                    }
                    echo '</tr>';
                }
                ?>
                </tbody>
                </table>
                </div>
                
                </body>
                </html>
