<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if borrow_id, item_name, and username are set in the POST request
if (isset($_POST['borrow_id'], $_POST['item_name'], $_POST['username'])) {
    $borrow_id = $_POST['borrow_id'];
    $item_name = $_POST['item_name'];
    $username = $_POST['username'];
    
    // Check if the item has already been received
    $check_received_sql = "SELECT * FROM returned_items_history WHERE borrow_id = ?";
    $stmt_check = $conn->prepare($check_received_sql);
    $stmt_check->bind_param("i", $borrow_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // Item has already been received, redirect back to the previous page
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); // Exit the script
    }
    
    // Fetch the username of the person who borrowed the item
    $borrower_username = "";
    $fetch_borrower_sql = "SELECT u.username FROM borrowed_items AS bi JOIN users AS u ON bi.user_id = u.user_id WHERE bi.borrow_id = ?";
    $stmt_fetch = $conn->prepare($fetch_borrower_sql);
    $stmt_fetch->bind_param("i", $borrow_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    
    if ($result_fetch->num_rows > 0) {
        $row = $result_fetch->fetch_assoc();
        $borrower_username = $row['username'];
    }
    
    // Define the received_date as the current timestamp
    $received_date = date("Y-m-d H:i:s");
    
    // Insert the record into the returned_items_history table
    $insert_history_sql = "INSERT INTO returned_items_history (borrow_id, item_name, username, received_by, received_date) VALUES (?, ?, ?, ?, ?)";
    $stmt_history = $conn->prepare($insert_history_sql);
    $stmt_history->bind_param("issss", $borrow_id, $item_name, $borrower_username, $_SESSION['username'], $received_date);
    
    if ($stmt_history->execute()) {
        // Redirect back to the previous page
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); // Exit the script
    } else {
        // Handle the insert failure
        echo "Error recording returned item: " . $conn->error;
    }
} else {
    // Handle the case when borrow_id, item_name, or username is not provided
    echo "Error: Missing required parameters.";
}
?>
