<?php
session_start(); // Start the session at the beginning of the file

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['identity'] != 1) {
    // If not logged in or not an admin, redirect to the login page or show an error message
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db-connect.php';

// Function to add a new user
function addUser($user_id, $username, $password) {
    global $conn;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $identity = 0; // Regular user
    $sql = "INSERT INTO users (user_id, username, password, identity) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $user_id, $username, $hashed_password, $identity);
    return $stmt->execute();
}

// Function to change user credentials
function changeUserCredentials($user_id, $new_username, $new_password) {
    global $conn;
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET username=?, password=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_username, $hashed_password, $user_id);
    return $stmt->execute();
}

// Function to delete users
function deleteUsers($user_ids) {
    global $conn;
    $user_ids_array = explode(',', $user_ids);
    $sql = "DELETE FROM users WHERE id IN (";
    $sql .= str_repeat("?,", count($user_ids_array) - 1) . "?)";
    $stmt = $conn->prepare($sql);
    $types = str_repeat("i", count($user_ids_array)); // Types string for bind_param
    $stmt->bind_param($types, ...$user_ids_array); // Use splat operator to unpack array into arguments
    return $stmt->execute();
}

// Check if user_ids parameter is set in the URL
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action == 'add') {
        // Handle adding a new user
        if (isset($_POST['new_user_id']) && isset($_POST['new_user_username']) && isset($_POST['new_user_password'])) {
            if (addUser($_POST['new_user_id'], $_POST['new_user_username'], $_POST['new_user_password'])) {
                header("Location: users.php?success=add");
                exit();
            } else {
                echo "Error adding new user: " . $conn->error;
            }
        }
    } elseif ($action == 'change') {
        // Handle changing user credentials
        if (isset($_POST['user_id']) && isset($_POST['new_username']) && isset($_POST['new_password'])) {
            if (changeUserCredentials($_POST['user_id'], $_POST['new_username'], $_POST['new_password'])) {
                header("Location: users.php?success=change");
                exit();
            } else {
                echo "Error changing user credentials: " . $conn->error;
            }
        }
    } elseif ($action == 'delete') {
        // Handle deleting users
        if (isset($_GET['user_ids'])) {
            if (deleteUsers($_GET['user_ids'])) {
                header("Location: users.php?success=delete");
                exit();
            } else {
                echo "Error deleting users: " . $conn->error;
            }
        }
    }
}

// Redirect to users page if action parameter is not set
header("Location: users.php");
exit();
?>
