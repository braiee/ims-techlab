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

// Handle form submissions for editing office supplies
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_office_supply'])) {
    // Get form data
    $office_id = $_POST['edit_office_id'];
    $office_name = $_POST['edit_office_name'];
    $qty = $_POST['edit_qty'];
    $emei = $_POST['edit_emei'];
    $sn = $_POST['edit_sn'];
    $ref_rnss = $_POST['edit_ref_rnss'];
    $owner = $_POST['edit_owner'];
    $custodian = $_POST['edit_custodian'];
    $rnss_acc = $_POST['edit_rnss_acc'];
    $remarks = $_POST['edit_remarks'];
    $status = $_POST['edit_status'];
    $category_id = $_POST['edit_categories'];
    $legend_id = $_POST['edit_legends'];

    // Prepare SQL statement for updating data in office_supplies table
    $sql = "UPDATE office_supplies SET 
            office_name='$office_name', qty='$qty', emei='$emei', sn='$sn', ref_rnss='$ref_rnss', owner='$owner', custodian='$custodian', rnss_acc='$rnss_acc', remarks='$remarks', status='$status', categories_id='$category_id', legends_id='$legend_id' 
            WHERE office_id='$office_id'";

    // Execute SQL statement
    if ($conn->query($sql) === TRUE) {
        $successMessage = "Office supply updated successfully.";
    } else {
        $errorMessage = "Error updating office supply: " . $conn->error;
    }

    // Redirect back to officesupplies.php with success or error message
    if (!empty($successMessage)) {
        header("Location: officesupplies.php?success=" . urlencode($successMessage));
        exit();
    } elseif (!empty($errorMessage)) {
        header("Location: officesupplies.php?error=" . urlencode($errorMessage));
        exit();
    }
}

$conn->close();
?>
