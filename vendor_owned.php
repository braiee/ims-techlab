<?php

session_start();
include 'db-connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
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



// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Fetch categories for dropdown
$legendsResult = $conn->query("SELECT legends_id, legends_name FROM legends");

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add vendor-owned item
    if (isset($_POST['add_vendor_owned'])) {
        $item_name = $_POST['item_name'];
        $vendor_name = $_POST['vendor_name'];
        $contact_person = $_POST['contact_person'];
        $purpose = $_POST['purpose'];
        $turnover_tsto = $_POST['turnover_tsto'];
        $return_vendor = $_POST['return_vendor'];
        $legends_id = $_POST['legends_id'];
        $project_lead = $_POST['project_lead']; // Add project_lead parameter

        $sql = "INSERT INTO vendor_owned (item_name, vendor_name, contact_person, purpose, turnover_tsto, return_vendor, legends_id, project_lead)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssis", $item_name, $vendor_name, $contact_person, $purpose, $turnover_tsto, $return_vendor,  $legends_id, $project_lead);

        if ($stmt->execute()) {
            $successMessage = "Vendor-owned item added successfully.";
        } else {
            $errorMessage = "Error adding vendor-owned item: " . $conn->error;
        }
        $stmt->close();
    }

    // Edit vendor-owned item
    if (isset($_POST['edit_vendor_owned'])) {
        $vendor_id = $_POST['edit_vendor_id'];
        $item_name = $_POST['edit_item_name'];
        $vendor_name = $_POST['edit_vendor_name'];
        $contact_person = $_POST['edit_contact_person'];
        $purpose = $_POST['edit_purpose'];
        $turnover_tsto = $_POST['edit_turnover_tsto'];
        $return_vendor = $_POST['edit_return_vendor'];
        $legends_id = $_POST['edit_legends_id'];
        $project_lead = $_POST['edit_project_lead']; // Add project_lead parameter
    
        $sql = "UPDATE vendor_owned 
                SET item_name=?, vendor_name=?, contact_person=?, purpose=?, turnover_tsto=?, return_vendor=?, legends_id=?, project_lead=?
                WHERE vendor_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssisi", $item_name, $vendor_name, $contact_person, $purpose, $turnover_tsto, $return_vendor, $legends_id, $project_lead, $vendor_id);
    
        if ($stmt->execute()) {
            $successMessage = "Vendor-owned item updated successfully.";
        } else {
            $errorMessage = "Error updating vendor-owned item: " . $conn->error;
        }
        $stmt->close();
    }

    
// Delete vendor-owned items
if (isset($_POST['delete_vendor_owned'])) {
    if (isset($_POST['vendor_ids'])) {
        $vendor_ids = $_POST['vendor_ids'];
        $vendor_ids_str = implode(',', array_fill(0, count($vendor_ids), '?'));

        // Get the username of the user performing the deletion
        $deleted_by = $_SESSION['username']; // Assuming username is stored in the session

        // Get the current timestamp
        $delete_timestamp = date('Y-m-d H:i:s');

        // Update the status to 'deleted', set Deleted By, and Delete Timestamp
        $sql = "UPDATE vendor_owned 
                SET status='deleted', deleted_by=?, delete_timestamp=? 
                WHERE vendor_id IN ($vendor_ids_str)";
        $typeString = str_repeat('s', count($vendor_ids) + 2); // +2 for deleted_by and delete_timestamp
        $stmt = $conn->prepare($sql);

        // Create an array with bind parameters dynamically
        $params = array_merge([$typeString, $deleted_by, $delete_timestamp], $vendor_ids);
        $stmt->bind_param(...$params);

        if ($stmt->execute()) {
            $successMessage = "Vendor-owned items marked as deleted successfully.";
        } else {
            $errorMessage = "Error marking vendor-owned items as deleted: " . $conn->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "No vendor-owned items selected to mark as deleted.";
    }
}

}

$sql = "SELECT vo.*, c.categories_name, l.legends_name 
        FROM vendor_owned vo
        LEFT JOIN categories c ON vo.categories_id = c.categories_id
        LEFT JOIN legends l ON vo.legends_id = l.legends_id
        WHERE vo.status != 'deleted'";
