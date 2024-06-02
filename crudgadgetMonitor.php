<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db-connect.php';

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Fetch categories from the database
$categoriesResult = $conn->query("SELECT categories_id, categories_name FROM categories");

// Fetch legends from the database
$legendsResult = $conn->query("SELECT legends_id, legends_name FROM legends");

// Check if the form for adding gadget monitor is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_gadget'])) {
    // Retrieve form data
    // Adjust the form field names accordingly
    $gadget_name = $_POST['gadget_name'];
    $color = $_POST['color'];
    $emei = $_POST['emei'];
    $sn = $_POST['sn'];
    $custodian = $_POST['custodian'];
    $rnss_acc = $_POST['rnss_acc'];
    $condition = $_POST['condition'];
    $purpose = $_POST['purpose'];
    $remarks = $_POST['remarks'];
    $categories_id = $_POST['categories_id'];
    $legends_id = $_POST['legends_id'];
    $status = $_POST['status'];

    // Prepare SQL statement to insert gadget monitor into the database using prepared statement
    $sql = "INSERT INTO gadget_monitor (gadget_name, color, emei, sn, custodian, rnss_acc, `condition`, purpose, remarks, categories_id, legends_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssss", $gadget_name, $color, $emei, $sn, $custodian, $rnss_acc, $condition, $purpose, $remarks, $categories_id, $legends_id, $status);
    
    if ($stmt->execute()) {
        $successMessage = "New gadget monitor added successfully.";
    } else {
        $errorMessage = "Error adding new gadget monitor: " . $conn->error;
    }

    $stmt->close();
}

// Handle form submissions for editing gadget monitor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_gadget'])) {
    // Get form data
    $gadget_id = $_POST['edit_gadget_id'];
    $gadget_name = $_POST['edit_gadget_name'];
    $color = $_POST['edit_color'];
    $emei = $_POST['edit_emei'];
    $sn = $_POST['edit_sn'];
    $custodian = $_POST['edit_custodian'];
    $rnss_acc = $_POST['edit_rnss_acc'];
    $condition = $_POST['edit_condition'];
    $purpose = $_POST['edit_purpose'];
    $remarks = $_POST['edit_remarks'];
    $categories_id = $_POST['edit_categories_id'];
    $legends_id = $_POST['edit_legends_id'];
    $status = $_POST['edit_status'];

    // Prepare SQL statement for updating data in gadget_monitor table using prepared statement
    $sql = "UPDATE gadget_monitor 
            SET gadget_name=?, color=?, emei=?, sn=?, custodian=?, rnss_acc=?, `condition`=?, purpose=?, remarks=?, categories_id=?, legends_id=?, status=?
            WHERE gadget_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssi", $gadget_name, $color, $emei, $sn, $custodian, $rnss_acc, $condition, $purpose, $remarks, $categories_id, $legends_id, $status, $gadget_id);
    
    if ($stmt->execute()) {
        $successMessage = "Gadget monitor updated successfully.";
    } else {
        $errorMessage = "Error updating gadget monitor: " . $conn->error;
    }

    $stmt->close();
}

// Delete gadget monitors
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_gadget'])) {
    // Handle delete action
    // Get gadget monitor IDs and update their status to "Deleted" in the database
    if (isset($_POST['gadget_ids'])) {
        $gadget_ids = $_POST['gadget_ids'];
        $gadget_ids_str = "'" . implode("','", $gadget_ids) . "'";
        
        // Get current user's username
        $current_user = $_SESSION['username'];
        
        // Get current timestamp
        $current_timestamp = date("Y-m-d H:i:s");
        
        $sql = "UPDATE gadget_monitor SET status = 'Deleted', delete_timestamp = '$current_timestamp', deleted_by = '$current_user' WHERE gadget_id IN ($gadget_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Gadget monitors marked as deleted successfully.";
        } else {
            $errorMessage = "Error marking gadget monitors as deleted: " . $conn->error;
        }
    } else {
        $errorMessage = "No gadget monitors selected to delete.";
    }
}

// Redirect back to gadget_monitor.php with success or error message
if (!empty($successMessage)) {
    header("Location: gadgetmonitor.php?success=" . urlencode($successMessage));
    exit();
} elseif (!empty($errorMessage)) {
    header("Location: gadgetmonitor.php?error=" . urlencode($errorMessage));
    exit();
}

$conn->close();
?>
