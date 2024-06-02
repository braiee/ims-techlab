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

// Check if form is submitted (for deleting office supplies)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Handle delete action
    // Get office supply IDs and delete them from the database
    if (isset($_POST['office_ids'])) {
        $office_ids = $_POST['office_ids'];
        $office_ids_str = "'" . implode("','", $office_ids) . "'";
        $sql = "DELETE FROM office_supplies WHERE office_id IN ($office_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Office supplies deleted successfully.";
        } else {
            $errorMessage = "Error deleting office supplies: " . $conn->error;
        }
    } else {
        $errorMessage = "No office supplies selected to delete.";
    }
}

// SQL query to fetch office supply data
$sql = "SELECT office_id, office_name, emei, sn, owner, custodian, rnss_acc, remarks, status, categories_id, legends_id 
        FROM office_supplies";
$result = $conn->query($sql);

// Fetch categories from the database
$categoriesResult = $conn->query("SELECT categories_id, categories_name FROM categories");

// Fetch legends from the database
$legendsResult = $conn->query("SELECT legends_id, legends_name FROM legends");

// Fetch status options
$statusOptions = array("Available", "Pending", "Approved", "Returned");


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


</style>

<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="ticketing.php" class="nav-item "><span class="icon-placeholder"></span>Borrow</a>
    <a href="category.php" class="nav-item "><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item "><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item active"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item "><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item"><span class="icon-placeholder"></span>Device Monitors</a>
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
       
        if ($result->num_rows > 0) {
            echo '<form action="" method="post">';
            echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
            echo '<h2 style="color: #5D9C59;">Manage Office Supplies</h2>';
            
            echo '<input type="submit" name="delete" value="Delete">';
            echo '</div>';
            if (isset($_GET['success'])) {
                $successMessage = urldecode($_GET['success']);
                echo '<div class="success-message">' . $successMessage . '</div>';
            } elseif (!empty($errorMessage)) {
                echo '<div class="message-container">';
                echo '<div class="error-message">' . $errorMessage . '</div>';
                echo '</div>';
            }

 echo '<div class="table-container">';
                echo '<table>';
                
                echo '<thead>';
                echo '<tr>';
                echo '<th></th>'; // Checkbox column
                echo '<th>Item</th>';
                echo '<th>IMEI</th>';
                echo '<th>SN</th>';
                echo '<th>Cust.</th>';
                echo '<th>RNSS</th>';
                echo '<th>Rem.</th>';
                echo '<th>Loc.</th>';
                echo '<th>Cat.</th>';
                echo '<th>Status</th>';
                echo '<th>Action</th>'; // New column for actions
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><input type="checkbox" name="office_ids[]" value="' . $row["office_id"] . '"></td>'; // Checkbox
                    echo '<td>' . $row["office_name"] . '</td>';
                    echo '<td>' . $row["emei"] . '</td>';
                    echo '<td>' . $row["sn"] . '</td>';
                    echo '<td>' . $row["custodian"] . '</td>';
                    echo '<td>' . $row["rnss_acc"] . '</td>';
                    echo '<td>' . $row["remarks"] . '</td>';
                    echo '<td>' . getLegendName($row["legends_id"], $conn) . '</td>'; // Display legend name
                    echo '<td>' . getCategoryName($row["categories_id"], $conn) . '</td>'; // Display category name
                    echo '<td>' . $row["status"] . '</td>'; // Display status

                    echo '<td><button class="edit-button" type="button" onclick="editOfficeSupply(' . $row["office_id"] . ', \'' . $row["office_name"] . '\', \'' . $row["emei"] . '\', \'' . $row["sn"] . '\', \'' . $row["owner"] . '\', \'' . $row["custodian"] . '\', \'' . $row["rnss_acc"] . '\', \'' . $row["remarks"] . '\')">Edit</button></td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
                echo '</div>'; // End of table-container
                echo '</form>';
            } else {
            echo "No office supplies found.";
        }
        $conn->close();
        ?>
        <!-- Button to open modal -->
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
            <label for="emei">IMEI  :</label>
            <input type="text" id="emei" name="emei" placeholder="Enter IMEI" >
            <br><label for="sn">Serial:</label>
            <input type="text" id="sn" name="sn" placeholder="Enter serial number" ><br>
            <label for="owner">Owner:</label>
            <input type="text" id="owner" name="owner" placeholder="Enter owner" >
            <label for="custodian">Custodian:</label>
            <input type="text" id="custodian" name="custodian" placeholder="Enter custodian" >
            <label for="rnss_acc">RNSS Account:</label>
            <input type="text" id="rnss_acc" name="rnss_acc" placeholder="Enter RNSS account" >
            <label for="remarks">Remarks:</label><br>
            <select id="remarks" name="remarks" >
                <option disabled selected>Select condition</option>
                <option value="Good">Good</option>
                <option value="Fair">Fair</option>
                <option value="Poor">Poor</option>
            </select>

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
            <label for="legends_id">Legend:</label>
            <select id="legends_id" name="legends_id">
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
            <label for="edit_emei">IMEI  :</label>
            <input type="text" id="edit_emei" name="edit_emei" placeholder="Enter IMEI" >
            <label for="edit_sn">Serial:</label>
            <input type="text" id="edit_sn" name="edit_sn" placeholder="Enter serial number" ><br>
            <label for="edit_owner">Owner:</label>
            <input type="text" id="edit_owner" name="edit_owner" placeholder="Enter owner" >
            <label for="edit_custodian">Custodian:</label>
            <input type="text" id="edit_custodian" name="edit_custodian" placeholder="Enter custodian" >
            <label for="edit_rnss_acc">RNSS Account:</label>
            <input type="text" id="edit_rnss_acc" name="edit_rnss_acc" placeholder="Enter RNSS account" >
            <div> </div>
            <label for="edit_remarks">Remarks:</label><br>
