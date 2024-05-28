<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db-connect.php';

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Fetch user's data
$user_id = $_SESSION['user_id'];
$sql = "SELECT user_id, username FROM users WHERE id='$user_id'";
$userResult = $conn->query($sql);
$userData = $userResult->fetch_assoc();

// Check if form is submitted (for changing user credentials)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_credentials'])) {
    // Get new credentials from the form
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the user's credentials in the database
    $sql = "UPDATE users SET username='$new_username', password='$hashed_password' WHERE id='$user_id'";
    if ($conn->query($sql) === TRUE) {
        $successMessage = "Your credentials have been updated successfully.";
    } else {
        $errorMessage = "Error updating your credentials: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/DasHbOARd.css">
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
        <h1>User Dashboard</h1>
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
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $userData['user_id']; ?></td>
                    <td><?php echo $userData['username']; ?></td>
                </tr>
            </tbody>
        </table>
        <h2>Change Credentials</h2>
        <form action="" method="post">
            <input type="text" name="new_username" placeholder="New Username" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit" name="change_credentials">Change Credentials</button>
        </form>
    </div>
</body>
</html>
