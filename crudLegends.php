<?php
session_start();

// Include database connection
include 'db-connect.php';

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Create operation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_legend'])) {
    $legend_name = $_POST['legend_name'];
    $legend_abv = $_POST['legend_abv'];


    $sql = "INSERT INTO legends (legends_name, abv) VALUES ('$legend_name', '$legend_abv')";
    if ($conn->query($sql) === TRUE) {
        $successMessage = "Legend added successfully.";
    } else {
        $errorMessage = "Error adding legend: " . $conn->error;
    }
}

// Update operation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_legend'])) {
    $legend_id = $_POST['edit_legend_id'];
    $legend_name = $_POST['edit_legend_name'];
    $legend_abv = $_POST['edit_legend_abv'];


    $sql = "UPDATE legends SET legends_name='$legend_name', abv='$legend_abv' WHERE legends_id='$legend_id'";
    if ($conn->query($sql) === TRUE) {
        $successMessage = "Legend updated successfully.";
    } else {
        $errorMessage = "Error updating legend: " . $conn->error;
    }
}

// Delete operation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_legend'])) {
    if (isset($_POST['delete_legend_id'])) {
        $legend_ids = $_POST['delete_legend_id'];
        $legend_ids_str = implode(",", $legend_ids);

        $sql = "DELETE FROM legends WHERE legends_id IN ($legend_ids_str)";
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Location(s) deleted successfully.";
        } else {
            $errorMessage = "Error deleting location(s): " . $conn->error;
        }
    } else {
        $errorMessage = "No locations selected to delete.";
    }
}

// Redirect back to legends.php with success or error message
if (!empty($successMessage)) {
    $_SESSION['success_message'] = $successMessage;
} elseif (!empty($errorMessage)) {
    $_SESSION['error_message'] = $errorMessage;
}
header("Location: legends.php");
exit();
?>
