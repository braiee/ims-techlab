<?php
session_start();

// Include database connection
include 'db-connect.php';

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Fetch categories from the database
$categoriesResult = $conn->query("SELECT categories_id, categories_name FROM categories");

// Function to generate unique_id
function generateUniqueID($conn, $legends_id, $categories_id, $year, $office_id) {
    // Retrieve legends abbreviation
    $legends_abv_result = $conn->query("SELECT abv FROM legends WHERE legends_id = '$legends_id'");
    $legends_abv = $legends_abv_result->fetch_assoc()['abv'];

    // Retrieve categories abbreviation
    $categories_abv_result = $conn->query("SELECT abv FROM categories WHERE categories_id = '$categories_id'");
    $categories_abv = $categories_abv_result->fetch_assoc()['abv'];

    // Pad the office_id with three zeros
    $padded_office_id = str_pad($office_id, 3, '0', STR_PAD_LEFT);

    // Generate unique_id
    $unique_legends_id = $legends_abv . '-' . $categories_abv . '-' . $year . '-' . $padded_office_id;
    return $unique_legends_id;
}

// Check if the form for adding office supplies is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_office_supply'])) {
    // Retrieve form data
    $office_name = $_POST['office_name'];
    $custodian = $_POST['custodian'];
    $remarks = $_POST['remarks'];
    $categories_id = $_POST['categories_id'];
    $legends_id = $_POST['legends_id'];
    $status = $_POST['status'];

    // Prepare SQL statement to insert office supply into the database using prepared statement
    $sql = "INSERT INTO office_supplies (office_name,  custodian,  remarks, categories_id, legends_id, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $office_name,  $custodian,  $remarks, $categories_id, $legends_id, $status);
    
    if ($stmt->execute()) {
        $office_id = $stmt->insert_id; // Get the inserted office_id
        // Generate the unique_id
        $unique_legends_id = generateUniqueID($conn, $legends_id, $categories_id, date("Y"), $office_id);
        // Update the unique_id for the inserted row
        $conn->query("UPDATE office_supplies SET unique_legends_id = '$unique_legends_id' WHERE office_id = '$office_id'");
        $successMessage = "New office supply added successfully.";
    } else {
        $errorMessage = "Error adding new office supply: " . $conn->error;
    }

    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_office_supply'])) {
    // Get form data
    $office_id = $_POST['edit_office_id'];
    $office_name = $_POST['edit_office_name'];
    $custodian = $_POST['edit_custodian'];
    $remarks = $_POST['edit_remarks'];
    $categories_id = $_POST['edit_categories_id'];
    $legends_id = $_POST['edit_legends_id'];
    $status = $_POST['edit_status'];

    // Prepare SQL statement for updating data in office_supplies table using prepared statement
    $sql = "UPDATE office_supplies 
            SET office_name=?, custodian=?, remarks=?, categories_id=?, legends_id=?, status=?
            WHERE office_id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("ssssssi", $office_name, $custodian, $remarks, $categories_id, $legends_id, $status, $office_id);
    
    if ($stmt->execute()) {
        $successMessage = "Office supply updated successfully.";
    } else {
        $errorMessage = "Error updating office supply: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
}



// Delete office supplies
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Handle delete action
    // Get office supply IDs and update their status to "Deleted" in the database
    if (isset($_POST['office_ids'])) {
        $office_ids = $_POST['office_ids'];
        $office_ids_str = "'" . implode("','", $office_ids) . "'";
        
        // Get current user's username
        $current_user = $_SESSION['username'];

        $current_timestamp = date("Y-m-d H:i:s");

$sql = "UPDATE office_supplies 
        SET status = 'Deleted', 
            delete_timestamp = '$current_timestamp', 
            deleted_by = '$current_user' 
        WHERE office_id IN ($office_ids_str)";

if ($conn->query($sql) === TRUE) {
    $successMessage = "Office supplies marked as deleted successfully.";
} else {
    $errorMessage = "Error marking office supplies as deleted: " . $conn->error;
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
