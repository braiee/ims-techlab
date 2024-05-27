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
        $categories_id = $_POST['categories_id'];
        $legends_id = $_POST['legends_id'];

        $sql = "INSERT INTO vendor_owned (item_name, vendor_name, contact_person, purpose, turnover_tsto, return_vendor, categories_id, legends_id)
                VALUES ('$item_name', '$vendor_name', '$contact_person', '$purpose', '$turnover_tsto', '$return_vendor', '$categories_id', '$legends_id')";
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Vendor-owned item added successfully.";
        } else {
            $errorMessage = "Error adding vendor-owned item: " . $conn->error;
        }
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
        $categories_id = $_POST['edit_categories_id'];
        $legends_id = $_POST['edit_legends_id'];

        $sql = "UPDATE vendor_owned SET item_name='$item_name', vendor_name='$vendor_name', contact_person='$contact_person', purpose='$purpose', turnover_tsto='$turnover_tsto', return_vendor='$return_vendor', categories_id='$categories_id', legends_id='$legends_id' WHERE vendor_id='$vendor_id'";
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Vendor-owned item updated successfully.";
        } else {
            $errorMessage = "Error updating vendor-owned item: " . $conn->error;
        }
    }

    // Delete vendor-owned items
    if (isset($_POST['delete_vendor_owned'])) {
        if (isset($_POST['vendor_ids'])) {
            $vendor_ids = $_POST['vendor_ids'];
            $vendor_ids_str = "'" . implode("','", $vendor_ids) . "'";
            $sql = "DELETE FROM vendor_owned WHERE vendor_id IN ($vendor_ids_str)";
            if ($conn->query($sql) === TRUE) {
                $successMessage = "Vendor-owned items deleted successfully.";
            } else {
                $errorMessage = "Error deleting vendor-owned items: " . $conn->error;
            }
        } else {
            $errorMessage = "No vendor-owned items selected to delete.";
        }
    }
}

// SQL query to fetch vendor-owned data with category and legend names
$sql = "SELECT vendor_owned.*, categories.categories_name, legends.legends_name 
        FROM vendor_owned 
        LEFT JOIN categories ON vendor_owned.categories_id = categories.categories_id 
        LEFT JOIN legends ON vendor_owned.legends_id = legends.legends_id";

$result = $conn->query($sql);

// Fetch categories data
$sql_categories = "SELECT * FROM categories";
$result_categories = $conn->query($sql_categories);

// Fetch legends data
$sql_legends = "SELECT * FROM legends";
$result_legends = $conn->query($sql_legends);


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/Dashboard.css">
    <link rel="stylesheet" href="css/ticket_style.css">
    <title>Manage Vendor-Owned Items</title>
    <style>
        .btn-add, .btn-edit, .btn-delete {
            background-color: #5D9C59;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-delete {
            background-color: #D9534F;
        }
        .btn-edit {
            background-color: #28a745; /* Green color */
        }
    </style>
</head>
<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
    <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
    <a href="category.php" class="nav-item "><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Legends</a>
    <a href="vendor_owned.php" class="nav-item active"><span class="icon-placeholder"></span>Vendor-Owned</a>
    <span class="non-clickable-item">Office</span>
    <a href="#" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetmonitor.php" class="nav-item"><span class="icon-placeholder"></span>Gadget Monitor</a>
    <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Office Supplies</a>
    <a href="#" class="nav-item"><span class="icon-placeholder"></span>Gadget Supplies</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="#" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
    <span class="non-clickable-item">Summary</span>
    <a href="#" class="nav-item"><span class="icon-placeholder"></span>Product</a>
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


<!-- Success and Error Modal -->
<div id="messageModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeMessageModal()">&times;</span>
        <div id="messageContent"></div>
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
            echo '<form action="vendor_owned.php" method="post">';
            echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
            echo '<h2 style="color: #5D9C59;">Manage Vendor-Owned Items</h2>';
            echo '<input type="submit" name="delete_vendor_owned" value="Delete" class="btn-delete">';
            echo '</div>';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th></th>'; // Checkbox column
            echo '<th>ID</th>';
            echo '<th>Item Name</th>';
            echo '<th>Category</th>'; // Added Category column
            echo '<th>Legend</th>'; // Added Legend column
            echo '<th>Vendor Name</th>';
            echo '<th>Contact Person</th>';
            echo '<th>Purpose</th>';
            echo '<th>Turnover (TSTO)</th>';
            echo '<th>Date of Return to Vendor</th>';
            echo '<th>Edit</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>'; // Moved opening tag here

            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td><input type="checkbox" name="vendor_ids[]" value="' . $row["vendor_id"] . '"></td>'; // Checkbox
                echo '<td>' . $row["vendor_id"] . '</td>';
                echo '<td>' . $row["item_name"] . '</td>';
                echo '<td>' . $row["categories_name"] . '</td>'; // Display category name
                echo '<td>' . $row["legends_name"] . '</td>'; // Display legend name
                echo '<td>' . $row["vendor_name"] . '</td>';
                echo '<td>' . $row["contact_person"] . '</td>';
                echo '<td>' . (isset($row["purpose"]) ? $row["purpose"] : '') . '</td>'; // Check if "purpose" key is set
                echo '<td>' . (isset($row["turnover_tsto"]) ? $row["turnover_tsto"] : '') . '</td>'; // Check if "turnover_tsto" key is set
                echo '<td>' . (isset($row["return_vendor"]) ? $row["return_vendor"] : '') . '</td>'; // Check if "return_vendor" key is set
                echo '<td><button type="button" class="btn-edit" onclick="openEditModal(' . $row["vendor_id"] . ', \'' . $row["item_name"] . '\', \'' . $row["vendor_name"] . '\', \'' . $row["contact_person"] . '\', \'' . $row["purpose"] . '\', \'' . $row["turnover_tsto"] . '\', \'' . $row["return_vendor"] . '\', \'' . $row["categories_id"] . '\', \'' . $row["legends_id"] . '\')">Edit</button></td>';
                echo '</tr>';
            }

            echo '</tbody>'; // Moved closing tag here
            echo '</table>';
            echo '</form>';
        } else {
            echo "No vendor-owned items found.";
        }
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
            <label for="categories_id">Category:</label><br> <!-- Added Category label -->
            <label for="categories_id">Category:</label><br>
