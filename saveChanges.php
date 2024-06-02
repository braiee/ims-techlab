<?php
session_start();

// Include database connection
include 'db-connect.php';

// Initialize message variable
$message = "";
$success = false;

// Check if the form for editing a creative tool is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['creative_id'])) {
    // Retrieve data from the form
    $creative_id = $_POST['creative_id'];
    $creative_name = $_POST['creative_name'];
    $categories_name = $_POST['categories_name'];
    $legends_name = $_POST['legends_name'];
    $qty = $_POST['qty'];   
    $emei = $_POST['emei'];
    $sn = $_POST['sn'];
    $ref_rnss = $_POST['ref_rnss'];
    $owner = $_POST['owner'];
    $custodian = $_POST['custodian'];
    $rnss_acc = $_POST['rnss_acc'];
    $remarks = $_POST['remarks'];
    $descriptions = $_POST['descriptions'];

    // SQL query to update data in creative_tools table
    $sql = "UPDATE creative_tools SET creative_name='$creative_name', categories_name='$categories_name', legends_name='$legends_name', qty='$qty', emei='$emei', sn='$sn', ref_rnss='$ref_rnss', owner='$owner', custodian='$custodian', rnss_acc='$rnss_acc', remarks='$remarks', descriptions='$descriptions' WHERE creative_id='$creative_id'";

    if ($conn->query($sql) === TRUE) {
        $message = "Creative tool updated successfully.";
        $success = true;
    } else {
        $message = "Error updating creative tool: " . $conn->error;
    }
}

// Close database connection
$conn->close();

// Send response back to the client
echo json_encode(array("message" => $message, "success" => $success));
?>
