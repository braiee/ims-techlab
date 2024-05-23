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

// Handle form submissions for adding office supplies
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_office_supply'])) {
    // Get form data
    $office_name = $_POST['office_name'];
    $qty = $_POST['qty'];
    $emei = $_POST['emei'];
    $sn = $_POST['sn'];
    $ref_rnss = $_POST['ref_rnss'];
    $owner = $_POST['owner'];
    $custodian = $_POST['custodian'];
    $rnss_acc = $_POST['rnss_acc'];
    $remarks = $_POST['remarks'];

    // Prepare SQL statement for inserting data into office_supplies table
    $sql = "INSERT INTO office_supplies (office_name, qty, emei, sn, ref_rnss, owner, custodian, rnss_acc, remarks)
            VALUES ('$office_name', '$qty', '$emei', '$sn', '$ref_rnss', '$owner', '$custodian', '$rnss_acc', '$remarks')";

    // Execute SQL statement
    if ($conn->query($sql) === TRUE) {
        $successMessage = "New office supply added successfully.";
    } else {
        $errorMessage = "Error adding office supply: " . $conn->error;
    }
}

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

    // Prepare SQL statement for updating data in office_supplies table
    $sql = "UPDATE office_supplies 
            SET office_name='$office_name', qty='$qty', emei='$emei', sn='$sn', ref_rnss='$ref_rnss', owner='$owner', custodian='$custodian', rnss_acc='$rnss_acc', remarks='$remarks'
            WHERE office_id='$office_id'";

    // Execute SQL statement
    if ($conn->query($sql) === TRUE) {
        $successMessage = "Office supply updated successfully.";
    } else {
        $errorMessage = "Error updating office supply: " . $conn->error;
    }
}

// Handle form submissions for deleting office supplies
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_office_supply'])) {
    // Get office supply IDs and delete them from the database
    if (isset($_POST['office_ids'])) {
        $office_ids = $_POST['office_ids'];
        $office_ids_str = "'" . implode("','", $office_ids) . "'";
        $sql = "DELETE FROM office_supplies WHERE office_id IN ($office_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Office supplies deleted successfully.";
        } else {
            $errorMessage = "Error deleting office supplies: " . $conn->error;
        }
    } else {
        $errorMessage = "No office supplies selected to delete.";
    }
}

// Redirect back to officesupplies.php with success or error message
if (!empty($successMessage)) {
    header("Location: officesupplies.php?success=" . urlencode($successMessage));
    exit();
} elseif (!empty($errorMessage)) {
    header("Location: officesupplies.php?error=" . urlencode($errorMessage));
    exit();
}

$conn->close();
?>
