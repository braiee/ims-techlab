<?php
session_start();

// Include database connection
include 'db-connect.php';

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Add new gadget
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_gadget'])) {
    // Retrieve form data
    $gadget_name = $_POST['gadget_name'];
    $categories_id = $_POST['categories_id'];
    $legends_id = $_POST['legends_id']; // Add legends_id retrieval
    $color = $_POST['color'];
    $qty = $_POST['qty'];
    $emei = $_POST['emei'];
    $sn = $_POST['sn'];
    $ref_rnss = $_POST['ref_rnss'];
    $owner = $_POST['owner'];
    $custodian = $_POST['custodian'];
    $rnss_acc = $_POST['rnss_acc'];
    $condition = $_POST['condition'];
    $purpose = $_POST['purpose'];
    $remarks = $_POST['remarks'];

    // SQL query to insert data into the gadget_monitor table
    $sql = "INSERT INTO gadget_monitor (gadget_name, categories_id, legends_id, color, qty, emei, sn, ref_rnss, owner, custodian, rnss_acc, `condition`, purpose, remarks) 
            VALUES ('$gadget_name', '$categories_id', '$legends_id', '$color', '$qty', '$emei', '$sn', '$ref_rnss', '$owner', '$custodian', '$rnss_acc', '$condition', '$purpose', '$remarks')";

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
    $qty = $_POST['edit_qty'];
    $emei = $_POST['edit_emei'];
    $sn = $_POST['edit_sn'];
    $ref_rnss = $_POST['edit_ref_rnss'];
    $owner = $_POST['edit_owner'];
    $custodian = $_POST['edit_custodian'];
    $rnss_acc = $_POST['edit_rnss_acc'];
    $condition = $_POST['edit_condition'];
    $purpose = $_POST['edit_purpose'];
    $remarks = $_POST['edit_remarks'];

    // SQL query to update gadget details
    $sql = "UPDATE gadget_monitor SET gadget_name='$gadget_name', categories_id='$categories_id', legends_id='$legends_id', color='$color', qty='$qty', emei='$emei', sn='$sn', ref_rnss='$ref_rnss', owner='$owner', custodian='$custodian', rnss_acc='$rnss_acc', `condition`='$condition', purpose='$purpose', remarks='$remarks' WHERE gadget_id=$gadget_id";

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
