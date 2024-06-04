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


function getBorrowedItemCount($conn, $user_id) {
    $sql = "SELECT COUNT(*) AS total FROM borrowed_items WHERE user_id = ? AND status IN ('Approved', 'Not Approved')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'];

    if ($total > 0) {
        return '<span class="notification-badge">' . $total . '</span>';
    } else {
        return ''; // Return an empty string if there are no pending requests
    }
}
function getPendingItemCount($conn, $user_id) {
    $sql = "SELECT COUNT(*) AS total FROM borrowed_items WHERE user_id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total = $row['total'];

    if ($total > 0) {
        return '<span class="notification-badge">' . $total . '</span>';
    } else {
        return ''; // Return an empty string if there are no pending requests
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/user-css.css">
<style>
        input[type="date"] {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 10px;
        width: 50%;
        box-sizing: border-box; /* Ensure padding and border are included in width */
    }

.notification-badge {
    background-color: red;
    color: white;
    padding: 4px 8px;
    border-radius: 50%;
    margin-left: 4px;
}

/* Modal content */
.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px; /* Adjust the maximum width as needed */
    position: relative;
}

/* Close button */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Warning text */
.modal-content p {
    margin-bottom: 20px;
}

</style>
    <title>Borrow Items</title>
</head>
<body>
    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/techno.png" alt="Logo" class="logo"></a>
        <a href="user-dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
        <a href="user-borrow.php" class="nav-item active"><span class="icon-placeholder"></span>My Request</a>
        <a href="user-pendingborrow.php" class="nav-item ">
    <span class="icon-placeholder"></span>Pending Requests
    <?php echo getPendingItemCount($conn, $_SESSION["user_id"]); ?>
</a>

<a href="user-resultborrow.php" class="nav-item ">
    <span class="icon-placeholder"></span>My Accountability
    <?php echo getBorrowedItemCount($conn, $_SESSION["user_id"]); ?>
</a>

</div>
<!-- Header box container -->
<div class="header-box">
    <div class="header-box-content">
        <!-- Navigation links -->
        <ul class="nav-links">
            <!-- Display greeting message -->
            <?php if (isset($_SESSION["user_id"])): ?>
                <li>
                    <a href="user-users.php">
                        Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
                    </a>
                </li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

    <div class="center-container">

    <div class="container">
        <h1>Available Items for Borrowing</h1>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search...">
        </div>

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
                    <td><button class="borrow-button" onclick="openModal('Creative Tools', '<?php echo $row['creative_id']; ?>')">Borrow</button></td>
                </tr>
            <?php endwhile; ?>

            <?php while ($row = $result_gadget_monitor->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['gadget_name']; ?></td>
                    <td>Gadget Monitor</td>
                    <td><?php echo $row['status']; ?></td>
                    <td><button class="borrow-button" onclick="openModal('Gadget Monitor', '<?php echo $row['gadget_id']; ?>')">Borrow</button></td>
                </tr>
            <?php endwhile; ?>

            <?php while ($row = $result_office_supplies->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['office_name']; ?></td>
                    <td>Office Supplies</td>
                    <td><?php echo $row['status']; ?></td>
                    <td><button class="borrow-button" onclick="openModal('Office Supplies', '<?php echo $row['office_id']; ?>')">Borrow</button></td>
                </tr>
            <?php endwhile; ?>

        </tbody>
    </table>

    </div>
</div>
</div>
<!-- Modal -->
<div id="returnDateModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Enter Return Date</h2>
        <div class="liability-message">
            <p><strong>Please read and acknowledge the following:</strong></p>
            <ul>
                <li>By borrowing this item, you agree to be responsible for its safekeeping and timely return.</li>
                <li>You will be held liable for any damages or loss incurred during the borrowing period.</li>
                <li>Failure to return the item by the specified return date may result in penalties or fines.</li>
                <li>Repeated failure to comply with borrowing policies may lead to suspension of borrowing privileges.</li>
            </ul>
        </div>
        <form id="returnDateForm">
            <label for="returnDate">Return Date:</label>
            <input type="date" id="returnDate" name="returnDate" required>
            <br>
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

document.addEventListener("DOMContentLoaded", function() {
        var searchInput = document.getElementById("searchInput");
        var tableRows = document.querySelectorAll(".table-container tbody tr");

        searchInput.addEventListener("input", function() {
            var searchText = searchInput.value.toLowerCase();

            tableRows.forEach(function(row) {
                var rowData = row.innerText.toLowerCase();
                if (rowData.includes(searchText)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    });

        
    </script>
</body>
</html>

           
