<?php
session_start();

// Include database connection
include 'db-connect.php';

// Initialize message variable
$message = "";
$success = false;

// Check if the form for adding a creative tool is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_creative_tool'])) {
    // Retrieve data from the form
    $creative_name = $_POST['creative_name'];
    $descriptions = $_POST['descriptions'];
    $qty = $_POST['qty'];
    $emei = $_POST['emei'];
    $sn = $_POST['sn'];
    $ref_rnss = $_POST['ref_rnss'];
    $owner = $_POST['owner'];
    $custodian = $_POST['custodian'];
    $rnss_acc = $_POST['rnss_acc'];
    $remarks = $_POST['remarks'];
    $categories_id = $_POST['categories_id'];
    $legends_id = $_POST['legends_id'];
    $status = $_POST['status']; // Retrieve status

    // SQL query to insert data into creative_tools table
    $sql = "INSERT INTO creative_tools (creative_name, descriptions, qty, emei, sn, ref_rnss, owner, custodian, rnss_acc, remarks, categories_id, legends_id, status) VALUES ('$creative_name', '$descriptions', '$qty', '$emei', '$sn', '$ref_rnss', '$owner', '$custodian', '$rnss_acc', '$remarks', '$categories_id', '$legends_id', '$status')";

    if ($conn->query($sql) === TRUE) {
        $message = "New creative tool added successfully.";
        $success = true;
        // Redirect to creativeTools.php after successful add
        header("Location: creativeTools.php");
        exit();
    } else {
        $message = "Error adding creative tool: " . $conn->error;
    }
}

// Check if the form for editing a creative tool is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_creative_tool'])) {
    // Retrieve data from the form
    $creative_id = $_POST['edit_creative_id'];
    $creative_name = $_POST['edit_creative_name'];
    $descriptions = $_POST['edit_descriptions'];
    $qty = $_POST['edit_qty'];
    $emei = $_POST['edit_emei'];
    $sn = $_POST['edit_sn'];
    $ref_rnss = $_POST['edit_ref_rnss'];
    $owner = $_POST['edit_owner'];
    $custodian = $_POST['edit_custodian'];
    $rnss_acc = $_POST['edit_rnss_acc'];
    $remarks = $_POST['edit_remarks'];
    $categories_id = $_POST['edit_categories_id'];
    $legends_id = $_POST['edit_legends_id'];
    $status = $_POST['edit_status']; // Retrieve status

    // SQL query to update data in creative_tools table
    $sql = "UPDATE creative_tools SET creative_name='$creative_name', descriptions='$descriptions', qty='$qty', emei='$emei', sn='$sn', ref_rnss='$ref_rnss', owner='$owner', custodian='$custodian', rnss_acc='$rnss_acc', remarks='$remarks', categories_id='$categories_id', legends_id='$legends_id', status='$status' WHERE creative_id='$creative_id'";

    if ($conn->query($sql) === TRUE) {
        $message = "Creative tool updated successfully.";
        $success = true;
        // Redirect to creativeTools.php after successful update
        header("Location: creativeTools.php");
        exit();
    } else {
        $message = "Error updating creative tool: " . $conn->error;
    }
}

// Close database connection
$conn->close();

// Send response back to the client
echo json_encode(array("message" => $message, "success" => $success));
?>
