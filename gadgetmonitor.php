<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db-connect.php';

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
$categoriesResult = $conn->query("SELECT categories_id, categories_name FROM categories");
$legendsResult = $conn->query("SELECT legends_id, legends_name FROM legends");

// Delete gadget
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_gadget'])) {
    // Retrieve gadget IDs to delete
    $gadget_ids = $_POST['gadget_ids'];

    // Get current user's username
    $current_user = $_SESSION['username'];

    // Update status to "Deleted" and set delete_timestamp and deleted_by for each selected gadget
    $current_timestamp = date("Y-m-d H:i:s");
    foreach ($gadget_ids as $gadget_id) {
        $sql = "UPDATE gadget_monitor SET status = 'Deleted', delete_timestamp = '$current_timestamp', deleted_by = '$current_user' WHERE gadget_id=$gadget_id";
        if ($conn->query($sql) !== TRUE) {
            $errorMessage .= "Error marking gadget as deleted: " . $conn->error . "<br>";
        }
    }

    if (empty($errorMessage)) {
        $successMessage = "Gadgets marked as deleted successfully.";
    }
}



// SQL query to fetch gadget data excluding items with status "Deleted"
$sql = "SELECT 
            gm.unique_gadget_id,
            gm.gadget_id, 
            gm.gadget_name, 
            gm.categories_id, 
            c.categories_name, 
            gm.color, 
            gm.emei, 
            gm.sn,  
            gm.custodian, 
            gm.type,
            gm.rnss_acc, 
            gm.`condition`, 
            gm.purpose, 
            gm.remarks, 
            gm.legends_id, 
            l.legends_name, 
            gm.status,
            gm.ref_rnss,
            gm.owner
        FROM gadget_monitor gm 
        LEFT JOIN categories c ON gm.categories_id = c.categories_id
        LEFT JOIN legends l ON gm.legends_id = l.legends_id
        WHERE gm.status != 'Deleted'";

$result = $conn->query($sql);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/CategorY.css">
    <title>Manage Gadget Monitor</title>

    <style>
                th{
            color: #5D9C59;
            background-color: #f2f2f2;
        }

        .table-container {
            max-height: 400px; /* Set a maximum height for the table container */
            overflow-y: auto; /* Enable vertical scrolling */
        }
        .modal-content {
            margin: 5% auto; /* 5% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 30%; /* Could be more or less, depending on screen size */
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .view-button {
            background-color: #DDF7E3;
            color: #5D9C59;
            border: none;
            padding: 5px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 2px 1px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .view-button:hover {
            background-color: #ddf7e3ac;
        }

        .view-content p {
            margin: 10px 0;
        }

        .view-content strong {
            display: inline-block;
            width: 150px;
            color: black;
        }

        .assign-modal-content {
            background-color: #C7E8CA;
            margin: 5% auto; /* 5% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width:30%; /* Could be more or less, depending on screen size */
            border-radius: 10px;
        }

        .assign-modal-content h3 {
            margin-top: 0;
            color: #5D9C59;
        }

        .assign-modal-content label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }

        .assign-modal-content input[type="text"],
        .assign-modal-content input[type="number"],
        .assign-modal-content textarea,
        .assign-modal-content select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Ensure padding and border do not increase width */
        }

        .assign-modal-content input[type="submit"] {
            background-color: #5D9C59;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin-top: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .notification-badge {
    background-color: red;
    color: white;
    padding: 4px 8px;
    border-radius: 50%;
    margin-left: 4px;
}

        .assign-modal-content input[type="submit"]:hover {
            background-color: #4CAF50;
        }

        .assign-modal-content .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .assign-modal-content .close:hover,
        .assign-modal-content .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

            /* Style for select dropdowns */
    select {
        width: 95%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        margin-bottom: 10px;
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
    <a href="gadgetMonitor.php" class="nav-item active"><span class="icon-placeholder"></span>Gadgets/Devices</a>
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
    <?php
if (!empty($successMessage)) {
    echo '<script>showMessageModal("success", "' . $successMessage . '")</script>';
} elseif (!empty($errorMessage)) {
    echo '<script>showMessageModal("error", "' . $errorMessage . '")</script>';
}

echo '<form action="" method="post">';
echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
echo '<h1 style="color: #5D9C59;">Manage Gadget Monitor</h1>';
echo '<input type="submit" name="delete_gadget" value="Delete">';
echo '</div>';

echo '<div class="table-container">'; // Add a container div for the table

echo '<table>';
echo '<thead>';
echo '<tr>';
echo '<th></th>'; // Checkbox column
echo '<th>Asset ID</th>';

echo '<th>Item</th>';
echo '<th>Location</th>';
echo '<th>Category</th>';
echo '<th>Type</th>';

echo '<th>Status</th>';
echo '<th>Ref RNSS</th>'; // New column for actions
echo '<th>Owner</th>'; // New column for actions

echo '<th>Action</th>'; // New column for actions
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// Output data of each row
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td><input type="checkbox" name="gadget_ids[]" value="' . $row["gadget_id"] . '"></td>'; // Checkbox
        echo '<td>' . $row["unique_gadget_id"] . '</td>';
        echo '<td>' . $row["gadget_name"] . '</td>';
        echo '<td>' . $row["legends_name"] . '</td>';
        echo '<td>' . $row["categories_name"] . '</td>';
        echo '<td>' . $row["type"] . '</td>';

        echo '<td>' . $row["status"] . '</td>'; // Display status
        echo '<td>' . $row["ref_rnss"] . '</td>';
        echo '<td>' . $row["owner"] . '</td>';

        // Action buttons
        echo '<td>';
        echo '<button type="button" class="view-button" onclick=\'openViewModal(' . json_encode($row) . ')\'>View</button>';
        echo '<button type="button" class="view-button edit-button" onclick=\'openEditModal(' . json_encode($row) . ')\'>Edit</button>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="10">No gadgets found.</td></tr>';
}
echo '</tbody>';
echo '</table>';
echo '</div>';

