<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST["category"];
    $item_id = $_POST["item_id"];
    $return_date = $_POST["return_date"];
    $user_id = $_POST["user_id"];

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

    // Insert the borrow request into the borrowed_items table
    $sql_insert = "INSERT INTO borrowed_items (user_id, item_id, category, return_date, status) VALUES (?, ?, ?, ?, 'Pending')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiss", $user_id, $item_id, $category, $return_date);

    if ($stmt_insert->execute()) {
        // Update the status of the item to 'Pending'
        $sql_update = "UPDATE $table SET status = 'Pending' WHERE $id_column = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $item_id);
        if ($stmt_update->execute()) {
            echo "Request submitted successfully.";
        } else {
            echo "Failed to update item status.";
        }
    } else {
        echo "Failed to submit request.";
    }

    $stmt_insert->close();
    $stmt_update->close();
    $conn->close();
}
?>
