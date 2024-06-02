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
        /* Style for making the table responsive */
        .modal-content {
            max-width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            th, td {
                display: block;
                width: 100%;
            }
            tr {
                display: block;
                margin-bottom: 1rem;
            }
            tr:nth-child(odd) {
                background-color: #f9f9f9;
            }
            th {
                background-color: transparent;
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            td {
                border: none;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            td:before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: 45%;
                padding-right: 10px;
                text-align: left;
                white-space: nowrap;
            }
        }

        .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .pagination a {
        color: #555;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        transition: background-color .3s;
        border: 1px solid #ddd;
        margin: 0 4px;
    }

    .pagination a.active {
        background-color: #4CAF50;
        color: white;
        border: 1px solid #4CAF50;
    }


       /* Pagination styles */
       #pagination {
        margin-top: 20px;
        text-align: center;
    }

    .pagination-link {
        display: inline-block;
        padding: 8px 16px;
        text-decoration: none;
        color: #5D9C59;
        border: 1px solid #5D9C59;
        border-radius: 4px;
        margin-right: 5px;
    }

    .pagination-link.active {
        background-color: #5D9C59;
        color: white;
    }

    .pagination-link:hover {
        background-color: #5D9C59;
        color: white;
    }


    </style>


</head>
<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link">        <img src="assets/img/techno.png" alt="Logo" class="logo">
</a>
    <a href="dashboard.php" class="nav-item active"><span class="icon-placeholder"></span>Dashboard</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Borrow</span>
        <a href="admin-borrow.php" class="nav-item"><span class="icon-placeholder"></span>Requests</a>
        <a href="admin-requestborrow.php" class="nav-item"><span class="icon-placeholder"></span>Approval</a>
        <a href="admin-fetchrequest.php" class="nav-item"><span class="icon-placeholder"></span>Returned</a>
    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item"><span class="icon-placeholder"></span>Device Monitors</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
        <span class="non-clickable-item">Settings</span>
    <a href="users.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>
    <a href="deleted_items.php" class="nav-item"><span class="icon-placeholder"></span>Bin</a>



</div>
<!-- Header box container -->
<div class="header-box">
    <div class="header-box-content">
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

    <Br>
    <br>
    
    <!-- Container for Cards -->
    <div class="card-container">
    <div class="card" onclick="loadTableContent('Product')">
            <h3>Product</h3>
            <p>View Table</p>
        </div>
        <div class="card" id="borrowCard" onclick="loadTableContent('Borrowed Items')">
            <h3>Borrowed Items</h3>
            <p>View Table</p>
        </div>
        <div class="card" id="suppliesCard" onclick="loadTableContent('Supplies')">
            <h3>Office Supplies</h3>
            <p>View Table</p>
        </div>
        <div class="card" id="creativeToolsCard" onclick="loadTableContent('Creative Tools')">
            <h3>Office Creative Tools</h3>
            <p>View Table</p>
        </div>
        <div class="card" id="deviceMonitorsCard" onclick="loadTableContent('Device Monitors')">
            <h3>Device Monitors</h3>
            <p>View Table</p>
        </div>
        <div class="card" id="vendorOwnedCard" onclick="loadTableContent('Vendor Owned Devices')">
            <h3>Vendor Owned Devices</h3>
            <p>View Table</p>
        </div>

        <div class="card" id="locationCard" onclick="loadTableContent('Location')">
            <h3>Location</h3>
            <p>View Table</p>
        </div>
        <div class="card" id="categoriesCard" onclick="loadTableContent('Categories')">
            <h3>Categories</h3>
            <p>View Table</p>
        </div>
    </div>
    
<!-- Modal for displaying table -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle" style="color: #5D9C59"></h2>
        <input type="text" id="searchInputModal" onkeyup="searchTableModal()" placeholder="Search for records...">
        <div id="noTicketsMessage" style="color: #FF5733; display: none;">No records found.</div>
        <div id="tableContentModal"></div>
        <div id="pagination" class="pagination">
    <a href="#" class="pagination-link" id="prevPageBtn" onclick="navigatePage(-1)">Prev</a>
    <!-- Pagination links will be dynamically generated here -->
    <a href="#" class="pagination-link" id="nextPageBtn" onclick="navigatePage(1)">Next</a>
</div>


        </div>
    </div>
</div>

<!-- JavaScript for fetching table content and AJAX pagination -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // No automatic loading of table content
});

