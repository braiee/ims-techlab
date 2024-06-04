
<?php
date_default_timezone_set('Asia/Manila');

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
$successMessage = "";
$errorMessage = "";

// Check if form is submitted (for deleting office supplies)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Handle delete action
    // Get office supply IDs and update their status to "Deleted" in the database
    if (isset($_POST['office_ids'])) {
        $office_ids = $_POST['office_ids'];
        $office_ids_str = "'" . implode("','", $office_ids) . "'";
        
        // Get current user's username
        $current_user = $_SESSION['username'];
        
        // Get current timestamp
        $current_timestamp = date("Y-m-d H:i:s");
        
        $sql = "UPDATE office_supplies SET status = 'Deleted', delete_timestamp = '$current_timestamp', deleted_by = '$current_user' WHERE office_id IN ($office_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Office supplies marked as deleted successfully.";
        } else {
            $errorMessage = "Error marking office supplies as deleted: " . $conn->error;
        }
    } else {
        $errorMessage = "No office supplies selected to delete.";
    }
}

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



// SQL query to fetch office supply data excluding those with status "Deleted"
$sql = "SELECT unique_legends_id, office_id, office_name, custodian, remarks, status, categories_id, legends_id 
        FROM office_supplies
        WHERE status != 'Deleted'";
$result = $conn->query($sql);

// Fetch categories from the database
$categoriesResult = $conn->query("SELECT categories_id, categories_name FROM categories");

// Fetch legends from the database
$legendsResult = $conn->query("SELECT legends_id, legends_name FROM legends");

// Fetch status options excluding "Deleted"
$statusOptions = array("Available", "Not Available");

// Function to get category name based on category ID
function getCategoryName($category_id, $conn) {
    $sql = "SELECT categories_name FROM categories WHERE categories_id = $category_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["categories_name"];
    } else {
        return "N/A";
    }
}

// Function to get legend name based on legend ID
function getLegendName($legend_id, $conn) {
    $sql = "SELECT legends_name FROM legends WHERE legends_id = $legend_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["legends_name"];
    } else {
        return "N/A";
    }
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/DashboarD.css">
    <link rel="stylesheet" href="css/tickeT_style.css">
    <title>Manage Office Supplies</title>
</head>

<style>
    
