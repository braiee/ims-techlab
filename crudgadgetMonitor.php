<?php
session_start();

// Include database connection
include 'db-connect.php';

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Fetch categories from the database
$categoriesResult = $conn->query("SELECT categories_id, categories_name FROM categories");

// Fetch legends from the database
$legendsResult = $conn->query("SELECT legends_id, legends_name FROM legends");

// Add new gadget
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_gadget'])) {
    // Retrieve form data
    $gadget_name = $_POST['gadget_name'];
    $categories_id = $_POST['categories_id'];
    $legends_id = $_POST['legends_id']; // Add legends_id retrieval
    $color = $_POST['color'];
    $emei = $_POST['emei'];
    $sn = $_POST['sn'];
    $custodian = $_POST['custodian'];
    $rnss_acc = $_POST['rnss_acc'];
    $condition = $_POST['condition'];
    $purpose = $_POST['purpose'];
    $remarks = $_POST['remarks'];
    $status = $_POST['status']; // Add status retrieval

    // SQL query to insert data into the gadget_monitor table
    $sql = "INSERT INTO gadget_monitor (gadget_name, categories_id, legends_id, color, emei, sn, custodian, rnss_acc, `condition`, purpose, remarks, status) 
            VALUES ('$gadget_name', '$categories_id', '$legends_id', '$color', '$qty', '$emei', '$sn',  '$custodian', '$rnss_acc', '$condition', '$purpose', '$remarks', '$status')";

    if ($conn->query($sql) === TRUE) {
        $successMessage = "New gadget added successfully.";
    } else {
        $errorMessage = "Error adding gadget: " . $conn->error;
    }
}

// Edit gadget details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_gadget'])) {
    // Retrieve form data
    $gadget_id = $_POST['edit_gadget_id'];
    $gadget_name = $_POST['edit_gadget_name'];
    $categories_id = $_POST['edit_categories_id'];
    $legends_id = $_POST['edit_legends_id']; // Add legends_id retrieval
    $color = $_POST['edit_color'];
    $emei = $_POST['edit_emei'];
    $sn = $_POST['edit_sn'];
    $custodian = $_POST['edit_custodian'];
    $rnss_acc = $_POST['edit_rnss_acc'];
    $condition = $_POST['edit_condition'];
    $purpose = $_POST['edit_purpose'];
    $remarks = $_POST['edit_remarks'];
    $status = $_POST['edit_status']; // Add status retrieval

    // SQL query to update gadget details
    $sql = "UPDATE gadget_monitor SET gadget_name='$gadget_name', categories_id='$categories_id', legends_id='$legends_id', color='$color',  emei='$emei', sn='$sn', custodian='$custodian', rnss_acc='$rnss_acc', `condition`='$condition', purpose='$purpose', remarks='$remarks', status='$status' WHERE gadget_id=$gadget_id";

    if ($conn->query($sql) === TRUE) {
        $successMessage = "Gadget details updated successfully.";
    } else {
        $errorMessage = "Error updating gadget details: " . $conn->error;
    }
}


// Delete gadget
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_gadget'])) {
    // Retrieve gadget ID to delete
    $gadget_id = $_POST['delete_gadget_id'];

    // SQL query to delete gadget
    $sql = "DELETE FROM gadget_monitor WHERE gadget_id=$gadget_id";

    if ($conn->query($sql) === TRUE) {
        $successMessage = "Gadget deleted successfully.";
    } else {
        $errorMessage = "Error deleting gadget: " . $conn->error;
    }
}

// Close database connection
$conn->close();

// Redirect back to the gadgetmonitor.php page with success or error message
header("Location: gadgetmonitor.php?successMessage=$successMessage&errorMessage=$errorMessage");
exit();
?>
