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

    </style>
</head>
<body>

        <!-- Side Navigation -->
        <div class="side-nav">
        <a href="#" class="logo-link">        <img src="assets/img/techno.png" alt="Logo" class="logo">
</a>
        <a href="user-dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
        <a href="user-borrow.php" class="nav-item "><span class="icon-placeholder"></span>Borrow</a>
        <a href="user-pendingborrow.php" class="nav-item"><span class="icon-placeholder"></span>Pending</a>
        <a href="user-resultborrow.php" class="nav-item"><span class="icon-placeholder"></span>Result</a>
        <span class="non-clickable-item">Settings</span>
        <a href="user-users.php" class="nav-item active"><span class="icon-placeholder"></span>Users</a>
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

</body>
</html>
