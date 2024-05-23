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

// Check if form is submitted (for deleting gadgets)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        // Handle delete action
        // Get gadget IDs and delete them from the database
        if (isset($_POST['gadget_ids'])) {
            $gadget_ids = $_POST['gadget_ids'];
            $gadget_ids_str = "'" . implode("','", $gadget_ids) . "'";
            $sql = "DELETE FROM gadget_monitor WHERE gadget_id IN ($gadget_ids_str)";

            if ($conn->query($sql) === TRUE) {
                $successMessage = "Gadgets deleted successfully.";
            } else {
                $errorMessage = "Error deleting gadgets: " . $conn->error;
            }
        } else {
            $errorMessage = "No gadgets selected to delete.";
        }
    } elseif (isset($_POST['add'])) {
        // Redirect to add gadget page
        header("Location: add_gadget.php");
        exit();
    }
}

// SQL query to fetch gadget data
$sql = "SELECT gadget_id, gadget_name, categories_id, color, qty, emei, sn, ref_rnss, owner, custodian, rnss_acc, `condition`, purpose, remarks FROM gadget_monitor";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/category.css">
    <title>Manage Gadget Monitor</title>
</head>

<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
    <a href="product.php" class="nav-item"><span class="icon-placeholder"></span>Product</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Category</a>
    <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
    <a href="logs.php" class="nav-item"><span class="icon-placeholder"></span>Logs</a>
    <a href="user.php" class="nav-item"><span class="icon-placeholder"></span>User</a>
    <a href="gadgetmonitor.php" class="nav-item active"><span class="icon-placeholder"></span>Gadget Monitor</a>
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
        ?>
        
        <form action="" method="post">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h2 style="color: #5D9C59;">Manage Gadget Monitor</h2>
                <button type="submit" name="delete">Delete</button>
                <button type="submit" name="add">Add</button>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th></th> <!-- Checkbox column -->
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category ID</th>
                        <th>Color</th>
                        <th>Quantity</th>
                        <th>EMEI</th>
                        <th>SN</th>
                        <th>Ref RNSS</th>
                        <th>Owner</th>
                        <th>Custodian</th>
                        <th>RNSS Acc</th>
                        <th>Condition</th>
                        <th>Purpose</th>
                        <th>Remarks</th>
                        <th>Actions</th> <!-- New header for actions -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td><input type="checkbox" name="gadget_ids[]" value="' . $row["gadget_id"] . '"></td>'; // Checkbox
                        echo '<td>' . $row["gadget_id"] . '</td>';
                        echo '<td>' . $row["gadget_name"] . '</td>';
                        echo '<td>' . $row["categories_id"] . '</td>';
                        echo '<td>' . $row["color"] . '</td>';
                        echo '<td>' . $row["qty"] . '</td>';
                        echo '<td>' . $row["emei"] . '</td>';
                        echo '<td>' . $row["sn"] . '</td>';
                        echo '<td>' . $row["ref_rnss"] . '</td>';
                        echo '<td>' . $row["owner"] . '</td>';
                        echo '<td>' . $row["custodian"] . '</td>';
                        echo '<td>' . $row["rnss_acc"] . '</td>';
                        echo '<td>' . $row["condition"] . '</td>';
                        echo '<td>' . $row["purpose"] . '</td>';
                        echo '<td>' . $row["remarks"] . '</td>';
                        echo '<td><a href="edit_gadget.php?id=' . $row["gadget_id"] . '">Edit</a></td>'; // Edit button
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </form>
        
        <?php
        if ($result->num_rows == 0) {
            echo "<p>No gadgets found.</p>";
        }
        ?>
    </div>
</div>

</body>
</html>

<?php
$conn->close();
?>
