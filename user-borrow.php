<?php
session_start();
include 'db-connect.php'; // Include your database connection script

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Fetch available items from creative_tools table
$sql_creative_tools = "SELECT * FROM creative_tools WHERE status = 'Available'";
$result_creative_tools = $conn->query($sql_creative_tools);

// Fetch available items from gadget_monitor table
$sql_gadget_monitor = "SELECT * FROM gadget_monitor WHERE status = 'Available'";
$result_gadget_monitor = $conn->query($sql_gadget_monitor);

// Fetch available items from office_supplies table
$sql_office_supplies = "SELECT * FROM office_supplies WHERE status = 'Available'";
$result_office_supplies = $conn->query($sql_office_supplies);

// Fetch available items from vendor_owned table
$sql_vendor_owned = "SELECT * FROM vendor_owned WHERE status = 'Available'";
$result_vendor_owned = $conn->query($sql_vendor_owned);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user-css.css">

    <title>Borrow Items</title>
</head>
<body>
<div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
        <a href="user-dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
        <a href="user-borrow.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
        <a href="user-pendingborrow.php" class="nav-item"><span class="icon-placeholder"></span>Pending</a>
        <a href="user-resultborrow.php" class="nav-item"><span class="icon-placeholder"></span>Result</a>
        <span class="non-clickable-item">Settings</span>
        <a href="user-users.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>
    </div>
    <div class="header-box">
        <div class="header-box-content">
            <ul class="nav-links">
                <?php
                if (isset($_SESSION["user_id"])) {
                    echo '<li>Hello, ' . $_SESSION["username"] . '!</li>';
                    echo '<li><a href="logout.php">Logout</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="container">
        <h1>Available Items for Borrowing</h1>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
            <?php while ($row = $result_creative_tools->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['creative_name']; ?></td>
                    <td>Creative Tools</td>
                    <td><?php echo $row['status']; ?></td>
                    <td><button onclick="openModal('Creative Tools', '<?php echo $row['creative_id']; ?>')">Borrow</button></td>
                </tr>
            <?php endwhile; ?>

            <?php while ($row = $result_gadget_monitor->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['gadget_name']; ?></td>
                    <td>Gadget Monitor</td>
                    <td><?php echo $row['status']; ?></td>
                    <td><button onclick="openModal('Gadget Monitor', '<?php echo $row['gadget_id']; ?>')">Borrow</button></td>
                </tr>
            <?php endwhile; ?>

            <?php while ($row = $result_office_supplies->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['office_name']; ?></td>
                    <td>Office Supplies</td>
                    <td><?php echo $row['status']; ?></td>
                    <td><button onclick="openModal('Office Supplies', '<?php echo $row['office_id']; ?>')">Borrow</button></td>
                </tr>
            <?php endwhile; ?>

            <?php while ($row = $result_vendor_owned->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['item_name']; ?></td>
                    <td>Vendor Owned</td>
                    <td><?php echo $row['status']; ?></td>
                    <td><button onclick="openModal('Vendor Owned', '<?php echo $row['vendor_id']; ?>')">Borrow</button></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Modal -->
    <div id="returnDateModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Enter Return Date</h2>
            <form id="returnDateForm">
                <label for="returnDate">Return Date:</label>
                <input type="datetime-local" id="returnDate" name="returnDate" required>
                <button type="submit" class="borrow-button">Borrow</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(category, itemId) {
            var modal = document.getElementById("returnDateModal");
            modal.style.display = "block";
            modal.setAttribute("data-category", category);
            modal.setAttribute("data-itemId", itemId);
        }

        function closeModal() {
            var modal = document.getElementById("returnDateModal");
            modal.style.display = "none";
        }

        document.getElementById("returnDateForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent form submission
    var returnDate = document.getElementById("returnDate").value;
    var category = document.getElementById("returnDateModal").getAttribute("data-category");
    var itemId = document.getElementById("returnDateModal").getAttribute("data-itemId");
    var userId = <?php echo $_SESSION["user_id"]; ?>; // Get the user_id from session
    borrowItem(category, itemId, returnDate, userId); // Pass user_id to borrowItem function
});

        function borrowItem(category, itemId, returnDate, userId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "submit_request.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert(xhr.responseText);
            location.reload();
        }
    };
    // Include userId in the data sent to the server
    xhr.send("category=" + encodeURIComponent(category) + "&item_id=" + encodeURIComponent(itemId) + "&return_date=" + encodeURIComponent(returnDate) + "&user_id=" + encodeURIComponent(userId));
}

        
    </script>
</body>
</html>

           
