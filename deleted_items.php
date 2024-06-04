<?php
session_start();
include 'db-connect.php';

// Initialize variables
$filter = "";
$search = "";
$items_per_page = 20; // Number of items per page

// Get the current page or set default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Check if filter is applied
if (isset($_POST['filter'])) {
    $filter = $_POST['filter'];
    // Reset current page to 1 whenever a filter is applied
    $current_page = 1;
}

$offset = ($current_page - 1) * $items_per_page;

function getTotalItemCount($conn, $table_name) {
    $sql = "SELECT COUNT(*) AS total FROM $table_name WHERE status = 'Pending'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total = $row['total'];

    if ($total > 0) {
        return '<span class="notification-badge">' . $total . '</span>';
    } else {
        return ''; // Return an empty string if there are no items awaiting approval
    }
}

function getTotalFetchRequestCount($conn) {
    $sql = "SELECT COUNT(*) AS total FROM borrowed_items WHERE status = 'Returned'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total = $row['total'];

    if ($total > 0) {
        return '<span class="notification-badge">' . $total . '</span>';
    } else {
        return ''; // Return an empty string if there are no items awaiting approval
    }
}

// Check if search term is provided
if (isset($_POST['search'])) {
    $search = $_POST['search'];
}

$sql = "SELECT 
combined.source, 
combined.name, 
combined.category, 
combined.descriptions, 
combined.color, 
combined.imei, 
combined.sn, 
combined.owner, 
combined.uniqueid, 
IFNULL(
    (SELECT status 
     FROM borrowed_items 
     WHERE borrowed_items.item_id = combined.id 
       AND borrowed_items.status = 'Deleted' 
     LIMIT 1), 
    'Deleted'
) AS status,
categories.categories_name
FROM (
SELECT 
    office_id AS id, 
    'Office Supplies' AS source, 
    office_name AS name, 
    categories_id AS category, 
    NULL AS descriptions, 
    NULL AS color, 
    NULL AS imei, 
    NULL AS sn, 
    custodian AS owner, 
    unique_legends_id AS uniqueid, 
    status
FROM office_supplies
UNION ALL
SELECT 
    gadget_id AS id, 
    'Gadget and Devices' AS source, 
    gadget_name AS name, 
    categories_id AS category, 
    NULL AS descriptions, 
    color, 
    emei AS imei, 
    sn, 
    custodian AS owner, 
    unique_gadget_id AS uniqueid, 
    status
FROM gadget_monitor
UNION ALL
SELECT 
    creative_id AS id, 
    'Creative Tools' AS source, 
    creative_name AS name, 
    categories_id AS category, 
    descriptions, 
    NULL AS color, 
    emei AS imei, 
    sn, 
    custodian AS owner, 
    unique_creative_id AS uniqueid, 
    status
FROM creative_tools
UNION ALL
SELECT 
    vendor_id AS id, 
    'Vendor Owned' AS source, 
    item_name AS name, 
    categories_id AS category, 
    NULL AS descriptions, 
    NULL AS color, 
    NULL AS imei, 
    NULL AS sn, 
    contact_person AS owner, 
    unique_vendor_id AS uniqueid, 
    status
FROM vendor_owned
) AS combined
LEFT JOIN categories ON combined.category = categories.categories_id";



// Add filter condition
$conditions = [];
$params = [];
$types = "";

if ($filter != "") {
    $conditions[] = "combined.source = ?";
    $params[] = $filter;
    $types .= "s";
}

