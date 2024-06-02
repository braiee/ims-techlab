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
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : "";
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : "";

// Clear message variables from session
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// SQL query to fetch legends data
$sql = "SELECT legends_id, legends_name FROM legends";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/Dashboard.css">
    <link rel="stylesheet" href="css/Ticket_style.css">
    <title>Manage Location</title>
</head>

<body>
<!-- Side Navigation -->
<div class="side-nav">
<a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="ticketing.php" class="nav-item "><span class="icon-placeholder"></span>Borrow</a>
    <a href="category.php" class="nav-item "><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item active"><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item "><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item"><span class="icon-placeholder"></span>Device Monitors</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
    <span class="non-clickable-item">Summary</span>
    <a href="product.php" class="nav-item"><span class="icon-placeholder"></span>Product</a>
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

<!-- Success and Error Modal -->
<div id="messageModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeMessageModal()">&times;</span>
        <div id="messageContent"></div>
    </div>
</div>

<div class="main-content">
    <div class="container">
    
        <form action="crudLegends.php" method="post">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h2 style="color: #5D9C59;">Manage Location</h2>
                <div>
                    <input type="submit" name="delete_legend" value="Delete" class="btn-delete">
                </div>
            </div>
            <div class="message-container">
            <?php
            if (!empty($successMessage)) {
                echo '<div class="success-message">' . $successMessage . '</div>';
            } elseif (!empty($errorMessage)) {
                echo '<div class="error-message">' . $errorMessage . '</div>';
            }
            ?>
        </div>

            <?php
            if ($result->num_rows > 0) {
                echo '<table>';
                echo '<thead>';
                echo '<tr>';
                echo '<th></th>'; // Checkbox column
                echo '<th>Location</th>';
                echo '<th>Action</th>'; // Edit button column
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><input type="checkbox" name="delete_legend_id[]" value="' . $row["legends_id"] . '"></td>'; // Checkbox
                    echo '<td>' . $row["legends_name"] . '</td>';
                    echo '<td><button type="button" onclick="showEditLegendModal(' . $row["legends_id"] . ', \'' . $row["legends_name"] . '\')" class="btn-edit">Edit</button></td>'; // Edit button
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
            } else {
                echo "No legends found.";
            }
            $conn->close();
            ?>
        </form>

        <!-- Add button at the bottom left -->
        <button type="button" onclick="showAddLegendModal()" class="btn-add" style="margin-top: 20px;">Add</button>
    </div>
</div>

<!-- Add Legend Modal -->
<div id="addLegendModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddLegendModal()">&times;</span>
        <form action="crudLegends.php" method="post">
            <h2>Add Location</h2>
            <label for="legend_name">Location Name:</label>
            <input type="text" id="legend_name" name="legend_name" required>
            <input type="submit" name="add_legend" value="Add Legend">
        </form>
    </div>
</div>

<!-- Edit Legend Modal -->
<div id="editLegendModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditLegendModal()">&times;</span>
        <form action="crudLegends.php" method="post">
            <h2>Edit Location</h2>
            <input type="hidden" id="edit_legend_id" name="edit_legend_id">
            <label for="edit_legend_name">Location Name:</label>
            <input type="text" id="edit_legend_name" name="edit_legend_name" required>
            <input type="submit" name="edit_legend" value="Edit Legend">
        </form>
    </div>
</div>

<!-- JavaScript for modal functionality -->
<script>
    var addLegendModal = document.getElementById('addLegendModal');
    var editLegendModal = document.getElementById('editLegendModal');

// Function to hide messages after 2 seconds
function hideMessages() {
        var messages = document.querySelectorAll('.success-message, .error-message');
        messages.forEach(function(message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, 2000);
        });
    }

    // Call the function when the page loads
    window.onload = function() {
        hideMessages();
    }; 

    // Show Add Legend Modal
    function showAddLegendModal() {
        addLegendModal.style.display = "block";
    }

    // Close Add Legend Modal
    function closeAddLegendModal() {
        addLegendModal.style.display = "none";
    }

    // Show Edit Legend Modal
    function showEditLegendModal(legend_id, legend_name) {
        document.getElementById('edit_legend_id').value = legend_id;
        document.getElementById('edit_legend_name').value = legend_name;
        editLegendModal.style.display = "block";
    }

    // Close Edit Legend Modal
    function closeEditLegendModal() {
        editLegendModal.style.display = "none";
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        var modals = document.getElementsByClassName("modal");
        for (var i = 0; i < modals.length; i++) {
            var modal = modals[i];
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }
</script>

<!-- CSS for buttons (for illustration purposes, you can adjust as needed) -->
<style>
    .btn-add, .btn-delete, .btn-edit {
        padding: 10px 15px;
        margin-right: 10px;
        color: #5D9C59;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-add {
        background-color: #DDF7E3;
    }
    .btn-edit {
        background-color: #DDF7E3; /* Green color */
    }
    .btn-edit:hover{
                background-color: #ddf7e3ac;
            }
            
            .btn-add:hover{
                background-color: #ddf7e3ac;
            }


.success-message {
    color: #5D9C59;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
}

.error-message {
    color: #D8000C;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
}

    
</style>

</body>
</html>
