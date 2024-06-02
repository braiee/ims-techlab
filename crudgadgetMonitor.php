<?php
session_start();

// Include database connection
include 'db-connect.php';

// Function to generate unique_gadget_id
function generateUniqueGadgetID($conn, $gadget_id) {
    // Fetch necessary information for generating unique_gadget_id
    $sql = "SELECT l.abv AS legends_abv, c.abv AS categories_abv, gm.date_added
            FROM gadget_monitor gm
            JOIN legends l ON gm.legends_id = l.legends_id
            JOIN categories c ON gm.categories_id = c.categories_id
            WHERE gm.gadget_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $gadget_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch data from the query result
    $row = $result->fetch_assoc();
    $legends_abv = $row['legends_abv'];
    $categories_abv = $row['categories_abv'];
    $date_added = $row['date_added'];
    
    // Close the prepared statement
    $stmt->close();
    
    // Extract year from the date_added
    $year = date('Y', strtotime($date_added));
    
    // Fetch the count of gadgets added in the same year
    $sql = "SELECT COUNT(*) AS count_gadgets
            FROM gadget_monitor
            WHERE YEAR(date_added) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch count of gadgets
    $row = $result->fetch_assoc();
    $count_gadgets = $row['count_gadgets'];
    
    // Close the prepared statement
    $stmt->close();
    
    // Pad the count with three zeros
    $padded_count = str_pad($count_gadgets + 1, 3, '0', STR_PAD_LEFT);
    
    // Generate unique_gadget_id
    $unique_gadget_id = substr($legends_abv, 0, 3) . '-' . substr($categories_abv, 0, 3) . '-' . $year . '-' . $padded_count;
    
    return $unique_gadget_id;
}
// Check if the form for adding gadget monitor is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_gadget'])) {
    // Retrieve form data
    // Adjust the form field names accordingly
    $gadget_name = $_POST['gadget_name'];
    $color = $_POST['color'];
    $emei = $_POST['emei'];
    $sn = $_POST['sn'];
    $custodian = $_POST['custodian'];
    $rnss_acc = $_POST['rnss_acc'];
    $condition = $_POST['condition'];
    $purpose = $_POST['purpose'];
    $remarks = $_POST['remarks'];
    $categories_id = $_POST['categories_id'];
    $legends_id = $_POST['legends_id'];
    $status = $_POST['status'];
    $ref_rnss = $_POST['ref_rnss']; // Add this line to retrieve ref_rnss value
    $owner = $_POST['owner']; // Add this line to retrieve owner value

    // Prepare SQL statement to insert gadget monitor into the database using prepared statement
    $sql = "INSERT INTO gadget_monitor (gadget_name, color, emei, sn, custodian, rnss_acc, `condition`, purpose, remarks, categories_id, legends_id, status, ref_rnss, owner) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssss", $gadget_name, $color, $emei, $sn, $custodian, $rnss_acc, $condition, $purpose, $remarks, $categories_id, $legends_id, $status, $ref_rnss, $owner);
    
    if ($stmt->execute()) {
        // Get the last inserted ID
        $last_insert_id = $conn->insert_id;
        // Generate unique_gadget_id
        $unique_gadget_id = generateUniqueGadgetID($conn, $last_insert_id);
        // Update the unique_gadget_id for the inserted row
        $update_sql = "UPDATE gadget_monitor SET unique_gadget_id = ? WHERE gadget_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $unique_gadget_id, $last_insert_id);
        $update_stmt->execute();
        
        $successMessage = "New gadget monitor added successfully.";
    } else {
        $errorMessage = "Error adding new gadget monitor: " . $conn->error;
    }

    $stmt->close();
}

// Handle form submissions for editing gadget monitor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_gadget'])) {
    // Get form data
    $gadget_id = $_POST['edit_gadget_id'];
    $gadget_name = $_POST['edit_gadget_name'];
    $color = $_POST['edit_color'];
    $emei = $_POST['edit_emei'];
    $sn = $_POST['edit_sn'];
    $custodian = $_POST['edit_custodian'];
    $rnss_acc = $_POST['edit_rnss_acc'];
    $condition = $_POST['edit_condition'];
    $purpose = $_POST['edit_purpose'];
    $remarks = $_POST['edit_remarks'];
    $categories_id = $_POST['edit_categories_id'];
    $legends_id = $_POST['edit_legends_id'];
    $status = $_POST['edit_status'];
    $ref_rnss = $_POST['edit_ref_rnss']; // Add this line to retrieve ref_rnss value
    $owner = $_POST['edit_owner']; // Add this line to retrieve owner value

    // Prepare SQL statement for updating data in gadget_monitor table using prepared statement
    $sql = "UPDATE gadget_monitor 
            SET gadget_name=?, color=?, emei=?, sn=?, custodian=?, rnss_acc=?, `condition`=?, purpose=?, remarks=?, categories_id=?, legends_id=?, status=?, ref_rnss=?, owner=?
            WHERE gadget_id=?";
    $stmt = $conn->prepare($sql);
    // Check if the prepared statement is successfully created
    if ($stmt) {
        // Bind parameters to the prepared statement
        $stmt->bind_param("ssssssssssssssi", $gadget_name, $color, $emei, $sn, $custodian, $rnss_acc, $condition, $purpose, $remarks, $categories_id, $legends_id, $status, $ref_rnss, $owner, $gadget_id);
        // Execute the prepared statement
        if ($stmt->execute()) {
            $successMessage = "Gadget monitor updated successfully.";
        } else {
            $errorMessage = "Error updating gadget monitor: " . $conn->error;
        }
        // Close the prepared statement
        $stmt->close();
    } else {
        // Error in preparing the statement
        $errorMessage = "Error preparing update statement: " . $conn->error;
    }
}


// Delete gadget monitors
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_gadget'])) {
    // Handle delete action
    // Get gadget monitor IDs and update their status to "Deleted" in the database
    if (isset($_POST['gadget_ids'])) {
        $gadget_ids = $_POST['gadget_ids'];
        $gadget_ids_str = "'" . implode("','", $gadget_ids) . "'";
        
        // Get current user's username
        $current_user = $_SESSION['username'];
        
        // Get current timestamp
        $current_timestamp = date("Y-m-d H:i:s");
        
        $sql = "UPDATE gadget_monitor SET status = 'Deleted', delete_timestamp = '$current_timestamp', deleted_by = '$current_user' WHERE gadget_id IN ($gadget_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Gadget monitors marked as deleted successfully.";
        } else {
            $errorMessage = "Error marking gadget monitors as deleted: " . $conn->error;
        }
    } else {
        $errorMessage = "No gadget monitors selected to delete.";
    }
}



// Redirect back to gadget_monitor.php with success or error message
if (!empty($successMessage)) {
    header("Location: gadgetmonitor.php?success=" . urlencode($successMessage));
    exit();
} elseif (!empty($errorMessage)) {
    header("Location: gadgetmonitor.php?error=" . urlencode($errorMessage));
    exit();
}

$conn->close();
?>
