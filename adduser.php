<?php
include 'db-connect.php';

$username = 'admin';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
$identity = 1; // 1 for admin, 0 for regular user

// Check if the user already exists
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "User already exists.";
} else {
    // Insert the user if they do not exist
    $sql = "INSERT INTO users (username, password, identity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $username, $hashed_password, $identity);

    if ($stmt->execute()) {
        echo "New user created successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
