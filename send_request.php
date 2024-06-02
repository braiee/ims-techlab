<?php
session_start();
include 'db-connect.php'; // Include your database connection script

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the user is logged in
    if (!isset($_SESSION["user_id"])) {
        // Redirect the user to the login page if not logged in
        header("Location: login.php");
        exit();
    }

    // Retrieve the item name from the form
    $item_name = $_POST['item_name'];

    // Insert the borrow request into the borrowed_items table
    $user_id = $_SESSION["user_id"];
    $borrow_date = date("Y-m-d H:i:s"); // Current date and time
    $status = 'Pending';

    $sql = "INSERT INTO borrowed_items (user_id, item_name, borrow_date, status) 
            VALUES ('$user_id', '$item_name', '$borrow_date', '$status')";

    if ($conn->query($sql) === TRUE) {
        // Borrow request successfully submitted
        header("Location: user-borrow.php?status=success");
    } else {
        // Error occurred while submitting borrow request
        header("Location: user-borrow.php?status=error");
    }

    $conn->close();
}
?>
