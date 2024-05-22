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
    <link rel="stylesheet" href="css/DaShbOARd.css">
    <title>Dashboard</title>
    <style>
          /* Hide the title and search bar by default */
          #tableTitle, #searchInput {
            display: none;
        }
        /* Styling for the modal */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            padding-top: 60px;
            
        }

        /* Modal content */
        .modal-content {
            background-color: #C7E8CA;
            margin: 5% auto; /* 15% from the top and centered */
            padding: 20px;
            width: 80%; /* Could be more or less, depending on screen size */

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

        #ticketTableModal {
            width: 100%;
            border-collapse: collapse;
            border-radius: 56px;
        }

        #ticketTableModal th,
        #ticketTableModal td {
            padding: 8px;
            text-align: left;
        }

        #ticketTableModal th {
            background-color: #f2f2f2;
        }

        #ticketTableModal tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        #searchInputModal {
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
            box-sizing: border-box;
            width: 15%;
        }

           /* Style for no tickets found message */
           #noTicketsFoundMessage {
            display: none;
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item active"><span class="icon-placeholder"></span>Dashboard</a>
    <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Categories</a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Legends</a>
    <span class="non-clickable-item">Office</span> <!-- Non-clickable and unhoverable item -->
    <a href="#" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
    <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="#" class="nav-item"><span class="icon-placeholder"></span>Gadget Supplies</a>
    <span class="non-clickable-item">Vendors</span> <!-- Non-clickable and unhoverable item -->
    <a href="#" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
    <span class="non-clickable-item">Summary</span> <!-- Non-clickable and unhoverable item -->
    <a href="#" class="nav-item"><span class="icon-placeholder"></span>Product</a>
</div>
<!-- Header box container -->
<div class="header-box">
    <div class="header-box-content">
        <!-- Navigation links -->
        <ul class="nav-links">
            <li><a href="logout.php">Logout</a></li>
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
        <!-- Cards with IDs for each section -->
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
    searchInputModal.style.display = 'block'; // Show the search bar when loading a table

    fetchTicketsModal(1, tableType);
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
                document.getElementById('noTicketsMessage').style.display = 'none'; // Hide message if tickets found
            } else {
                document.getElementById('noTicketsMessage').style.display = 'block'; // Display message if no tickets found
                document.getElementById('tableContentModal').innerHTML = ''; // Clear table content
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
    found = false; // Initialize found flag

    for (i = 0; i < tr.length; i++) {
        if (tr[i].getElementsByTagName("td").length > 0) { // Skip if it's a table heading row
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
                tr[i].style.display = ""; // Show row if match found
                document.getElementById('noTicketsMessage').style.display = 'none'; // Hide message if match found
            } else {
                tr[i].style.display = "none"; // Hide row if no match found
            }
        }
    }

    if (!found) {
        document.getElementById('noTicketsMessage').style.display = 'block'; // Display message if no match found
    }
}
function closeModal() {
    var modal = document.getElementById('myModal');
    modal.style.display = "none";
}

</script>


</body>
</html>