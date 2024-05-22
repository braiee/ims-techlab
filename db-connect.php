<?php
// Database credentials
$servername = "localhost"; // Change this if your database is hosted elsewhere
$username = "root";
$password = "";
$dbname = "techno_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
