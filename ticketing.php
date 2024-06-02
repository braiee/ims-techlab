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
    <link rel="stylesheet" href="css/TickeT_STylE.css">

    <title>Borrow Item</title>
</head>

<body>
<!-- Side Navigation -->
<div class="side-nav">
<a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="ticketing.php" class="nav-item active"><span class="icon-placeholder"></span>Borrow</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
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

            // Check if the user is logged in
            if (isset($_SESSION["user_id"])) {
                // Display greeting message with username
                echo '<li>Hello, ' . $_SESSION["username"] . '!</li>';
                echo '<li><a href="logout.php">Logout</a></li>';
            }
            ?>
        </ul>
    </div>
</div>

<div class="main-ticket">

    <div class="container">
    <?php
if ($result->num_rows > 0) {
    echo '<form action="" method="post">';
    echo '<div style="display: flex; justify-content: space-between; align-items: center;">';
    echo '<h2 style="color: #5D9C59;">Borrowed Item Tickets</h2>';
    echo '<input type="submit" class="delete-btn" name="delete" value="Delete" margin-right: 10px;">';
    echo '</div>';
    echo '<div class="table-container">'; // Add a container div for the table

    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>'; // Checkbox column
    echo '<input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes(this)">';
    echo '</th>'; // Checkbox column
    echo '<th>Ticket ID</th>';
    echo '<th>Item Borrowed</th>';
    echo '<th>Purpose</th>';
    echo '<th>Status</th>';
    echo '<th>Borrowed By</th>';
    echo '<th>Date Borrowed</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td><input type="checkbox" name="ticket_ids[]" value="' . $row["ticket_id"] . '"></td>'; // Checkbox
        echo '<td>' . $row["ticket_id"] . '</td>';
        echo '<td>' . $row["task_name"] . '</td>';
        echo '<td>' . $row["description"] . '</td>';
        echo '<td>' . $row["status"] . '</td>';
        echo '<td>' . $row["assigned_to"] . '</td>';
        echo '<td>' . $row["date_created"] . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>'; // Close the table container
    echo '</form>';
    
    // Check if delete action is triggered and no checkboxes are selected
    if (isset($_POST['delete']) && empty($_POST['ticket_ids'])) {
        echo '<p id="error-message" style="color: red;">Please select a ticket to delete.</p>';
    }
} else {
    echo "No tickets found.";
}
?>

        <!-- Button to open modal -->
        <button class="assign-button" onclick="openModal()">Borrow</button>
    </div>
</div>
    <!-- The Modal -->
    <div id="assignModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 style="color: #5D9C59;">Borrow an Item</h3>
            <form action="process_assign_ticket.php" method="post">
                <input type="hidden" id="date_created" name="date_created" value="<?php echo date("Y-m-d H:i:s"); ?>">

                <label style="color: #5D9C59;" for="task_name">Item Borrowed:</label>
                <select id="task_name" name="task_name">
                    <option value="VR Goggles">VR Goggles</option>
                    <option value="Laptop">Laptop</option>
                    <option value="Drone">Drone</option>
                </select>

                <label for="description">Purpose:</label>
                <input type="text"  name="description" placeholder="Enter description" required>
                <br>

                <label style="color: #5D9C59;" for="status">Status:</label>
                <select id="status" name="status" placeholder="Enter description">
                    <option value="Borrowed">Borrowed</option>
                    <option value="For Request">For request</option>
                    <option value="Returned">Returned</option>
                </select>

                <label style="color: #5D9C59;" for="assigned_to">Borrowed By:</label>
                <select id="assigned_to" name="assigned_to">
                    <option value="Jermaine">Jermaine</option>
                    <option value="Ambraie">Ambraie</option>
                    <option value="Melvin">Melvin</option>
                </select>
                <input type="submit" class="assign-button" value="Borrow">
            </form>
        </div>
    </div>

    <!-- JavaScript for modal functionality -->
    <script>
        // Get the modal
        var modal = document.getElementById('assignModal');

        // When the user clicks the button, open the modal 
        function openModal() {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        function closeModal() {
            modal.style.display = "none";
        }

        function toggleAllCheckboxes(masterCheckbox) {
            var checkboxes = document.getElementsByName('ticket_ids[]');
            
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = masterCheckbox.checked;
            }
        }

        // Hide the error message after 3 seconds
        window.onload = function() {
            var errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                setTimeout(function() {
                    errorMessage.style.display = 'none';
                }, 3000);
            }
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
</body>
</html>
