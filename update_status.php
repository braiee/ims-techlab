<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if borrow_id, user_id, and username are set in the POST request
if (isset($_POST['borrow_id'], $_POST['user_id'], $_POST['username'])) {
    $borrow_id = $_POST['borrow_id'];
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    
    // Determine decision based on the presence of 'approve' or 'reject' in the POST data
    if (isset($_POST['approve'])) {
        $decision = 'Approved';
    } elseif (isset($_POST['reject'])) {
        $decision = 'Not Approved';
    } else {
        // Handle the case when neither 'approve' nor 'reject' is set
        echo "Error: Missing decision.";
        exit(); // Exit the script
    }
    
    // Update the borrow request status and decided_by column
    $update_sql = "UPDATE borrowed_items SET status = ?, decided_by = ? WHERE borrow_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $decision, $username, $borrow_id);
    
    if ($stmt->execute()) {
        // Redirect to the borrow requests page
        header("Location: admin-requestborrow.php");
        exit(); // Exit the script
    } else {
        // Handle the update failure
        echo "Error updating status: " . $conn->error;
    }
} else {
    // Handle the case when borrow_id, user_id, or username is not provided
    echo "Error: Missing required parameters.";
}
?>
