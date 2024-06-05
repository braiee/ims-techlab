<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if borrow_id is set in the POST request
if (isset($_POST['borrow_id'])) {
    $borrow_id = $_POST['borrow_id'];
    
    // Update the status of items to 'Available'
    $update_items_sql = "UPDATE creative_tools SET status = 'Available' WHERE creative_id IN (SELECT item_id FROM borrowed_items WHERE borrow_id = ?)";
    $stmt_items = $conn->prepare($update_items_sql);
    $stmt_items->bind_param("i", $borrow_id);
    $stmt_items->execute();
    $stmt_items->close();
    
    $update_items_sql = "UPDATE gadget_monitor SET status = 'Available' WHERE gadget_id IN (SELECT item_id FROM borrowed_items WHERE borrow_id = ?)";
    $stmt_items = $conn->prepare($update_items_sql);
    $stmt_items->bind_param("i", $borrow_id);
    $stmt_items->execute();
    $stmt_items->close();

            
    $update_items_sql = "UPDATE office_supplies SET status = 'Available' WHERE office_id IN (SELECT item_id FROM borrowed_items WHERE borrow_id = ?)";
    $stmt_items = $conn->prepare($update_items_sql);
    $stmt_items->bind_param("i", $borrow_id);
    $stmt_items->execute();
    $stmt_items->close();

            
    $update_items_sql = "UPDATE vendor_owned SET status = 'Available' WHERE vendor_id IN (SELECT item_id FROM borrowed_items WHERE borrow_id = ?)";
    $stmt_items = $conn->prepare($update_items_sql);
    $stmt_items->bind_param("i", $borrow_id);
    $stmt_items->execute();
    $stmt_items->close();

    // Update the borrow request status to 'Received'
    $update_sql = "UPDATE borrowed_items SET status = 'Available' WHERE borrow_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $borrow_id);
    
    if ($stmt->execute()) {
        // Redirect back to the previous page
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); // Exit the script
    } else {
        // Handle the update failure
        echo "Error updating status: " . $conn->error;
    }
} else {
    // Handle the case when borrow_id is not provided
    echo "Error: Missing required parameter.";
}
?>
