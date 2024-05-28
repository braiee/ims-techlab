<?php
session_start(); // Start the session at the beginning of the file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Retrieve username from session if set
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Include database connection
include 'db-connect.php';

date_default_timezone_set('Asia/Manila'); // e.g., 'America/New_York'


// Check if form is submitted (for deleting tickets)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Handle delete action
    // Get ticket IDs and delete them from the database
    if (isset($_POST['ticket_ids'])) {
        $ticket_ids = $_POST['ticket_ids'];
        $ticket_ids_str = "'" . implode("','", $ticket_ids) . "'";
        $sql = "DELETE FROM ticketing_table WHERE ticket_id IN ($ticket_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            echo "Tickets deleted successfully.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "No tickets selected to delete.";
    }
}

// SQL query to fetch ticket data
$sql = "SELECT ticket_id, task_name, description, status, assigned_to, date_created FROM ticketing_table";
$result = $conn->query($sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/Dashboard.css">
    <link rel="stylesheet" href="css/Ticket_STylE.css">
    <title>Borrow Item</title>
</head>
<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="user-dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
    <a href="user-ticketing.php" class="nav-item active"><span class="icon-placeholder"></span>Borrow</a>
    <a href="logout.php" class="nav-item"><span class="icon-placeholder"></span>Logout</a>
</div>
<!-- Header box container -->
<div class="header-box">
    <div class="header-box-content">
        <!-- Navigation links -->
        <ul class="nav-links">
            <!-- Display greeting message -->
            <?php
            if (isset($_SESSION["user_id"])) {
                echo '<li>Hello, ' . $_SESSION["username"] . '!</li>';
                echo '<li><a href="logout.php">Logout</a></li>';
            }
            ?>
        </ul>
    </div>
</div>
<!-- Rest of the HTML content -->

    <<div class="container">
        <h1>Pending Borrowing Requests</h1>
        <table class="request-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Output pending requests
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["user_name"] . "</td>";
                    echo "<td>" . $row["item_name"] . "</td>";
                    echo "<td>" . $row["description"] . "</td>";
                    echo "<td><a href='approve-request.php?request_id=" . $row["id"] . "'>Approve</a></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