<input type="text" id="edit_remarks" name="edit_remarks" placeholder="Enter remarks"><br>

<input type="hidden" id="edit_office_id" name="edit_office_id">
            <!-- Other input fields for editing office supply details -->
            <label for="edit_categories_id">Category:</label>
            <select id="edit_categories_id" name="edit_categories_id">
                <?php
                // Fetch and populate categories from the database
                $categoriesResult->data_seek(0); // Reset pointer to the beginning
                while ($category = $categoriesResult->fetch_assoc()) {
                    echo '<option value="' . $category['categories_id'] . '">' . $category['categories_name'] . '</option>';
                }
                ?>
            </select>
            <label for="edit_legends_id">Legend:</label>
            <select id="edit_legends_id" name="edit_legends_id">
                <?php
                // Fetch and populate legends from the database
                $legendsResult->data_seek(0); // Reset pointer to the beginning
                while ($legend = $legendsResult->fetch_assoc()) {
                    echo '<option value="' . $legend['legends_id'] . '">' . $legend['legends_name'] . '</option>';
                }
                ?>
            </select>
            <label for="edit_status">Status:</label>
            <select id="edit_status" name="edit_status">
                <?php
                // Populate status options
                foreach ($statusOptions as $status) {
                    echo '<option value="' . $status . '">' . $status . '</option>';
                }
                ?>
            </select>

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
function editOfficeSupply(id, name, emei, sn, owner, custodian, rnss_acc, remarks, categories_id, legends_id, status) {
    document.getElementById('edit_office_id').value = id;
    document.getElementById('edit_office_name').value = name;
    document.getElementById('edit_emei').value = emei;
    document.getElementById('edit_sn').value = sn;
    document.getElementById('edit_owner').value = owner;
    document.getElementById('edit_custodian').value = custodian;
    document.getElementById('edit_rnss_acc').value = rnss_acc;
    document.getElementById('edit_remarks').value = remarks;
    document.getElementById('edit_categories_id').value = categories_id;
    document.getElementById('edit_legends_id').value = legends_id;
    document.getElementById('edit_status').value = status; // Assuming you also have a status field in the form
    editModal.style.display = "block";
}

// When the user clicks on <span> (x), close the edit modal
function closeEditModal() {
    editModal.style.display = "none";
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
</script>

</body>
</html>