echo '</form>';
$conn->close();
?>

<!-- Button to open modal -->
        <button class="assign-button" onclick="openModal()">Add Gadget</button>
    </div>
</div>

<!-- The Modal for Viewing Gadgets -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeViewModal()">&times;</span>
        <h3>View Gadget Details</h3>
        <div id="viewContent" class="view-content">
            <table>
                <tr>
                    <td><strong>Asset ID:</strong></td>
                    <td id="gadgetId"></td>
                </tr>
                <tr>
                    <td><strong>Name:</strong></td>
                    <td id="gadgetName"></td>
                </tr>
                <tr>
                    <td><strong>Category:</strong></td>
                    <td id="gadgetCategory"></td>
                </tr>
                <tr>
                    <td><strong>Type:</strong></td>
                    <td id="gadgetType"></td>
                </tr>
                <tr>
                    <td><strong>Color:</strong></td>
                    <td id="gadgetColor"></td>
                </tr>

                <tr>
                    <td><strong>IMEI:</strong></td>
                    <td id="gadgetEMEI"></td>
                </tr>
                <tr>
                    <td><strong>Serial Number:</strong></td>
                    <td id="gadgetSN"></td>
                </tr>
        
                <tr>
                    <td><strong>Custodian:</strong></td>
                    <td id="gadgetCustodian"></td>
                </tr>
                <tr>
                    <td><strong>RNSS Account:</strong></td>
                    <td id="gadgetRNSSAcc"></td>
                </tr>
                <tr>
                    <td><strong>Condition:</strong></td>
                    <td id="gadgetCondition"></td>
                </tr>
                <tr>
                    <td><strong>Purpose:</strong></td>
                    <td id="gadgetPurpose"></td>
                </tr>
                <tr>
                    <td><strong>Remarks:</strong></td>
                    <td id="gadgetRemarks"></td>
                </tr>
                <tr>
                    <td><strong>Location:</strong></td>
                    <td id="gadgetLegends"></td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td id="gadgetStatus"></td>
                </tr>
                <tr>
                    <td><strong>Ref RNSS:</strong></td>
                    <td id="gadgetRefRNSS"></td>
                </tr>
                <tr>
                    <td><strong>Owner:</strong></td>
                    <td id="gadgetOwner"></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h3>Edit Gadget Details</h3>
        <div id="editContent" class="edit-content">
            <form action="crudgadgetMonitor.php" method="post">
                <!-- Populate fields with existing data -->
                <input type="hidden" id="edit_gadget_id" name="edit_gadget_id">
                
                <label for="edit_gadget_name">Gadget Name:</label>
                <input type="text" id="edit_gadget_name" name="edit_gadget_name"><br>
                
                <label for="edit_categories_id">Category:</label>
                <select class="unclickable" name="edit_categories_id" id="edit_categories_id" >
                    <option value="">Select Category</option>
                    <?php
                    // Loop through categoriesResult to fetch and display categories options
                    while ($category = $categoriesResult->fetch_assoc()) {
                        echo '<option value="' . $category['categories_id'] . '">' . $category['categories_name'] . '</option>';
                    }
                    ?>
                </select><br>

                <label for="edit_legends_id">Location:</label>
                <select name="edit_legends_id" id="edit_legends_id" class="unclickable">
                    <option value="">Select Location</option>
                    <?php
                    // Loop through legendsResult to fetch and display legends options
                    while ($legend = $legendsResult->fetch_assoc()) {
                        echo '<option value="' . $legend['legends_id'] . '">' . $legend['legends_name'] . '</option>';
                    }
                    ?>
                </select><br>

                
                <label for="edit_type">Type:</label>
                <input type="text" id="edit_type" name="edit_type"><br>
                

                <label for="edit_color">Color:</label>
                <input type="text" id="edit_color" name="edit_color"><br>
                

                <label for="edit_emei">IMEI:</label>
                <input type="text" id="edit_emei" name="edit_emei"><br>

                <label for="edit_sn">Serial Number:</label>
                <input type="text" id="edit_sn" name="edit_sn"><br>


                <label for="edit_custodian">Custodian:</label>
                <input type="text" id="edit_custodian" name="edit_custodian"><br>

                <label for="edit_rnss_acc">RNSS Account:</label>
                <input type="text" id="edit_rnss_acc" name="edit_rnss_acc"><br>

                <label for="edit_condition">Condition:</label>
                <input type="text" id="edit_condition" name="edit_condition"><br>

                <label for="edit_purpose">Purpose:</label>
                <input type="text" id="edit_purpose" name="edit_purpose"><br>

                <label for="edit_remarks">Remarks:</label><br>
                <input type="text" id="edit_remarks" name="edit_remarks"></input><br>


                <label for="edit_ref_rnss">Ref RNSS:</label>
                <input type="text" id="edit_ref_rnss" name="edit_ref_rnss"><br>
                
                <label for="edit_owner">Owner:</label>
                <input type="text" id="edit_owner" name="edit_owner"><br>

                <label for="edit_status">Status:</label>
                <select name="edit_status" id="edit_status">
                    <option value="Available">Available</option>
                    <option value="Returned">Not Available</option>

                </select><br>

                <input type="submit" name="edit_gadget" value="Save Changes">
            </form>
        </div>
    </div>
