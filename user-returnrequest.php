<?php
session_start();
include 'db-connect.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["borrow_id"])) {
    $borrow_id = $_POST["borrow_id"];
    
    // Check if the borrow request belongs to the logged-in user
    $user_id = $_SESSION["user_id"];
    $check_sql = "SELECT * FROM borrowed_items WHERE borrow_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $borrow_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update the status to Returned
        $update_sql = "UPDATE borrowed_items SET status = 'Returned' WHERE borrow_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $borrow_id);
        
        if ($update_stmt->execute()) {
            header("Location: user-resultborrow.php");
            exit();
        } else {
            echo "Error updating status";
        }
    } else {
        echo "You are not authorized to return this item.";
    }
} else {
    echo "Invalid request";
}
?>
