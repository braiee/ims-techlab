<?php
include 'db-connect.php';

// Fetch creative tool details based on creative_id
$creative_id = $_GET['id'];
$sql = "SELECT * FROM creative_tools WHERE creative_id = $creative_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Echo HTML content with details
    echo '<p>ID: ' . $row["creative_id"] . '</p>';
    echo '<p>Name: ' . $row["creative_name"] . '</p>';
    echo '<p>Quantity: ' . $row["qty"] . '</p>';
    // Add more details as needed
} else {
    echo "No details found";
}
$conn->close();
?>
