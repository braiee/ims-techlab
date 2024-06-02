<?php
session_start();

// Include database connection
include 'db-connect.php';

// Check if the form for adding a creative tool is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_creative_tool'])) {
    // Retrieve data from the form
    $creative_name = $_POST['creative_name'];
    $descriptions = $_POST['descriptions'];
    $emei = $_POST['emei'];
    $sn = $_POST['sn'];
    $custodian = $_POST['custodian'];
    $rnss_acc = $_POST['rnss_acc'];
    $remarks = $_POST['remarks'];
    $categories_id = $_POST['categories_id'];
    $legends_id = $_POST['legends_id'];

    // SQL query to insert data into creative_tools table
    $sql = "INSERT INTO creative_tools (creative_name, descriptions, emei, sn, custodian, rnss_acc, remarks, categories_id, legends_id) VALUES ('$creative_name', '$descriptions', '$emei', '$sn', '$custodian', '$rnss_acc', '$remarks', '$categories_id', '$legends_id')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "New creative tool added successfully.";
    } else {
        $_SESSION['message'] = "Error adding creative tool: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
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
    $sql = "UPDATE gadget_monitor SET gadget_name='$gadget_name', categories_id='$categories_id', 
    legends_id='$legends_id', color='$color', emei='$emei', sn='$sn', custodian='$custodian', 
    rnss_acc='$rnss_acc', `condition`='$condition', purpose='$purpose', remarks='$remarks', 
    status='$status' WHERE gadget_id=$gadget_id";

    if ($conn->query($sql) === TRUE) {
        $successMessage = "Gadget details updated successfully.";
    } else {
        $errorMessage = "Error updating gadget details: " . $conn->error;
    }
}


// Close database connection
$conn->close();

// Display the message if it exists
if (isset($_SESSION['message'])) {
    echo '<p>' . $_SESSION['message'] . '</p>';
    unset($_SESSION['message']); // Clear the message after displaying it
}
?>
