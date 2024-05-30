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
        // Delete the request
        $delete_sql = "DELETE FROM borrowed_items WHERE borrow_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $borrow_id);
        
        if ($delete_stmt->execute()) {
            header("Location: user-resultborrow.php");
            exit();
        } else {
            echo "Error deleting request";
        }
    } else {
        echo "You are not authorized to delete this request.";
    }
} else {
    echo "Invalid request";
}
?>
