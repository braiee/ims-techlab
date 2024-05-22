<?php
// Include database connection
include 'db-connect.php';


// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get assigned user
    $assigned_to = $_POST['assigned_to'];

    // Insert new ticket with assigned user and date created
    $sql = "INSERT INTO ticketing_table (task_name, description, status, assigned_to, date_created) 
            VALUES ('".$_POST["task_name"]."', '".$_POST["description"]."', '".$_POST["status"]."', '$assigned_to', '".$_POST["date_created"]."')";

    if ($conn->query($sql) === TRUE) {
        // Redirect user to ticketing.php
        header("Location: ticketing.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();
?>