function loadTableContent(table) {
    var modal = document.getElementById('myModal');
    var modalTitle = document.getElementById('modalTitle');
    var tableContentModal = document.getElementById('tableContentModal');
    var noTicketsMessage = document.getElementById('noTicketsMessage');
    var pagination = document.getElementById('pagination');

    modalTitle.textContent = table + ' Table';
    tableContentModal.innerHTML = '';
    noTicketsMessage.style.display = 'none';
    pagination.innerHTML = '';

    fetchTableData(table, 1); // Load first page of the table data
    modal.style.display = 'block';
}

function fetchTableData(table, page) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_tickets.php?table=' + table + '&page=' + page, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            var tableContentModal = document.getElementById('tableContentModal');
            var noTicketsMessage = document.getElementById('noTicketsMessage');
            var pagination = document.getElementById('pagination');

            var tableContent = '';
            if (table === 'Borrowed Items') {
                 tableContent = generateBorrowedItemsTable(response.borrowed_items);
            } else if (table === 'Device Monitors') {
                tableContent = generateDeviceMonitorsTable(response.gadget_monitor);
            } else if (table === 'Location') {
                tableContent = generateLocationTable(response.locations);
            } else if (table === 'Vendor Owned Devices') {
                tableContent = generateVendorOwnedTable(response.vendor);
            } else if (table === 'Categories') {
                tableContent = generateCategoriesTable(response.categories);
            } else if (table === 'Supplies') {
                tableContent = generateSuppliesTable(response.supplies);
            } else if (table === 'Creative Tools') {
                tableContent = generateCreativeToolsTable(response.creative_tools);
            } else if (table === 'Product') { // Handle 'Product' table
                tableContent = generateProductTable(response.products); // Generate product table
            }


            tableContentModal.innerHTML = tableContent;

            if (response[table.toLowerCase().replace(' ', '_')] && response[table.toLowerCase().replace(' ', '_')].length === 0) {
                noTicketsMessage.style.display = 'block';
            } else {
                noTicketsMessage.style.display = 'none';
            }

            pagination.innerHTML = generatePagination(table, response.total_pages, page);
        }
    };
    xhr.send();
}


function generateBorrowedItemsTable(data) {
    var tableContent = '<table><thead><tr><th>Item Name</th><th>Status</th><th>Username</th></tr></thead><tbody>';
    data.forEach(function(item) {
        tableContent += '<tr><td>' + item.item_name + '</td><td>' + item.status + '</td><td>' + item.username + '</td></tr>';
    });
    tableContent += '</tbody></table>';
    return tableContent;
}


function generateProductTable(data) {
    var tableContent = '<table><thead><tr><th>Tagging</th><th>Asset Number</th><th>Item Name</th><th>Owner</th><th>Status</th></tr></thead><tbody>';
    data.forEach(function(item) {
        tableContent += '<tr><td>' + item.tagging + '</td><td>' + item.asset_number + '</td><td>' + item.name + '</td><td>' + item.owner + '</td><td>' + item.status + '</td></tr>';
    });
    tableContent += '</tbody></table>';
    return tableContent;
}


function generateDeviceMonitorsTable(data) {
    var tableContent = '<table><thead><tr><th>Gadget Name</th><th>Cateogry</th><th>Color</th><th>IMEI</th><th>SN</th><th>Custodian</th><th>RNSS Acc</th><th>Condition</th><th>Purpose</th><th>Remarks</th><th>Location</th><th>Status</th></tr></thead><tbody>';
    data.forEach(function(item) {
        tableContent += '<tr><td>' + item.gadget_name + '</td><td>' + item.categories_name + '</td><td>' + item.color + '</td><td>' + item.emei + '</td><td>' + item.sn + '</td><td>' + item.custodian + '</td><td>' + item.rnss_acc + '</td><td>' + item.condition + '</td><td>' + item.purpose + '</td><td>' + item.remarks + '</td><td>' + item.legends_name + '</td><td>' + item.status + '</td></tr>';
    });
    tableContent += '</tbody></table>';
    return tableContent;
}


function generateLocationTable(data) {
    var tableContent = '<table><thead><tr><th>Legends Name</th><th>Abbreviation</th></tr></thead><tbody>';
    data.forEach(function(item) {
        tableContent += '<tr><td>' + item.legends_name + '</td><td>' + item.abv + '</td></tr>';
    });
    tableContent += '</tbody></table>';
    return tableContent;
}