</div>


<!-- The Modal for Adding Gadgets -->
<div id="assignModal" class="modal">
    <!-- Modal content -->
    <div class="assign-modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Add a New Gadget</h3>
        <form action="crudgadgetMonitor.php" method="post">
            <label for="gadget_name">Gadget Name:</label>
            <input type="text" id="gadget_name" name="gadget_name" placeholder="Enter gadget name" required>
            <label for="categories_id">Category:</label>
            <select id="categories_id" name="categories_id">
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
            </select>
            <label for="color">Type:</label>
            <input type="text" id="type" name="type" placeholder="Enter type" >
            <label for="color">Color:</label>
            <input type="text" id="color" name="color" placeholder="Enter color" >
            <label for="emei">IMEI:</label>
            <input type="text" id="emei" name="emei" placeholder="Enter EMEI" >
            <label for="sn">SN:</label>
            <input type="text" id="sn" name="sn" placeholder="Enter serial number" >
            <label for="custodian">Custodian:</label>
            <input type="text" id="custodian" name="custodian" placeholder="Enter custodian" >
            <label for="rnss_acc">RNSS Account:</label>
            <input type="text" id="rnss_acc" name="rnss_acc" placeholder="Enter RNSS account" >
            <label for="condition">Condition:</label>
            <input type="text" id="condition" name="condition" placeholder="Enter condition" >
            <label for="purpose">Purpose:</label>
            <input type="text" id="purpose" name="purpose" placeholder="Enter purpose" >
            <label for="remarks">Remarks:</label>
            <input type="text" id="remarks" name="remarks" placeholder="Enter remarks" ></input>
            <label for="status">Status:</label>
