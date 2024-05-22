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

// Check if form is submitted (for deleting office supplies)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Handle delete action
    // Get office supply IDs and delete them from the database
    if (isset($_POST['office_ids'])) {
        $office_ids = $_POST['office_ids'];
        $office_ids_str = "'" . implode("','", $office_ids) . "'";
        $sql = "DELETE FROM office_supplies WHERE office_id IN ($office_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Office supplies deleted successfully.";
        } else {
            $errorMessage = "Error deleting office supplies: " . $conn->error;
        }
    } else {
        $errorMessage = "No office supplies selected to delete.";
    }
}

// SQL query to fetch office supply data
$sql = "SELECT office_id, office_name, qty, emei, sn, ref_rnss, owner, custodian, rnss_acc, remarks FROM office_supplies";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css"> <!-- Apply dashboard styles -->
    <link rel="stylesheet" href="css/category.css"> <!-- Apply category styles -->
    <title>Manage Office Supplies</title>
</head>

<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
    <a href="product.php" class="nav-item"><span class="icon-placeholder"></span>Products</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
    <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Ticketing</a>
    <a href="logs.php" class="nav-item"><span class="icon-placeholder"></span>Logs</a>
    <a href="user.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>
    <a href="officeSupplies.php" class="nav-item active"><span class="icon-placeholder"></span>Office Supplies</a>
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

<div class="main-content">
    <div class="container">
        <?php
        if (!empty($successMessage)) {
            echo '<div class="success">' . $successMessage . '</div>';
        } elseif (!empty($errorMessage)) {
            echo '<div class="error">' . $errorMessage . '</div>';
        }

        if ($result->num_rows > 0) {
            echo '<form action="" method="post">';
            echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
            echo '<h2 style="color: #5D9C59;">Manage Office Supplies</h2>';
            echo '<input type="submit" name="delete" value="Delete">';
            echo '</div>';
            echo '<table>';
            
            echo '<thead>';
            echo '<tr>';
            echo '<th></th>'; // Checkbox column
            echo '<th>ID</th>';
            echo '<th>Name</th>';
            echo '<th>Quantity</th>';
            echo '<th>EMEI</th>';
            echo '<th>SN</th>';
            echo '<th>Ref RNSS</th>';
            echo '<th>Owner</th>';
            echo '<th>Custodian</th>';
            echo '<th>RNSS Acc</th>';
            echo '<th>Remarks</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td><input type="checkbox" name="office_ids[]" value="' . $row["office_id"] . '"></td>'; // Checkbox
                echo '<td>' . $row["office_id"] . '</td>';
                echo '<td>' . $row["office_name"] . '</td>';
                echo '<td>' . $row["qty"] . '</td>';
                echo '<td>' . $row["emei"] . '</td>';
                echo '<td>' . $row["sn"] . '</td>';
                echo '<td>' . $row["ref_rnss"] . '</td>';
                echo '<td>' . $row["owner"] . '</td>';
                echo '<td>' . $row["custodian"] . '</td>';
                echo '<td>' . $row["rnss_acc"] . '</td>';
                echo '<td>' . $row["remarks"] . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</form>';
        } else {
            echo "<p>No office supplies found.</p>";
        }
        $conn->close();
        ?>
        <!-- Button to add new office supply -->
        <a href="addOfficeSupply.php" class="button">Add New Office Supply</a>
    </div>
</div>

</body>
</html>