function generateVendorOwnedTable(data) {
    var tableContent = '<table><thead><tr><th>Item Name</th><th>Vendor Name</th><th>Contact Person</th><th>Purpose</th><th>Turnover to TSTO</th><th>Return to Vendor</th></tr></thead><tbody>';
    data.forEach(function(item) {
        tableContent += '<tr><td>' + item.item_name + '</td><td>' + item.vendor_name + '</td><td>' + item.contact_person + '</td><td>' + item.purpose + '</td><td>' + item.turnover_tsto + '</td><td>' + item.return_vendor + '</td></tr>';
    });
    tableContent += '</tbody></table>';
    return tableContent;
}

function generateCategoriesTable(data) {
    var tableContent = '<table><thead><tr><th>Categories Name</th><th>Abbreviations</th></tr></thead><tbody>';
    data.forEach(function(item) {
        tableContent += '<tr><td>' + item.categories_name + '</td><td>' + item.abv + '</td></tr>';
    });
    tableContent += '</tbody></table>';
    return tableContent;
}

function generateSuppliesTable(data) {
    var tableContent = '<table><thead><tr><th>Item Name</th><th>Category</th><th>Location</th><th>Custodian</th><th>Remarks</th><th>Status</th><th>Date Added</th></tr></thead><tbody>';
    data.forEach(function(item) {
        tableContent += '<tr><td>' + item.office_name + '</td><td>' + item.categories_name + '</td><td>' + item.legends_name + '</td><td>' + item.custodian + '</td><td>' + item.remarks + '</td><td>' + item.status + '</td><td>' + item.date_added + '</td></tr>';
    });
    tableContent += '</tbody></table>';
    return tableContent;
}

function generateCreativeToolsTable(data) {
    var tableContent = '<table><thead><tr><th>Creative ID</th><th>Creative Name</th><th>IMEI</th><th>SN</th><th>Custodian</th><th>RNSS Acc</th><th>Remarks</th><th>Descriptions</th><th>Category</th><th>Location</th></tr></thead><tbody>';
    data.forEach(function(item) {
        tableContent += '<tr><td>' + item.creative_id + '</td><td>' + item.creative_name + '</td><td>' + item.emei + '</td><td>' + item.sn + '</td><td>' + item.custodian + '</td><td>' + item.rnss_acc + '</td><td>' + item.remarks + '</td><td>' + item.descriptions + '</td><td>' + item.categories_name + '</td><td>' + item.legends_name + '</td></tr>';
    });
    tableContent += '</tbody></table>';
    return tableContent;
}


function generatePagination(table, totalPages, currentPage) {
    var paginationContent = '';

    // Number of pages to display
    var numPagesToShow = 5;

    // Calculate the start and end pages
    var startPage = Math.max(1, currentPage - Math.floor(numPagesToShow / 2));
    var endPage = Math.min(totalPages, startPage + numPagesToShow - 1);

    // Adjust the start and end pages if needed to display numPagesToShow pages
    if (endPage - startPage + 1 < numPagesToShow) {
        startPage = Math.max(1, endPage - numPagesToShow + 1);
    }

    // Previous button
    if (currentPage > 1) {
        paginationContent += '<a href="#" class="pagination-link" onclick="fetchTableData(\'' + table + '\', ' + (currentPage - 1) + ')">Prev</a>';
    }

    // Ellipsis if start page is greater than 1
    if (startPage > 1) {
        paginationContent += '<span class="pagination-ellipsis">...</span>';
    }

    // Page links
    for (var i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            paginationContent += '<a href="#" class="pagination-link active" onclick="fetchTableData(\'' + table + '\', ' + i + ')">' + i + '</a>';
        } else {
            paginationContent += '<a href="#" class="pagination-link" onclick="fetchTableData(\'' + table + '\', ' + i + ')">' + i + '</a>';
        }
    }

    // Ellipsis if end page is less than total pages
    if (endPage < totalPages) {
        paginationContent += '<span class="pagination-ellipsis">...</span>';
    }

    // Next button
    if (currentPage < totalPages) {
        paginationContent += '<a href="#" class="pagination-link" onclick="fetchTableData(\'' + table + '\', ' + (currentPage + 1) + ')">Next</a>';
    }

    return paginationContent;
}
function closeModal() {
    var modal = document.getElementById('myModal');
    modal.style.display = 'none';
}

function searchTableModal() {
    var input, filter, table, tr, td, i, j, txtValue;
    input = document.getElementById('searchInputModal');
    filter = input.value.toUpperCase();
    table = document.querySelector('#tableContentModal table');
    tr = table.getElementsByTagName('tr');

    for (i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
        tr[i].style.display = 'none'; // Hide all rows initially

        td = tr[i].getElementsByTagName('td');
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = ''; // Show the row if a match is found
                    break; // Exit the loop to avoid redundant checks
                }
            }
        }
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
