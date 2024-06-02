<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Check if form is submitted and borrow IDs are provided
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["borrow_ids"])) {
    // Get the list of borrow IDs from the POST request
    $borrow_ids = $_POST["borrow_ids"];

    // Prepare and execute SQL to update the status of the selected borrow requests to "Cancelled"
    $sql = "UPDATE borrowed_items SET status = 'Cancelled' WHERE borrow_id IN (";
    $placeholders = rtrim(str_repeat('?, ', count($borrow_ids)), ', ');
    $sql .= $placeholders . ")";
    $stmt = $conn->prepare($sql);

    // Bind parameters dynamically
    $types = str_repeat('i', count($borrow_ids)); // Assuming borrow_id is integer
    $stmt->bind_param($types, ...$borrow_ids);
    $stmt->execute();

    // Redirect back to user-pendingborrow.php after cancellation
    header("Location: user-pendingborrow.php");
    exit();
} else {
    // If borrow_ids are not provided, redirect to the pending borrow requests page
    header("Location: user-pendingborrow.php");
    exit();
}
?>