table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: pre-wrap; /* Allow line breaks */
            word-wrap: break-word; /* Break long words */
        }
        th, td {
            max-width: 150px;
        }
        .edit-button {
        padding: 8px 16px;
        background-color: #DDF7E3;
        color: #5D9C59;
        border: none;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .edit-button:hover {
        background-color: #ddf7e3ac;
    }
    .notification-badge {
    background-color: red;
    color: white;
    padding: 4px 8px;
    border-radius: 50%;
    margin-left: 4px;
}

.success-message {
    color: #5D9C59;
    padding: 10px;
    border-radius: 5px;
}

.error-message {
    color: #D93025;
    padding: 10px;
    border-radius: 5px;
}

.unclickable {
        pointer-events: none;
        background-color: #e9ecef; /* Optional: to give a visual cue */
    }

    .dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-btn {
    background-color: #C7E8CA;
    color: #5D9C59;
    padding: 14px 20px;
    border: none;
    cursor: pointer;
    font-size: 20px;
    text-align: center;
    width: 100%;
    margin-top: -10px;
    margin-right: 10px;
}

.dropdown-btn:hover {
    transform: translateY(-5px); /* Slight lift effect on hover */
    transition: transform 0.2s;
}

.nav-links {
    list-style-type: none;
    margin: 0;
    padding: 0;
    display: none;
    flex-direction: column;
    align-items: flex-start;
    background-color: #C7E8CA;
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    z-index: 1;
}

.nav-links li {
    margin: 0;
    padding: 0;
    width: 70%;
}

.nav-links a {
    display: block;
    color: #5D9C59;
    text-align: center;
    padding: 14px 20px;
    text-decoration: none;
    transition: transform 0.2s;
    width: 100%;
    font-size: 16px;
}

.nav-links a:hover {
    transform: translateY(-5px); /* Slight lift effect on hover */
}

.show {
    display: flex;
}




</style>

<body>
<!-- Side Navigation -->
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
    <a href="officeSupplies.php" class="nav-item active"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item"><span class="icon-placeholder"></span>Gadgets/Devices</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
        <span class="non-clickable-item">Settings</span>
    <a href="deleted_items.php" class="nav-item"><span class="icon-placeholder"></span>Bin</a>

</div>

<!-- Header box container -->
<div class="header-box">
    <div class="header-box-content">
    <div class="dropdown">
        <button class="dropdown-btn">Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!
</button>
        <ul class="nav-links dropdown-content">
            <!-- Display greeting message -->
            <?php if (isset($_SESSION["user_id"])): ?>
                <li>
                    <a href="users.php">
                        Settings    
                    </a>
                </li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>

    </div>
</div>



<div class="main-content" style="overflow: hidden; height:650px;">
    
<div class="container">
    <form action="" method="post">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h2 style="color: #5D9C59;">Manage Office Supplies</h2>
            <input type="submit" name="delete" value="Delete">
        </div>

        <?php
        if ($result->num_rows > 0) {
            // Start of table-container
            echo '<div class="table-container">';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>';
            echo '<th>Asset ID</th>';
            echo '<th>Item Name</th>';
            echo '<th>Loc.</th>';
            echo '<th>Cat.</th>';
            echo '<th>Cust.</th>';
            echo '<th>Rem.</th>';
            echo '<th>Status</th>';
            echo '<th>Action</th>'; // New column for actions
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td><input type="checkbox" name="office_ids[]" value="' . $row["office_id"] . '"></td>'; // Checkbox
                echo '<td>' . $row["unique_legends_id"] . '</td>'; // Display unique_id
                echo '<td>' . $row["office_name"] . '</td>';
                echo '<td>' . getLegendName($row["legends_id"], $conn) . '</td>'; // Display legend name
                echo '<td>' . getCategoryName($row["categories_id"], $conn) . '</td>'; // Display category name
                echo '<td>' . $row["custodian"] . '</td>';
                echo '<td>' . $row["remarks"] . '</td>';
                echo '<td>' . $row["status"] . '</td>'; // Display status
                echo '<td><button class="edit-button" type="button" onclick="editOfficeSupply(' . $row["office_id"] . ', \'' . addslashes($row["office_name"]) . '\', \'' . addslashes($row["custodian"]) . '\', \'' . addslashes($row["remarks"]) . '\', \'' . $row["categories_id"] . '\', \'' . $row["legends_id"] . '\', \'' . $row["status"] . '\', \'' . $row["unique_legends_id"] . '\')">Edit</button></td>';
                echo '</tr>';
            }

            // End of table-container
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            // No office supplies found
            echo "<div class='table-container'>";
            echo "<table>";
            echo "<thead>";
            echo "<tr>";
            echo "<th></th>"; // Empty cell for checkbox
            echo "<th>Asset ID</th>";
            echo "<th>Item Name</th>";
            echo "<th>Loc.</th>";
            echo "<th>Cat.</th>";
            echo "<th>Cust.</th>";
            echo "<th>Rem.</th>";
            echo "<th>Status</th>";
            echo "<th>Action</th>"; // New column for actions
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            // Output an empty row to keep the table structure visible
            echo "<tr>";
            echo "<td colspan='9'>No office supplies found.</td>"; // Span all columns
            echo "</tr>";
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        }
        $conn->close();
        ?>

        <!-- Button to open modal -->
    </form>
    <button class="assign-button" onclick="openModal()">Add Office Supply</button>

</div>
</div>

<!-- The Modal for Adding Office Supplies -->
<div id="assignModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Add a New Office Supply</h3>
        <form action="crudOfficeSupplies.php" method="post">
            <label for="office_name">Office Supply Name:</label>
            <input type="text" id="office_name" name="office_name" placeholder="Enter office supply name"><br>
            <label for="custodian">Custodian:</label>
            <input type="text" id="custodian" name="custodian" placeholder="Enter custodian" >
            <label for="remarks">Remarks:</label><br>
            <input type="text" id="remarks" name="remarks" placeholder="Enter remarks"></input>

            <label for="categories_id">Category:</label>
            <select id="categories_id" name="categories_id" required>
                <option value="">Select Category</option>
                <?php
                // Reset categoriesResult cursor
                $categoriesResult->data_seek(0);
                // Check if there are any categories
                if ($categoriesResult->num_rows > 0) {
                    // Loop through each category
                    while ($category = $categoriesResult->fetch_assoc()) {
                        // Output option for each category
                        echo '<option value="' . htmlspecialchars($category["categories_id"]) . '">' . htmlspecialchars($category["categories_name"]) . '</option>';
                    }
                }
                ?>
            </select>
            <label for="legends_id">Location:</label>
            <select id="legends_id" name="legends_id" required>
            <option value="">Select Location</option>

                <?php
                // Fetch and populate legends from the database
                while ($legend = $legendsResult->fetch_assoc()) {
                    echo '<option value="' . $legend['legends_id'] . '">' . $legend['legends_name'] . '</option>';
                }
                ?>
            </select>
            <label for="status">Status:</label>
            <select id="status" name="status">
                <?php
                // Populate status options
                foreach ($statusOptions as $status) {
                    echo '<option value="' . $status . '">' . $status . '</option>';
                }
                ?>
            </select>

            <input type="submit" class="assign-button" value="Add Office Supply" name="add_office_supply">
        </form>
    </div>
