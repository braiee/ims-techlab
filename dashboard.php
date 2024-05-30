<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Retrieve username from session if set
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/DasHbOARd.css">
    <title>Dashboard</title>
    <style>
        
    </style>
</head>
<body>
<!-- Side Navigation -->
    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
        <a href="dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
        <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
        <a href="admin-borrow.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>

        <a href="category.php" class="nav-item "><span class="icon-placeholder"></span>Categories</a>
        <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Legends</a>
        <a href="vendor_owned.php" class="nav-item active"><span class="icon-placeholder"></span>Vendor-Owned</a>
        <span class="non-clickable-item">Office</span>
        <a href="#" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
        <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
        <a href="gadgetmonitor.php" class="nav-item"><span class="icon-placeholder"></span>Gadget Monitor</a>
        <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Office Supplies</a>
        <a href="#" class="nav-item"><span class="icon-placeholder"></span>Gadget Supplies</a>
        <span class="non-clickable-item">Vendors</span>
        <a href="#" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
        <span class="non-clickable-item">Summary</span>
        <a href="product.php" class="nav-item"><span class="icon-placeholder"></span>Product</a>
        <span class="non-clickable-item">Borrow</span>
        <a href="admin-requestborrow.php" class="nav-item"><span class="icon-placeholder"></span>Requests</a>
        <a href="admin-fetchrequest.php" class="nav-item"><span class="icon-placeholder"></span>Returned</a>
        <?php
        // Display settings link only for admin users
        if ($_SESSION['username'] === 'admin') {
            echo '<span class="non-clickable-item">Settings</span>';
            echo '<a href="users.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>';
        }
        ?>
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
<!-- Main Content -->
<div class="main-content">
    <!-- Greeting message -->
    <div class="greeting">
        <?php
        if (isset($_SESSION["user_id"])) {
            echo '<h1 style="color:#5D9C59;">Hello, ' . $_SESSION["username"] . '!</h1>';
        }
        ?>
    </div>
    
    <!-- Container for Cards -->
    <div class="card-container">
        <div class="card" id="productCard" onclick="loadTableContent('Product')">
            <h3>Product</h3>
            <p>View Table</p>
        </div>
        <div class="card" id="borrowCard" onclick="loadTableContent('Borrow')">
            <h3>Borrow</h3>
            <p>View Table</p>
        </div>
        <div class="card" id="userCard" onclick="loadTableContent('User')">
            <h3>User</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="loadTableContent('Office Supplies')">
            <h3>Office Supplies</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="loadTableContent('Creative Tools')">
            <h3>Office Creative Tools</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="loadTableContent('Office Devices')">
            <h3>Office Devices</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="loadTableContent('Vendor Owned Devices')">
            <h3>Vendor Owned Devices</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="loadTableContent('Summary of Devices')">
            <h3>Summary of Devices</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="loadTableContent('Legends')">
            <h3>Legends</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="loadTableContent('Categories')">
            <h3>Categories</h3>
            <p>View Table</p>
        </div>
    </div>
    
<!-- Modal for displaying table -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle" style="color: #5D9C59"></h2>
        <input type="text" id="searchInputModal" onkeyup="searchTableModal()" placeholder="Search for tickets...">
        <div id="noTicketsMessage" style="color: #FF5733; display: none;">No tickets found.</div>
        <div id="tableContentModal"></div>
    </div>
</div>

<!-- JavaScript for fetching table content and AJAX pagination -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // No automatic loading of tables on page load
});

function loadTableContent(tableType) {
    var modal = document.getElementById('myModal');
    var modalTitle = document.getElementById('modalTitle');
    modalTitle.textContent = tableType + ' Table';
    var searchInputModal = document.getElementById('searchInputModal');
    searchInputModal.style.display = 'block';

    fetchTicketsModal(1, tableType);
    modal.classList.add('fade-in');
    modal.classList.remove('fade-out');
    modal.style.display = "block";
}

function fetchTicketsModal(page, tableType) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_tickets.php?page=' + page + '&table=' + tableType, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.tickets.length > 0) {
                renderTableModal(response.tickets);
                document.getElementById('noTicketsMessage').style.display = 'none';
            } else {
                document.getElementById('noTicketsMessage').style.display = 'block';
                document.getElementById('tableContentModal').innerHTML = '';
            }
        }
    };
    xhr.send();
}

function renderTableModal(tickets) {
    var tableContentModal = document.getElementById('tableContentModal');
    var tableHtml = '<table id="ticketTableModal"><thead><tr><th>Ticket ID</th><th>Item Borrowed</th><th>Purpose</th><th>Status</th><th>Borrowed By</th><th>Date Borrowed</th></tr></thead><tbody>';
    for (var i = 0; i < tickets.length; i++) {
        tableHtml += '<tr><td>' + tickets[i].ticket_id + '</td><td>' + tickets[i].task_name + '</td><td>' + tickets[i].description + '</td><td>' + tickets[i].status + '</td><td>' + tickets[i].assigned_to + '</td><td>' + tickets[i].date_created + '</td></tr>';
    }
    tableHtml += '</tbody></table>';
    tableContentModal.innerHTML = tableHtml;
}

function searchTableModal() {
    var input, filter, table, tr, td, i, j, txtValue, found;
    input = document.getElementById("searchInputModal");
    filter = input.value.toUpperCase();
    table = document.getElementById("ticketTableModal");
    tr = table.getElementsByTagName("tr");
    found = false;

    for (i = 0; i < tr.length; i++) {
        if (tr[i].getElementsByTagName("td").length > 0) {
            td = tr[i].getElementsByTagName("td");
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            if (found) {
                tr[i].style.display = "";
                document.getElementById('noTicketsMessage').style.display = 'none';
            } else {
                tr[i].style.display = "none";
            }
        }
    }

    if (!found) {
        document.getElementById('noTicketsMessage').style.display = 'block';
    }
}

function closeModal() {
    var modal = document.getElementById('myModal');
    modal.classList.add('fade-out');
    modal.classList.remove('fade-in');

    setTimeout(function() {
        modal.style.display = "none";
    }, 500);
}
</script>

</body>
</html>
