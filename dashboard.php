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
    <link rel="stylesheet" href="css/DashboaRd.css">
    <title>Dashboard</title>
    <style>
    
    </style>
</head>
<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item active"><span class="icon-placeholder"></span>Dashboard</a>
    <a href="creativeTools.php" class="nav-item active"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Category</a>
    <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
    <a href="logs.php" class="nav-item"><span class="icon-placeholder"></span>Logs</a>
    <a href="#user.php" class="nav-item"><span class="icon-placeholder"></span>User</a>
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
        <!-- Cards -->
        <div class="card" onclick="toggleTableSection()">
            <h3>Product</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="toggleTableSection()">
            <h3>Borrow</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="toggleTableSection()">
            <h3>User</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="toggleTableSection()">
            <h3>Product</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="toggleTableSection()">
            <h3>Borrow</h3>
            <p>View Table</p>
        </div>
        <div class="card" onclick="toggleTableSection()">
            <h3>User</h3>
            <p>View Table</p>
        </div>
    </div>
    
    <!-- Table Section -->
    <div id="tableSection">
        <h2 style="color: #5D9C59">Borrowed Item Tickets</h2>
        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for tickets...">
        <div id="tableContent"></div>
        <div class="pagination" id="pagination"></div>
        <br>
    </div>
</div>

<!-- JavaScript for toggling table visibility and AJAX pagination -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchTickets(1);
    });

    function toggleTableSection() {
        var tableSection = document.getElementById('tableSection');
        if (tableSection.style.display === "none") {
            tableSection.style.display = "block";
        } else {
            tableSection.style.display = "none";
        }
    }

    function fetchTickets(page) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_tickets.php?page=' + page, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);
                renderTable(response.tickets);
                renderPagination(response.total_pages, page);
            }
        };
        xhr.send();
    }

    function renderTable(tickets) {
        var tableContent = document.getElementById('tableContent');
        var tableHtml = '<table id="ticketTable"><thead><tr><th>Ticket ID</th><th>Item Borrowed</th><th>Purpose</th><th>Date Borrowed</th><th>Status</th><th>Borrowed By</th><th>Date Created</th></tr></thead><tbody>';
        for (var i = 0; i < tickets.length; i++) {
            tableHtml += '<tr><td>' + tickets[i].ticket_id + '</td><td>' + tickets[i].task_name + '</td><td>' + tickets[i].description + '</td><td>' + tickets[i].due_date + '</td><td>' + tickets[i].status + '</td><td>' + tickets[i].assigned_to + '</td><td>' + tickets[i].date_created + '</td></tr>';
        }
        tableHtml += '</tbody></table>';
        tableContent.innerHTML = tableHtml;
    }

    function renderPagination(totalPages, currentPage) {
        var pagination = document.getElementById('pagination');
        var paginationHtml = '';
        for (var page = 1; page <= totalPages; page++) {
            if (page == currentPage) {
                paginationHtml += '<span>' + page + '</span> ';
            } else {
                paginationHtml += '<a href="#" onclick="fetchTickets(' + page + '); return false;">' + page + '</a> ';
            }
        }
        pagination.innerHTML = paginationHtml;
    }

    function searchTable() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("ticketTable");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            tr[i].style.display = "none";
            td = tr[i].getElementsByTagName("td");
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        break;
                    }
                }
            }
        }
    }
</script>

</body>
</html>
