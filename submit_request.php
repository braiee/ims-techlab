<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if category, item_id, and return_date are set in the POST request
if (isset($_POST['category'], $_POST['item_id'], $_POST['return_date'], $_POST['user_id'])) {
    $category = $_POST['category'];
    $item_id = $_POST['item_id'];
    $return_date = $_POST['return_date'];
    $user_id = $_POST['user_id']; // Retrieve user_id from POST data
    $borrow_date = date("Y-m-d H:i:s");
    $status = 'Pending';

    // Prepare the SQL statement
    $sql = "INSERT INTO borrowed_items (user_id, item_id, category, borrow_date, return_date, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("iissss", $user_id, $item_id, $category, $borrow_date, $return_date, $status);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Borrow request sent.";
        } else {
            echo "Error: Unable to execute the statement.";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "Error: Missing required parameters.";
}

// Close the database connection
$conn->close();
?>
