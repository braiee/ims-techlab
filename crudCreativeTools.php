<?php
session_start();

// Include database connection
include 'db-connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Check if form is submitted for adding a creative tool
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_creative_tool'])) {
    // Validate and sanitize input data
    $creative_name = htmlspecialchars($_POST['creative_name']);
    $descriptions = htmlspecialchars($_POST['descriptions']);
    $qty = htmlspecialchars($_POST['qty']);
    $emei = htmlspecialchars($_POST['emei']);
    $sn = htmlspecialchars($_POST['sn']);
    $ref_rnss = htmlspecialchars($_POST['ref_rnss']);
    $owner = htmlspecialchars($_POST['owner']);
    $custodian = htmlspecialchars($_POST['custodian']);
    $rnss_acc = htmlspecialchars($_POST['rnss_acc']);
    $remarks = htmlspecialchars($_POST['remarks']);

    // Prepare and execute SQL statement to insert the new creative tool
    $sql = "INSERT INTO creative_tools (creative_name, descriptions, qty, emei, sn, ref_rnss, owner, custodian, rnss_acc, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $creative_name, $descriptions, $qty, $emei, $sn, $ref_rnss, $owner, $custodian, $rnss_acc, $remarks);
    if ($stmt->execute()) {
        $successMessage = "Creative tool added successfully.";
    } else {
        $errorMessage = "Error adding creative tool: " . $conn->error;
    }
    $stmt->close();
}

// Check if form is submitted for editing a creative tool
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_creative_tool'])) {
    // Validate and sanitize input data
    $creative_id = $_POST['edit_creative_id'];
    $creative_name = htmlspecialchars($_POST['edit_creative_name']);
    $descriptions = htmlspecialchars($_POST['edit_descriptions']);
    $qty = htmlspecialchars($_POST['edit_qty']);
    $emei = htmlspecialchars($_POST['edit_emei']);
    $sn = htmlspecialchars($_POST['edit_sn']);
    $ref_rnss = htmlspecialchars($_POST['edit_ref_rnss']);
    $owner = htmlspecialchars($_POST['edit_owner']);
    $custodian = htmlspecialchars($_POST['edit_custodian']);
    $rnss_acc = htmlspecialchars($_POST['edit_rnss_acc']);
    $remarks = htmlspecialchars($_POST['edit_remarks']);

    // Prepare and execute SQL statement to update the creative tool
    $sql = "UPDATE creative_tools SET creative_name=?, descriptions=?, qty=?, emei=?, sn=?, ref_rnss=?, owner=?, custodian=?, rnss_acc=?, remarks=? WHERE creative_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssi", $creative_name, $descriptions, $qty, $emei, $sn, $ref_rnss, $owner, $custodian, $rnss_acc, $remarks, $creative_id);
    if ($stmt->execute()) {
        // If update is successful, send success response to JavaScript
        echo json_encode(array("success" => true, "message" => "Creative tool updated successfully."));
    } else {
        // If update fails, send error response to JavaScript
        echo json_encode(array("success" => false, "message" => "Error updating creative tool: " . $conn->error));
    }
    $stmt->close();
    exit(); // Terminate script after processing form submission
}


// Check if form is submitted for deleting creative tools
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Handle delete action
    // Get creative tool IDs and delete them from the database
    if (isset($_POST['creative_ids'])) {
        $creative_ids = $_POST['creative_ids'];
        $creative_ids_str = "'" . implode("','", $creative_ids) . "'";
        $sql = "DELETE FROM creative_tools WHERE creative_id IN ($creative_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Creative tools deleted successfully.";
        } else {
            $errorMessage = "Error deleting creative tools: " . $conn->error;
        }
    } else {
        $errorMessage = "No creative tools selected to delete.";
    }
}

// Redirect back to creativeTools.php with success or error message
if (!empty($successMessage)) {
    header("Location: creativeTools.php?success=" . urlencode($successMessage));
} elseif (!empty($errorMessage)) {
    header("Location: creativeTools.php?error=" . urlencode($errorMessage));
} else {
    header("Location: creativeTools.php");
}
exit();
?>
