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

// Check if the form for adding office supplies is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_office_supply'])) {
    // Retrieve form data
    $office_name = $_POST['office_name'];
    $emei = $_POST['emei'];
    $sn = $_POST['sn'];
    $owner = $_POST['owner'];
    $custodian = $_POST['custodian'];
    $rnss_acc = $_POST['rnss_acc'];
    $remarks = $_POST['remarks'];
    $categories_id = $_POST['categories_id'];
    $legends_id = $_POST['legends_id'];
    $status = $_POST['status'];

    // Prepare SQL statement to insert office supply into the database using prepared statement
    $sql = "INSERT INTO office_supplies (office_name, emei, sn, owner, custodian, rnss_acc, remarks, categories_id, legends_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $office_name, $emei, $sn, $owner, $custodian, $rnss_acc, $remarks, $categories_id, $legends_id, $status);
    
    if ($stmt->execute()) {
        $successMessage = "New office supply added successfully.";
    } else {
        $errorMessage = "Error adding new office supply: " . $conn->error;
    }

    $stmt->close();
}

// Handle form submissions for editing office supplies
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_office_supply'])) {
    // Get form data
    $office_id = $_POST['edit_office_id'];
    $office_name = $_POST['edit_office_name'];
    $emei = $_POST['edit_emei'];
    $sn = $_POST['edit_sn'];
    $owner = $_POST['edit_owner'];
    $custodian = $_POST['edit_custodian'];
    $rnss_acc = $_POST['edit_rnss_acc'];
    $remarks = $_POST['edit_remarks'];
    $categories_id = $_POST['edit_categories_id'];
    $legends_id = $_POST['edit_legends_id'];
    $status = $_POST['edit_status'];

    // Prepare SQL statement for updating data in office_supplies table using prepared statement
    $sql = "UPDATE office_supplies 
            SET office_name=?, emei=?, sn=?, owner=?, custodian=?, rnss_acc=?, remarks=?, categories_id=?, legends_id=?, status=?
            WHERE office_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssi", $office_name, $emei, $sn, $owner, $custodian, $rnss_acc, $remarks, $categories_id, $legends_id, $status, $office_id);
    
    if ($stmt->execute()) {
        $successMessage = "Office supply updated successfully.";
    } else {
        $errorMessage = "Error updating office supply: " . $conn->error;
    }

    $stmt->close();
}

// Handle form submissions for deleting office supplies
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_office_supply'])) {
    // Get office supply IDs and delete them from the database
    if (isset($_POST['office_ids']) && !empty($_POST['office_ids'])) {
        $office_ids = $_POST['office_ids'];
        $office_ids_str = implode(",", $office_ids);
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
