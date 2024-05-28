<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['identity'] != 1) {
    // If not logged in or not an admin, redirect to the login page or show an error message
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db-connect.php';

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    $new_user_id = $_POST['new_user_id'];
    $new_user_username = $_POST['new_user_username'];
    $new_user_password = $_POST['new_user_password'];

    // Check if user ID is unique
    $checkUserQuery = "SELECT id FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($checkUserQuery);
    $stmt->bind_param("s", $new_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errorMessage = "User ID already exists. Please choose a different one.";
    } else {
        // Hash the new user's password
        $hashed_user_password = password_hash($new_user_password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $identity = 0; // Regular user
        $sql = "INSERT INTO users (user_id, username, password, identity) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $new_user_id, $new_user_username, $hashed_user_password, $identity);

        if ($stmt->execute()) {
            $successMessage = "New user added successfully.";
        } else {
            $errorMessage = "Error adding new user: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="container">
        <h1>Add New User</h1>
        <!-- Display success or error messages -->
        <?php
        if (!empty($successMessage)) {
            echo '<div class="success-message">' . $successMessage . '</div>';
        } elseif (!empty($errorMessage)) {
            echo '<div class="error-message">' . $errorMessage . '</div>';
        }
        ?>
        <!-- User Registration Form -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="new_user_id">User ID:</label>
            <input type="text" id="new_user_id" name="new_user_id" required>
            <label for="new_user_username">Username:</label>
            <input type="text" id="new_user_username" name="new_user_username" required>
            <label for="new_user_password">Password:</label>
            <input type="password" id="new_user_password" name="new_user_password" required>
            <button type="submit" name="add_user">Add User</button>
        </form>
        <a href="users.php">Back to Users Page</a>
    </div>
</body>
</html>