</div>

<!-- Modal for Editing Office Supplies -->
<div id="editModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h3>Edit Office Supply</h3>
        <form action="crudOfficeSupplies.php" method="post">
            <input type="hidden" id="edit_office_id" name="edit_office_id">

            <label for="edit_office_name">Office Supply Name:</label>
            <input type="text" id="edit_office_name" name="edit_office_name" placeholder="Enter office supply name" ><br>

            <label for="edit_custodian">Custodian:</label>
            <input type="text" id="edit_custodian" name="edit_custodian" placeholder="Enter custodian" ><br>

            <label for="edit_remarks">Remarks:</label><br>
            <input type="text" id="edit_remarks" name="edit_remarks" placeholder="Enter remarks"><br>

            <label for="edit_categories_id">Category:</label>
                <select class="unclickable" name="edit_categories_id" id="edit_categories_id" >
                <?php
                // Fetch and populate categories from the database
                $categoriesResult->data_seek(0); // Reset pointer to the beginning
                while ($category = $categoriesResult->fetch_assoc()) {
                    echo '<option value="' . $category['categories_id'] . '">' . $category['categories_name'] . '</option>';
                }
                ?>
            </select><br>

            <label for="edit_legends_id">Location:</label>
                <select name="edit_legends_id" id="edit_legends_id" class="unclickable">
                <?php
                // Fetch and populate legends from the database
                $legendsResult->data_seek(0); // Reset pointer to the beginning
                while ($legend = $legendsResult->fetch_assoc()) {
                    echo '<option value="' . $legend['legends_id'] . '">' . $legend['legends_name'] . '</option>';
                }
                ?>
            </select><br>

            <label for="edit_status">Status:</label>
            <select id="edit_status" name="edit_status">
                <?php
                // Populate status options
                foreach ($statusOptions as $status) {
                    echo '<option value="' . $status . '">' . $status . '</option>';
                }
                ?>
            </select><br>

            <input type="submit" class="edit-button" value="Save Changes" name="edit_office_supply">
        </form>
    </div>
</div>

<!-- JavaScript for modal functionality -->
<script>


    // Get the modals
    var modal = document.getElementById('assignModal');
    var editModal = document.getElementById('editModal');


    // When the user clicks the button, open the modal 
    function openModal() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    function closeModal() {
        modal.style.display = "none";
    }




// Show edit modal
function editOfficeSupply(office_id, office_name, custodian, remarks, categories_id, legends_id, status) {
    document.getElementById('edit_office_id').value = office_id;
    document.getElementById('edit_office_name').value = office_name;
    document.getElementById('edit_custodian').value = custodian;
    document.getElementById('edit_remarks').value = remarks;
    document.getElementById('edit_categories_id').value = categories_id;
    document.getElementById('edit_legends_id').value = legends_id;
    document.getElementById('edit_status').value = status;

    document.getElementById('editModal').style.display = "block";
}

function closeEditModal() {
    document.getElementById('editModal').style.display = "none";
}


// Automatically hide success and error messages after 2 seconds
setTimeout(function(){
    var successMessage = document.querySelector('.success-message');
    var errorMessage = document.querySelector('.error-message');
    if(successMessage) {
        successMessage.style.display = 'none';
    }
    if(errorMessage) {
        errorMessage.style.display = 'none';
    }
}, 2000);


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

    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('input[name="office_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
    }

    document.addEventListener('DOMContentLoaded', (event) => {
    const dropdownBtn = document.querySelector('.dropdown-btn');
    const dropdownContent = document.querySelector('.dropdown-content');

    dropdownBtn.addEventListener('click', () => {
        dropdownContent.classList.toggle('show');
    });

    // Close the dropdown if the user clicks outside of it
    window.addEventListener('click', (event) => {
        if (!event.target.matches('.dropdown-btn')) {
            if (dropdownContent.classList.contains('show')) {
                dropdownContent.classList.remove('show');
            }
        }
    });
});

</script>

</body>
</html>
