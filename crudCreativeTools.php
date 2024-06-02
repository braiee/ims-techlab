<?php
session_start();

// Include database connection
include 'db-connect.php';

// Function to generate unique creative ID
function generateUniqueCreativeID($legend_abv, $category_abv, $year_added, $row_num) {
    // Pad the row number to 4 digits with leading zeros
    $padded_row_num = str_pad($row_num, 4, '0', STR_PAD_LEFT);
    
    // Construct the unique ID with the specified format
    $unique_id = $legend_abv . '-' . $category_abv . '-' . $year_added . '-' . $padded_row_num;
    
    return $unique_id;
}

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
    $qty = $_POST['qty']; // Retrieve quantity

    // Generate unique ID
    $unique_creative_id = generateUniqueCreativeID($legends_id, $categories_id, date("Y"), 1);

    // Prepare SQL statement to insert creative tool into the database using prepared statement
    $sql = "INSERT INTO creative_tools (creative_name, descriptions, emei, sn, custodian, rnss_acc, remarks, categories_id, legends_id, qty, unique_creative_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssis", $creative_name, $descriptions, $emei, $sn, $custodian, $rnss_acc, $remarks, $categories_id, $legends_id, $qty, $unique_creative_id);

    if ($stmt->execute()) {
        $successMessage = "New creative tool added successfully.";
    } else {
        $errorMessage = "Error adding creative tool: " . $stmt->error;
    }
    $stmt->close();
    // Redirect to the current page to avoid resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle form submissions for editing creative tools
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_creative_tool'])) {
    // Get form data
    $creative_id = $_POST['edit_creative_id'];
    $creative_name = $_POST['edit_creative_name'];
    $descriptions = $_POST['edit_descriptions'];
    $emei = $_POST['edit_emei'];
    $sn = $_POST['edit_sn'];
    $custodian = $_POST['edit_custodian'];
    $rnss_acc = $_POST['edit_rnss_acc'];
    $remarks = $_POST['edit_remarks'];
    $categories_id = $_POST['edit_categories_id'];
    $legends_id = $_POST['edit_legends_id'];
    $qty = $_POST['edit_qty']; // Retrieve quantity

    // Prepare SQL statement for updating data in creative_tools table using prepared statement
    $sql = "UPDATE creative_tools 
            SET creative_name=?, descriptions=?, emei=?, sn=?, custodian=?, rnss_acc=?, remarks=?, categories_id=?, legends_id=?, qty=?
            WHERE creative_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssii", $creative_name, $descriptions, $emei, $sn, $custodian, $rnss_acc, $remarks, $categories_id, $legends_id, $qty, $creative_id);

    if ($stmt->execute()) {
        $successMessage = "Creative tool updated successfully.";
    } else {
        $errorMessage = "Error updating creative tool: " . $stmt->error;
    }
    $stmt->close();
}

// Check if the form for deleting creative tools is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_creative_tool'])) {
    // Handle delete action
    markCreativeToolsAsDeleted();
}

function markCreativeToolsAsDeleted() {
    global $conn, $successMessage, $errorMessage;

    // Get creative tool IDs and update their status to "Deleted" in the database
    if (isset($_POST['creative_ids'])) {
        $creative_ids = $_POST['creative_ids'];
        $creative_ids_str = "'" . implode("','", $creative_ids) . "'";

        // Get current user's username
        $current_user = $_SESSION['username'];
        
        // Get current timestamp
        $current_timestamp = date("Y-m-d H:i:s");

        // SQL to update creative tools status to "Deleted"
        $sql = "UPDATE creative_tools SET status = 'Deleted', delete_timestamp = '$current_timestamp', deleted_by = '$current_user' WHERE creative_id IN ($creative_ids_str)";

        // Execute SQL query
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Creative tools marked as deleted successfully.";
        } else {
            $errorMessage = "Error marking creative tools as deleted: " . $conn->error;
        }
    } else {
        $errorMessage = "No creative tools selected to delete.";
    }
}

// Close database connection
$conn->close();


// Redirect back to the current page with success or error message
if (!empty($successMessage)) {
    $_SESSION['message'] = $successMessage;
} elseif (!empty($errorMessage)) {
    $_SESSION['message'] = $errorMessage;
}

// Redirect to the current page to avoid resubmission on refresh
header("Location: " . $_SERVER['PHP_SELF']);
exit();
?>