<select id="status" name="status" >
    <option value="Available">Available</option>
    <option value="Pending">Pending</option>
    <option value="Approved">Approved</option>
    <option value="Returned">Returned</option>
</select>

            <label for="ref_rnss">Ref RNSS:</label>
            <input type="text" id="ref_rnss" name="ref_rnss" placeholder="Enter ref RNSS">
            
            <label for="owner">Owner:</label>
            <input type="text" id="owner" name="owner" placeholder="Enter owner">
            
            <input type="submit" class="assign-button" value="Add Gadget" name="add_gadget">
        </form>
    </div>
</div>



<!-- JavaScript for modal functionality -->
<script>
   // Get the modals

// When the user clicks the button, open the modal 
function openModal() {
    var modal = document.getElementById('assignModal');
    modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
function closeModal() {
    var modal = document.getElementById('assignModal');
    modal.style.display = "none";
}
  
// Open modal for viewing gadget details
function openViewModal(row) {
    var modal = document.getElementById("viewModal");

    // Fill in data in modal
    document.getElementById("gadgetId").textContent = row.unique_gadget_id;
    document.getElementById("gadgetName").textContent = row.gadget_name;
    document.getElementById("gadgetCategory").textContent = row.categories_name;
    document.getElementById("gadgetType").textContent = row.type;
    document.getElementById("gadgetColor").textContent = row.color;
    document.getElementById("gadgetEMEI").textContent = row.emei;
    document.getElementById("gadgetSN").textContent = row.sn;
    document.getElementById("gadgetCustodian").textContent = row.custodian;
    document.getElementById("gadgetRNSSAcc").textContent = row.rnss_acc;
    document.getElementById("gadgetCondition").textContent = row.condition;
    document.getElementById("gadgetPurpose").textContent = row.purpose;
    document.getElementById("gadgetRemarks").textContent = row.remarks;
    document.getElementById("gadgetLegends").textContent = row.legends_name;
    document.getElementById("gadgetStatus").textContent = row.status;
    document.getElementById("gadgetRefRNSS").textContent = row.ref_rnss;
    document.getElementById("gadgetOwner").textContent = row.owner;


    modal.style.display = "block";
}

 // Close modal for viewing creative tool
 function closeViewModal() {
        var modal = document.getElementById("viewModal");
        modal.style.display = "none";
    }
// Edit modal for editing gadget details
function openEditModal(row) {
    var modal = document.getElementById("editModal");

    // Populate fields with existing data
    document.getElementById("edit_gadget_id").value = row.gadget_id;
    document.getElementById("edit_gadget_name").value = row.gadget_name;
    document.getElementById("edit_categories_id").value = row.categories_id;
    document.getElementById("edit_legends_id").value = row.legends_id;
    document.getElementById("edit_type").value = row.type;

    document.getElementById("edit_color").value = row.color;
    document.getElementById("edit_emei").value = row.emei;
    document.getElementById("edit_sn").value = row.sn;
    document.getElementById("edit_custodian").value = row.custodian;
    document.getElementById("edit_rnss_acc").value = row.rnss_acc;
    document.getElementById("edit_condition").value = row.condition;
    document.getElementById("edit_purpose").value = row.purpose;
    document.getElementById("edit_remarks").value = row.remarks;
    document.getElementById("edit_status").value = row.status;
    document.getElementById("edit_ref_rnss").value = row.ref_rnss;
    document.getElementById("edit_owner").value = row.owner;



    modal.style.display = "block";
}

// Close edit modal for editing gadget
function closeEditModal() {
    var modal = document.getElementById("editModal");
    modal.style.display = "none";
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