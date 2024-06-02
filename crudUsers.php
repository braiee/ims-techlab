<?php
session_start(); // Start the session at the beginning of the file

// Include database connection
include 'db-connect.php';

// Handle add user function
if (isset($_POST['add_user'])) {
    // Retrieve data from form
    $new_user_id = $_POST['new_user_id'];
    $new_user_username = $_POST['new_user_username'];
    $new_user_password = $_POST['new_user_password'];

    // Hash the new user's password
    $hashed_user_password = password_hash($new_user_password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $identity = 0; // Regular user
    $sql = "INSERT INTO users (user_id, username, password, identity) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $new_user_id, $new_user_username, $hashed_user_password, $identity);

    $stmt->execute();
}

// Handle edit user function
if (isset($_POST['edit_user_credentials'])) {
    // Retrieve data from form
    $edit_user_id = $_POST['edit_user_id'];
    $edit_username = $_POST['edit_username'];
    $edit_password = $_POST['edit_password'];

    // Hash the new password
    $hashed_password = password_hash($edit_password, PASSWORD_DEFAULT);

    // Update the user's credentials in the database
    $sql = "UPDATE users SET username=?, password=? WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $edit_username, $hashed_password, $edit_user_id);

    $stmt->execute();
}

// Handle delete user function
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['user_ids'])) {
    // Retrieve user IDs from query parameter
    $user_ids = explode(',', $_GET['user_ids']);

    // Prepare SQL statement for deleting users
    $sql = "DELETE FROM users WHERE user_id IN (" . implode(',', $user_ids) . ")";

    // Execute SQL statement
    $conn->query($sql);
}

// Redirect back to the previous page
header("Location: {$_SERVER['HTTP_REFERER']}");
exit();
?>
