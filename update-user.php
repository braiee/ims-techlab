<?php
session_start();

include 'db-connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Validate form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are set
    if (isset($_POST["username"]) && isset($_POST["password"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update the user's username and password in the database
        $sql = "UPDATE users SET username = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $username, $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            // Redirect to user settings page with success message
            header("Location: user-users.php?successMessage=Your username and password have been updated successfully.");
            exit();
        } else {
            // Redirect to user settings page with error message
            header("Location: user-users.php?errorMessage=Failed to update your username and password. Please try again.");
            exit();
        }
    } else {
        // Redirect to user settings page with error message
        header("Location: user-users.php?errorMessage=Username and password are required.");
        exit();
    }
} else {
    // Redirect to user settings page with error message
    header("Location: user-users.php?errorMessage=Invalid request method.");
    exit();
}
?>
