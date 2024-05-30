<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Check if borrow_id is set
if (isset($_POST['borrow_id'])) {
    $borrow_id = $_POST['borrow_id'];

    // Update the status of the borrowed item
    $sql = "UPDATE borrowed_items SET status = 'Done' WHERE borrow_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $borrow_id);

    if ($stmt->execute()) {
        // Redirect back to the result page with a success message
        header("Location: user-resultborrow.php?status=Returned&message=done");
    } else {
        // Redirect back with an error message
        header("Location: user-resultborrow.php?status=Returned&message=error");
    }

    $stmt->close();
} else {
    // Redirect back with an error message
    header("Location: user-resultborrow.php?status=Returned&message=missing");
}

$conn->close();
?>