// Add search condition
if ($search != "") {
    $search_condition = "(combined.name LIKE ? OR combined.descriptions LIKE ? OR combined.color LIKE ? OR combined.imei LIKE ? OR combined.sn LIKE ? OR combined.owner LIKE ? OR combined.uniqueid LIKE ? OR categories.categories_name LIKE ?)";
    $conditions[] = $search_condition;
    $search_param = "%$search%";
    $params = array_merge($params, array_fill(0, 8, $search_param));
    $types .= str_repeat("s", 8);
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Prepare the statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters
if ($types != "") {
    $bindResult = $stmt->bind_param($types, ...$params);
    if (!$bindResult) {
        die("Error binding parameters: " . $stmt->error);
    }
}

// Execute the statement
$executeResult = $stmt->execute();
if (!$executeResult) {
    die("Error executing statement: " . $stmt->error);
}

// Get result
$result = $stmt->get_result();

if (!$result) {
    die("Invalid query: " . $conn->error);
}

$total_items = $result->num_rows;

// Add LIMIT clause for pagination
$sql .= " LIMIT ?, ?";
$offset_params = array_merge($params, [$offset, $items_per_page]);
$offset_types = $types . "ii";

// Prepare the statement with LIMIT
$stmt = $conn->prepare($sql);

// Bind parameters including LIMIT
$stmt->bind_param($offset_types, ...$offset_params);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Invalid query: " . $conn->error);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/category.css">
    <title>User Dashboard</title>
    <style>

.notification-badge {
    background-color: red;
    color: white;
    padding: 4px 8px;
    border-radius: 50%;
    margin-left: 4px;
}
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            margin-left: 200px;
            margin-top: 100px;
            margin-bottom: 10px;
        }

        .main-content {
            width: 80%;
            max-width: 1200px;
            margin: 0 auto;
            background-color: #C7E8CA;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow-y: hidden;
        }

        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }

        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .pagination li {
            margin: 0 5px;
        }

        .pagination a {
            text-decoration: none;
            color: #555;
            background-color: #f9f9f9;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .pagination a:hover {
            background-color: #5D9C59;
            color: #fff;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 150px;
            margin-left: 30px;
            height: 10px;
        }

        .card {
            background-color: #DDF7E3;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex: 1 1 calc(50% - 20px);
            max-width: calc(50% - 20px);
            text-align: center;
            pointer-events: none; /* Disable pointer events */
        }

        @media (max-width: 768px) {
            .card {
                flex: 1 1 calc(100% - 20px);
                max-width: calc(100% - 20px);
            }
        }

        .search-filter-form {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-filter-form input[type="text"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

        .search-filter-form button {
            padding: 8px 12px;
            background-color: #DDF7E3; /* Blue */
            color: #5D9C59;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-filter-form button:hover {
            background-color: #ddf7e3ac;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-left: 10px;
        }

        .filter-buttons button {
            padding: 8px 12px;
            background-color: #f0f0f0;
            color: #555;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filter-buttons button:hover {
            background-color: #5D9C59;
            color: #fff;
        }

        .filter-buttons .active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0; 
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
padding-top: 60px;
}
.modal-content {
        background-color: #fefefe;
        margin: 5% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Could be more or less, depending on screen size */
    }

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
</style>
</head>
<body>
    <!-- Side Navigation -->
    <div class="side-nav">
    <a href="#" class="logo-link">        <img src="assets/img/techno.png" alt="Logo" class="logo">
</a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Borrow</span>
        <a href="admin-borrow.php" class="nav-item "><span class="icon-placeholder"></span>Requests</a>
        <a href="admin-requestborrow.php" class="nav-item ">
    <span class="icon-placeholder"></span>Approval
    <?php
    // Get the total count of items awaiting approval
    $totalItems = getTotalItemCount($conn, 'borrowed_items');
    // Display the total count with a notification badge
    echo $totalItems;
    ?>
</a>       
<a href="admin-fetchrequest.php" class="nav-item <?php echo ($_SERVER['PHP_SELF'] == '/admin-fetchrequest.php') ? 'active' : ''; ?>">
    <span class="icon-placeholder"></span>Returned
    <?php echo getTotalFetchRequestCount($conn); ?>
</a>    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item "><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item "><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item"><span class="icon-placeholder"></span>Gadgets/Devices</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
        <span class="non-clickable-item">Settings</span>
        
    <a href="deleted_items.php" class="nav-item active"><span class="icon-placeholder"></span>Bin</a>

</div>

<!-- Header box container -->
<div class="header-box">
    <div class="header-box-content">
        <!-- Navigation links -->
        <ul class="nav-links">
            <!-- Display greeting message -->
            <?php if (isset($_SESSION["user_id"])): ?>
                <li>
                    <a href="users.php">
                        Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
                    </a>
                </li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>


<div class="wrapper">
    <div class="main-content">
        <!-- Search and Filter Form -->
        <form method="POST" id="searchFilterForm" class="search-filter-form">
        <input type="text" name="search" id="searchInput" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
        <div class="filter-buttons">
        <button type="button" onclick="applyFilter('Creative Tools')" class="<?php echo ($filter == 'Creative Tools') ? 'active' : ''; ?>">Creative Tools</button>
    <button type="button" onclick="applyFilter('Office Supplies')" class="<?php echo ($filter == 'Office Supplies') ? 'active' : ''; ?>">Office Supplies</button>
    <button type="button" onclick="applyFilter('Gadget and Devices')" class="<?php echo ($filter == 'Gadget and Devices') ? 'active' : ''; ?>">Gadget and Devices</button>
    <button type="button" onclick="applyFilter('Vendor Owned')" class="<?php echo ($filter == 'Vendor Owned') ? 'active' : ''; ?>">Vendor Owned</button>
    <button type="button" onclick="applyFilter('')" class="<?php echo ($filter == '') ? 'active' : ''; ?>">All</button>
    <input type="hidden" name="filter" id="filterInput" value="<?php echo htmlspecialchars($filter); ?>">

        </div>
        <button type="submit" style="display: none;">Submit</button>
    </form>
        <div class="table-container">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Tagging</th>
                        <th>Asset Number</th>
                        <th>Item Name</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Output data of each row
                    $result->data_seek(0); // Reset result pointer
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["source"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["uniqueid"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["owner"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["status"] ? $row["status"] : 'Available') . "</td>";
                        echo "<td><button class='view-data-btn' data-source='" . htmlspecialchars($row["source"]) . "' data-name='" . htmlspecialchars($row["name"]) . "' data-owner='" . htmlspecialchars($row["owner"]) . "' data-status='" . htmlspecialchars($row["status"]) . "' data-unique='" . htmlspecialchars($row["uniqueid"]) . "' data-category-name='" . htmlspecialchars($row["categories_name"]) . "'>View Data</button></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <ul class="pagination">
            <?php
            // Calculate the total number of pages
            $total_pages = ceil($total_items / $items_per_page);

            // Display pagination links
            for ($page = 1; $page <= $total_pages; $page++) {
                if ($page == $current_page) {
                    echo "<li><strong>$page</strong></li>";
                } else {
                    echo "<li><a href=\"?page=$page\">$page</a></li>";
                }
            }
            // Add "Next" button
            if ($current_page < $total_pages) {
                $next_page = $current_page + 1;
                echo "<li><a href=\"?page=$next_page\">&gt;</a></li>";
            }
            ?>
        </ul>
    </div>
</div>


<!-- Modal -->
<div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Item Details</h2>
            <p id="item-details"></p>
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // Get all "View Data" buttons
       // Get all "View Data" buttons
var viewDataButtons = document.querySelectorAll(".view-data-btn");

// When the user clicks on a "View Data" button, open the modal and display the data
viewDataButtons.forEach(function(button) {
    button.onclick = function() {
        var itemDetails = `
            <strong>Source:</strong> ${this.getAttribute("data-source")}<br>
            <strong>Asset Number:</strong> ${this.getAttribute("data-unique")}<br>
            <strong>Name:</strong> ${this.getAttribute("data-name")}<br>
            <strong>Category:</strong> ${this.getAttribute("data-category-name")}<br>
            <strong>Descriptions:</strong> ${this.getAttribute("data-descriptions")}<br>
            <strong>Color:</strong> ${this.getAttribute("data-color")}<br>
            <strong>IMEI:</strong> ${this.getAttribute("data-imei")}<br>
            <strong>Serial Number:</strong> ${this.getAttribute("data-sn")}<br>
            <strong>Owner:</strong> ${this.getAttribute("data-owner")}<br>
            <strong>Status:</strong> ${this.getAttribute("data-status")}<br>
        `;

        document.getElementById("item-details").innerHTML = itemDetails;
        modal.style.display = "block";
    };
});


        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        };

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };

    // Get the search input field
    var searchInput = document.getElementById("searchInput");
    var typingTimer; // Timer identifier
    var doneTypingInterval = 500; // Time in milliseconds (0.5 seconds)

    // Add event listener for input change
    searchInput.addEventListener("input", function() {
        clearTimeout(typingTimer); // Clear the previous timer

        typingTimer = setTimeout(function() {
            // Submit the form after a brief delay when the user stops typing
            searchInput.form.submit();
        }, doneTypingInterval);
    });

    // Prevent form submission when pressing Enter key
    searchInput.form.addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent the default form submission behavior
    });


    function applyFilter(filter) {
        document.getElementById('filterInput').value = filter;
        document.getElementById('searchFilterForm').submit();
    }

    </script>
</body>
</html>