<select id="categories_id" name="categories_id">
    <?php
    // Reset the internal pointer of the result set
    $result_categories->data_seek(0);
    while($category = $result_categories->fetch_assoc()) {
        echo '<option value="' . $category["categories_id"] . '">' . $category["categories_name"] . '</option>';
    }
    ?>
</select><br>
<label for="legends_id">Legend:</label><br>
<select id="legends_id" name="legends_id">
    <?php
    // Reset the internal pointer of the result set
    $result_legends->data_seek(0);
    while($legend = $result_legends->fetch_assoc()) {
        echo '<option value="' . $legend["legends_id"] . '">' . $legend["legends_name"] . '</option>';
    }
    ?>
</select><br>

            <label for="vendor_name">Vendor Name:</label><br>
            <input type="text" id="vendor_name" name="vendor_name" required><br>
            <label for="contact_person">Contact Person:</label><br>
            <input type="text" id="contact_person" name="contact_person"><br>
            <label for="purpose">Purpose:</label><br>
            <input type="text" id="purpose" name="purpose"><br>
            <label for="turnover_tsto">Turnover (TSTO):</label><br>
            <input type="date" id="turnover_tsto" name="turnover_tsto" required><br>
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
            <label for="edit_categories_id">Category:</label><br>
<select id="edit_categories_id" name="edit_categories_id">
    <?php
    // Reset the internal pointer of the result set
    $result_categories->data_seek(0);
    while($category = $result_categories->fetch_assoc()) {
        echo '<option value="' . $category["categories_id"] . '">' . $category["categories_name"] . '</option>';
    }
    ?>
</select><br>
<label for="edit_legends_id">Legend:</label><br>
<select id="edit_legends_id" name="edit_legends_id">
    <?php
    // Reset the internal pointer of the result set
    $result_legends->data_seek(0);
    while($legend = $result_legends->fetch_assoc()) {
        echo '<option value="' . $legend["legends_id"] . '">' . $legend["legends_name"] . '</option>';
    }
    ?>
</select><br>

            <label for="edit_vendor_name">Vendor Name:</label><br>
            <input type="text" id="edit_vendor_name" name="edit_vendor_name" required><br>
            <label for="edit_contact_person">Contact Person:</label><br>
            <input type="text" id="edit_contact_person" name="edit_contact_person"><br>
            <label for="edit_purpose">Purpose:</label><br>
            <input type="text" id="edit_purpose" name="edit_purpose"><br>
            <label for="edit_turnover_tsto">Turnover (TSTO):</label><br>
            <input type="date" id="edit_turnover_tsto" name="edit_turnover_tsto" required><br>
            <label for="edit_return_vendor">Date of Return to Vendor:</label><br>
            <input type="date" id="edit_return_vendor" name="edit_return_vendor"><br>
   
            <input type="submit" name="edit_vendor_owned" value="Save" class="btn-edit">
        </form>
    </div>
</div>

<!-- JavaScript for modal functionality -->
<script>
    // Get the modals
    var addModal = document.getElementById('addModal');
    var editModal = document.getElementById('editModal');
    var messageModal = document.getElementById('messageModal');

    // Show Add Modal
    function openAddModal() {
        addModal.style.display = "block";
    }

    // Show Edit Modal
    function openEditModal(id, item_name, vendor_name, contact_person, purpose, turnover_tsto, return_vendor, categories_id, legends_id) {
        document.getElementById('edit_vendor_id').value = id;
        document.getElementById('edit_item_name').value = item_name;
        document.getElementById('edit_vendor_name').value = vendor_name;
        document.getElementById('edit_contact_person').value = contact_person;
        document.getElementById('edit_purpose').value = purpose;
        document.getElementById('edit_turnover_tsto').value = turnover_tsto;
        document.getElementById('edit_return_vendor').value = return_vendor;
        document.getElementById('edit_categories_id').value = categories_id; // Set Category value
        document.getElementById('edit_legends_id').value = legends_id; // Set Legend value
        editModal.style.display = "block";
    }

    // Close Modals
    function closeAddModal() {
        addModal.style.display = "none";
    }
    function closeEditModal() {
        editModal.style.display = "none";
    }
    function closeMessageModal() {
        messageModal.style.display = "none";
    }

    // Automatically close message modal after 3 seconds
    setTimeout(function(){
        closeMessageModal();
    }, 3000);
</script>

</body>
</html>


