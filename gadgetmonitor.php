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

// Initialize message variables
$successMessage = "";
$errorMessage = "";

// Fetch categories for dropdown
$categoriesResult = $conn->query("SELECT categories_id, categories_name FROM categories");
$legendsResult = $conn->query("SELECT legends_id, legends_name FROM legends");

// Check if form is submitted (for deleting gadgets)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Handle delete action
    // Get gadget IDs and delete them from the database
    if (isset($_POST['gadget_ids'])) {
        $gadget_ids = $_POST['gadget_ids'];
        $gadget_ids_str = "'" . implode("','", $gadget_ids) . "'";
        $sql = "DELETE FROM gadget_monitor WHERE gadget_id IN ($gadget_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Gadgets deleted successfully.";
        } else {
            $errorMessage = "Error deleting gadgets: " . $conn->error;
        }
    } else {
        $errorMessage = "No gadgets selected to delete.";
    }
}

// SQL query to fetch gadget data
$sql = "SELECT gm.gadget_id, gm.gadget_name, gm.categories_id, c.categories_name, gm.color, gm.emei, gm.sn,  gm.custodian, gm.rnss_acc, gm.`condition`, gm.purpose, gm.remarks, gm.legends_id, l.legends_name, gm.status
        FROM gadget_monitor gm 
        LEFT JOIN categories c ON gm.categories_id = c.categories_id
        LEFT JOIN legends l ON gm.legends_id = l.legends_id";


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


    </style>
</head>

<body>
    <!-- Side Navigation -->
    <div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item active"><span class="icon-placeholder"></span>Device Monitors</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
    <span class="non-clickable-item">Summary</span>
    <a href="product.php" class="nav-item"><span class="icon-placeholder"></span>Product</a>
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


<div class="main-content">
    <div class="container">
        <?php
        if (!empty($successMessage)) {
            echo '<script>showMessageModal("success", "' . $successMessage . '")</script>';
        } elseif (!empty($errorMessage)) {
            echo '<script>showMessageModal("error", "' . $errorMessage . '")</script>';
        }

        if ($result->num_rows > 0) {
            echo '<form action="" method="post">';
            echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
            echo '<h2 style="color: #5D9C59;">Manage Gadget Monitor</h2>';
            echo '<input type="submit" name="delete" value="Delete">';
            echo '</div>';

            echo '<div class="table-container">'; // Add a container div for the table

            echo '<table>';
            
            echo '<thead>';
            echo '<tr>';
            echo '<th></th>'; // Checkbox column
            echo '<th>Item</th>';
            echo '<th>Category</th>';
            echo '<th>Location</th>';
            echo '<th>Rem.</th>';

            echo '<th>Condition</th>';
            echo '<th>Purpose</th>';
            echo '<th>Status</th>';

            echo '<th>Action</th>'; // New column for actions
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td><input type="checkbox" name="gadget_ids[]" value="' . $row["gadget_id"] . '"></td>'; // Checkbox
                echo '<td>'. $row["gadget_name"] . '</td>';
                echo '<td>' . $row["categories_name"] . '</td>';
                echo '<td>' . $row["legends_name"] . '</td>';

                echo '<td>' . $row["condition"] . '</td>';
                echo '<td>' . $row["purpose"] . '</td>';
                echo '<td>' . $row["remarks"] . '</td>';
                echo '<td>' . $row["status"] . '</td>'; // Display status
                // Action buttons
                echo '<td>';
                echo '<button type="button" class="view-button" onclick=\'openViewModal(' . json_encode($row) . ')\'>View</button>';
                echo '<button type="button" class="view-button edit-button" onclick=\'openEditModal(' . json_encode($row) . ')\'>Edit</button>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            echo '</form>';
        } else {
            echo "No gadgets found.";
        }
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
                    <td><strong>ID:</strong></td>
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
                <input type="hidden" id="editGadgetId" name="edit_gadget_id">
                
                <label for="edit_gadget_name">Gadget Name:</label>
                <input type="text" id="edit_gadget_name" name="edit_gadget_name"><br>
                
                <label for="edit_categories_id">Category:</label>
                <select name="edit_categories_id" id="edit_categories_id">
                <option value="">Select Category</option>

                    <?php
                    // Loop through categoriesResult to fetch and display categories options
                    while ($category = $categoriesResult->fetch_assoc()) {
                        echo '<option value="' . $category['categories_id'] . '">' . $category['categories_name'] . '</option>';
                    }
                    ?>
                </select><br>

                <label for="edit_legends_id">Location:</label>
                <select name="edit_legends_id" id="edit_legends_id">
                <option value="">Select Location</option>

                    <?php
                    // Loop through legendsResult to fetch and display legends options
                    while ($legend = $legendsResult->fetch_assoc()) {
                        echo '<option value="' . $legend['legends_id'] . '">' . $legend['legends_name'] . '</option>';
                    }
                    ?>
                </select><br>

                
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

                <label for="edit_status">Status:</label>
                <select name="edit_status" id="edit_status">
                    <option value="Available">Available</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Returned">Returned</option>
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
    document.getElementById("gadgetId").textContent = row.gadget_id;
    document.getElementById("gadgetName").textContent = row.gadget_name;
    document.getElementById("gadgetCategory").textContent = row.categories_name;
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
    document.getElementById("editGadgetId").value = row.gadget_id;
    document.getElementById("edit_gadget_name").value = row.gadget_name;
    document.getElementById("edit_categories_id").value = row.categories_id;
    document.getElementById("edit_legends_id").value = row.legends_id;
    document.getElementById("edit_color").value = row.color;
    document.getElementById("edit_emei").value = row.emei;
    document.getElementById("edit_sn").value = row.sn;
    document.getElementById("edit_custodian").value = row.custodian;
    document.getElementById("edit_rnss_acc").value = row.rnss_acc;
    document.getElementById("edit_condition").value = row.condition;
    document.getElementById("edit_purpose").value = row.purpose;
    document.getElementById("edit_remarks").value = row.remarks;
    document.getElementById("edit_status").value = row.status;

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

</script>

</body>
</html>