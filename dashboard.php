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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboarD.css">
    <title>Dashboard</title>
    <style>
        /* Hide the table section initially */
        #tableSection {
            display: none;
        }
    </style>
</head>
<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item active"><span class="icon-placeholder"></span>Dashboard</a>
    <a href="#" class="nav-item"><span class="icon-placeholder"></span>Product</a>
    <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
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
    </div>
    
    <!-- Table Section -->
    <div id="tableSection">
        <h2>Borrowed Item Tickets</h2>
        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for tickets...">
        <?php
        // Include database connection
        include 'db-connect.php';

        // SQL query to fetch ticket data
        $sql = "SELECT ticket_id, task_name, description, due_date, status, assigned_to, date_created FROM ticketing_table";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<table id="ticketTable">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Ticket ID</th>';
            echo '<th>Item Borrowed</th>';
            echo '<th>Purpose</th>';
            echo '<th>Date Borrowed</th>';
            echo '<th>Status</th>';
            echo '<th>Borrowed By</th>';
            echo '<th>Date Created</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row["ticket_id"] . '</td>';
                echo '<td>' . $row["task_name"] . '</td>';
                echo '<td>' . $row["description"] . '</td>';
                echo '<td>' . $row["due_date"] . '</td>';
                echo '<td>' . $row["status"] . '</td>';
                echo '<td>' . $row["assigned_to"] . '</td>';
                echo '<td>' . $row["date_created"] . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo "No tickets found.";
        }
        // Close connection
        $conn->close();
        ?>
    </div>
</div>

<!-- JavaScript for toggling table visibility -->
<script>
    function toggleTableSection() {
        var tableSection = document.getElementById('tableSection');
        if (tableSection.style.display === "none") {
            tableSection.style.display = "block";
        } else {
            tableSection.style.display = "none";
        }
    }

    function searchTable() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("ticketTable");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td");
            for (var j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        break;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    }
</script>

</body>
</html>
