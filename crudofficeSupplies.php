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

// Check if form is submitted (for adding office supplies)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
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
    $category_id = $_POST['categories'];
    $legend_id = $_POST['legends'];
    $status = $_POST['status'];

    // Prepare SQL statement for inserting data into office_supplies table
    $sql = "INSERT INTO office_supplies (office_name, qty, emei, sn, ref_rnss, owner, custodian, rnss_acc, remarks, categories_id, legends_id, status) 
            VALUES ('$office_name', '$qty', '$emei', '$sn', '$ref_rnss', '$owner', '$custodian', '$rnss_acc', '$remarks', '$category_id', '$legend_id', '$status')";

    // Execute SQL statement
    if ($conn->query($sql) === TRUE) {
        $successMessage = "New office supply added successfully.";
    } else {
        $errorMessage = "Error adding office supply: " . $conn->error;
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
