<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["borrow_ids"])) {
    // Get the list of borrow IDs from the POST request
    $borrow_ids = $_POST["borrow_ids"];

    // Loop through each borrow ID
    foreach ($borrow_ids as $borrow_id) {
        // Get the category and item ID associated with the borrow ID
        $sql_item_info = "SELECT category, item_id FROM borrowed_items WHERE borrow_id = ?";
        $stmt_item_info = $conn->prepare($sql_item_info);
        $stmt_item_info->bind_param("i", $borrow_id);
        $stmt_item_info->execute();
        $stmt_item_info->bind_result($category, $item_id);
        $stmt_item_info->fetch();
        $stmt_item_info->close();

        // Determine the table and column names based on category
        switch ($category) {
            case "Creative Tools":
                $table = "creative_tools";
                $id_column = "creative_id";
                break;
            case "Gadget Monitor":
                $table = "gadget_monitor";
                $id_column = "gadget_id";
                break;
            case "Office Supplies":
                $table = "office_supplies";
                $id_column = "office_id";
                break;
            default:
                echo "Invalid category";
                exit();
        }

        // Update the status of the borrowed item to 'Available'
        $sql_update_borrowed_item = "UPDATE borrowed_items SET status = 'Available' WHERE borrow_id = ?";
        $stmt_update_borrowed_item = $conn->prepare($sql_update_borrowed_item);
        $stmt_update_borrowed_item->bind_param("i", $borrow_id);
        $stmt_update_borrowed_item->execute();
        $stmt_update_borrowed_item->close();

        // Update the status of the item to 'Available' in the respective table
        $sql_update_item = "UPDATE $table SET status = 'Available' WHERE $id_column = ?";
        $stmt_update_item = $conn->prepare($sql_update_item);
        $stmt_update_item->bind_param("i", $item_id);
        $stmt_update_item->execute();
        $stmt_update_item->close();
    }

    // Close connection
    $conn->close();

    // Redirect back to user-pendingborrow.php after cancellation
    header("Location: user-pendingborrow.php");
    exit();
} else {
    // If borrow_ids are not provided or form is not submitted, redirect to the pending borrow requests page
    header("Location: user-pendingborrow.php");
    exit();
}
?>
