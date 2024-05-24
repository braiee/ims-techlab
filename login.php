<?php
session_start(); // Start the session at the beginning of the file

include 'db-connect.php';

// Initialize error variable
$error = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if username and password are set
    if (!empty($_POST["username"]) && !empty($_POST["password"])) {
        
        // Sanitize username and password (additional validation can be added here)
        $username = $_POST["username"];
        $password = $_POST["password"];

        // SQL query to check if the username exists
        $sql = "SELECT id, user_id, username, password FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Username exists, now check the password
            $user = $result->fetch_assoc();
            if ($user['password'] === $password) {
                // Login successful, store user ID and username in session
                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["username"] = $user["username"];
                header("Location: dashboard.php");
                exit();
            } else {
                // Invalid password
                $error = "Invalid password!";
            }
        } else {
            // Username does not exist
            $error = "Account does not exist!";
        }
    } else {
        // Username or password not set
        $error = "Please enter both username and password!";
    }

    // Store error in session if there is an error
    if ($error) {
        $_SESSION['error'] = $error;
        // Redirect to the login page
        header("Location: login.php");
        exit();
    }
} 

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/Main1.css">
    <title>SmartTrack</title>
</head>
<body>

<div class="container">
    <!-- Info Section -->
    <div class="info-section">
        <img src="assets/img/smarttrack.png" alt="Logo" class="logo">
    </div>
    <!-- Login Section -->
    <div class="login-section">
        <div class="form-container">
            <h1>Login</h1>
            <!-- Display the error message if it exists -->
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="error-box"><p class="error">' . $_SESSION['error'] . '</p></div>';
                // Clear the error message from the session
                unset($_SESSION['error']);
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Enter username">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter password">
                <button type="submit" class="assign-button">Login</button>
                <div class="forgot-password-wrapper">
                    <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