$result = $conn->query($sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/Dashboard.css">
    <link rel="stylesheet" href="css/TIcket_style.css">
    <title>Manage Vendor-Owned Items</title>
    <style>
        .btn-add, .btn-edit, .btn-delete {
            background-color:#DDF7E3;
            border: none;
    color: #5D9C59;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 4px 2px;
    cursor: pointer;
    border-radius: 4px;
}
.btn-edit {
    background-color: #DDF7E3; /* Green color */
}
.btn-edit:hover {
    background-color: #ddf7e3ac;
}
.btn-add:hover {
    background-color: #ddf7e3ac;
}

/* Additional styles for date input */
input[type="date"] {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    width: 20%;
    margin-bottom: 10px;
    font-size: 16px;
    /* Add any other styles as needed */
}
.notification-badge {
    background-color: red;
    color: white;
    padding: 4px 8px;
    border-radius: 50%;
    margin-left: 4px;
}
.success-message {
    color: #5cb85c;
    padding: 10px;
    margin-bottom: 10px;
}

.error-message {
    color: #d9534f;
    padding: 10px;
    margin-bottom: 10px;
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

.table-container td {
    text-overflow: ellipsis;
    overflow: hidden;
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
        <a href="admin-requestborrow.php" class="nav-item">
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
    <a href="gadgetMonitor.php" class="nav-item "><span class="icon-placeholder"></span>Gadgets/Devices</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item active"><span class="icon-placeholder"></span>Owned Gadgets</a>
        <span class="non-clickable-item">Settings</span>
    <a href="deleted_items.php" class="nav-item"><span class="icon-placeholder"></span>Bin</a>

</div>


<!-- Header box container -->
<div class="header-box">
    <div class="header-box-content">
        <!-- Navigation links -->
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
    </div>    </div>
</div>


<div class="main-content" style="overflow: hidden; height:650px;">
    <div class="container">
    <?php
echo '<form action="vendor_owned.php" method="post">';
echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
echo '<h1 style="color: #5D9C59;">Manage Vendor-Owned Items</h1>';
echo '<input type="submit" name="delete_vendor_owned" value="Delete" class="btn-delete" style="background-color:#DDF7E3; color:#5D9C59;">';
echo '</div>';

if (!empty($successMessage)) {
    echo '<div class="success-message">' . $successMessage . '</div>';
} elseif (!empty($errorMessage)) {
    echo '<div class="error-message">' . $errorMessage . '</div>';
}

echo '<div class="table-container">'; // Add a container div for the table

echo '<table>';
echo '<thead>';
echo '<tr>';
echo '<th></th>'; // Checkbox column
echo '<th>Item Name</th>';
echo '<th>Vendor Name</th>';
echo '<th>Con. Person</th>';
echo '<th>Purpose</th>';
echo '<th>Loc.</th>';
echo '<th>Turnover (TSTO)</th>';
echo '<th>Project Lead.</th>';
echo '<th>Date of Ret.</th>';
echo '<th>Edit</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td><input type="checkbox" name="vendor_ids[]" value="' . $row["vendor_id"] . '"></td>'; // Checkbox
        echo '<td>' . $row["item_name"] . '</td>';
        echo '<td>' . $row["vendor_name"] . '</td>';
        echo '<td>' . $row["contact_person"] . '</td>';
        echo '<td>' . $row["purpose"] . '</td>';
        echo '<td>' . $row["legends_name"] . '</td>';
        echo '<td>' . $row["turnover_tsto"] . '</td>';
        echo '<td>' . $row["project_lead"] . '</td>'; // Display Project Lead data
        echo '<td>' . $row["return_vendor"] . '</td>';
        echo '<td><button type="button" class="btn-edit" onclick="openEditModal(\'' . $row["vendor_id"] . '\', \'' . $row["item_name"] . '\', \'' . $row["vendor_name"] . '\', \'' . $row["contact_person"] . '\', \'' . $row["purpose"] . '\', \'' . $row["turnover_tsto"] . '\', \'' . $row["return_vendor"] . '\', \'' . $row["categories_id"] . '\', \'' . $row["legends_id"] . '\', \'' . $row["status"] . '\', \'' . $row["project_lead"] . '\')">Edit</button></td>'; // Include Project Lead in the edit button
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="10">No vendor-owned items found.</td></tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>'; // Close the table container

echo '</form>';
$conn->close();
?>
        <br>
        <!-- Add Button -->
        <button onclick="openAddModal()" class="btn-add">Add Vendor-Owned Item</button>
    </div>
</div>


<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddModal()">&times;</span>
        <h2>Add Vendor-Owned Item</h2>
        <form action="vendor_owned.php" method="post">
            <label for="item_name">Item Name:</label><br>
            <input type="text" id="item_name" name="item_name" required><br>
            <label for="vendor_name">Vendor Name:</label><br>
            <input type="text" id="vendor_name" name="vendor_name" required><br>
            <label for="contact_person">Contact Person:</label><br>
            <input type="text" id="contact_person" name="contact_person"><br>
            <label for="purpose">Purpose:</label><br>
            <input type="text" id="purpose" name="purpose"><br>
            <label for="turnover_tsto">Turnover (TSTO):</label><br>
            <input type="date" id="turnover_tsto" name="turnover_tsto" required><br>
            <label for="legends_id">Location:</label>
            <select id="legends_id" name="legends_id" >
                <option value="">Select Location</option>
                <?php
                // Reset legendsResult cursor
                $legendsResult->data_seek(0);
                if ($legendsResult->num_rows > 0) {
                    while($legend = $legendsResult->fetch_assoc()) {
                        echo '<option value="' . $legend["legends_id"] . '">' . $legend["legends_name"] . '</option>';
                    }
                }
                ?>
            </select><br>
            <label for="project_lead">Project Lead:</label><br>
            <input type="text" id="project_lead" name="project_lead"><br> <!-- Add Project Lead field -->
            <label for="return_vendor">Date of Return to Vendor:</label><br>
            <input type="date" id="return_vendor" name="return_vendor"><br>
            <input type="submit" name="add_vendor_owned" value="Add" class="btn-add">
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Vendor-Owned Item</h2>
        <form action="vendor_owned.php" method="post">
            <input type="hidden" id="edit_vendor_id" name="edit_vendor_id">
            <label for="edit_item_name">Item Name:</label><br>
            <input type="text" id="edit_item_name" name="edit_item_name" required><br>
            <label for="edit_vendor_name">Vendor Name:</label><br>
            <input type="text" id="edit_vendor_name" name="edit_vendor_name" required><br>
            <label for="edit_contact_person">Contact Person:</label><br>
            <input type="text" id="edit_contact_person" name="edit_contact_person"><br>
            <label for="edit_purpose">Purpose:</label><br>
            <input type="text" id="edit_purpose" name="edit_purpose"><br>
            <label for="edit_legends_id">Location:</label>
            <select id="edit_legends_id" name="edit_legends_id" >
                <option value="">Select Location</option>
                <?php
                // Reset legendsResult cursor
                $legendsResult->data_seek(0);
                if ($legendsResult->num_rows > 0) {
                    while($legend = $legendsResult->fetch_assoc()) {
                        echo '<option value="' . $legend["legends_id"] . '">' . $legend["legends_name"] . '</option>';
                    }
                }
                ?>
            </select>
            <label for="edit_turnover_tsto">Turnover (TSTO):</label><br>
            <input type="date" id="edit_turnover_tsto" name="edit_turnover_tsto" required><br>
            <label for="edit_project_lead">Project Lead:</label><br> <!-- Add Project Lead field -->
            <input type="text" id="edit_project_lead" name="edit_project_lead"><br> <!-- Add Project Lead field -->
            <label for="edit_return_vendor">Date of Return to Vendor:</label><br>
            <input type="date" id="edit_return_vendor" name="edit_return_vendor"><br>

<br><br>
            <input type="submit" name="edit_vendor_owned" value="Save" class="btn-edit">
        </form>
    </div>
</div>

<!-- JavaScript for modal functionality -->
<script>
    // Show Add Modal
    function openAddModal() {
        document.getElementById('addModal').style.display = "block";
    }

    function openEditModal(vendor_id, item_name, vendor_name, contact_person, purpose, turnover_tsto, return_vendor, categories_id, legends_id, status, project_lead) {
    // Fill in the values in the edit form
    document.getElementById('edit_vendor_id').value = vendor_id;
    document.getElementById('edit_item_name').value = item_name;
    document.getElementById('edit_vendor_name').value = vendor_name;
    document.getElementById('edit_contact_person').value = contact_person;
    document.getElementById('edit_purpose').value = purpose;
    document.getElementById('edit_turnover_tsto').value = turnover_tsto;
    document.getElementById('edit_return_vendor').value = return_vendor;
    document.getElementById('edit_project_lead').value = project_lead; // Set the value for project_lead

    
    
    // Set the selected value for legends_id
    document.getElementById('edit_legends_id').value = legends_id;


    // Display the edit modal
    document.getElementById('editModal').style.display = "block";
}

    function closeEditModal() {
        document.getElementById('editModal').style.display = "none";
    }

function closeEditModal() {
    var editModal = document.getElementById('editModal');
    fadeOut(editModal);
}

// Close Modals
function closeAddModal() {
    var addModal = document.getElementById('addModal');
    fadeOut(addModal);
}


    // Automatically close message after 1 second
    setTimeout(function () {
        var successMessage = document.querySelector('.success-message');
        var errorMessage = document.querySelector('.error-message');
        if (successMessage) {
            successMessage.style.display = "none";
        }
        if (errorMessage) {
            errorMessage.style.display = "none";
        }
    }, 2000);

    // Close modal when clicking outside of it
    window.onclick = function (event) {
        var modals = document.getElementsByClassName("modal");
        for (var i = 0; i < modals.length; i++) {
            var modal = modals[i];
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
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
