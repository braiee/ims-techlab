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
$sql = "SELECT gm.gadget_id, gm.gadget_name, gm.categories_id, c.categories_name, gm.color, gm.qty, gm.emei, gm.sn, gm.ref_rnss, gm.owner, gm.custodian, gm.rnss_acc, gm.`condition`, gm.purpose, gm.remarks, gm.legends_id, l.legends_name
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
    <link rel="stylesheet" href="css/category.css">
    <title>Manage Gadget Monitor</title>
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
        <a href="product.php" class="nav-item"><span class="icon-placeholder"></span>Product</a>
        <span class="non-clickable-item">Settings</span>
        <a href="users.php" class="nav-item"><span class="icon-placeholder"></span>Users</a>
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
            echo '<form action="" method="post">';
            echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
            echo '<h2 style="color: #5D9C59;">Manage Gadget Monitor</h2>';
            echo '<input type="submit" name="delete" value="Delete">';
            echo '</div>';
            echo '<table>';
            
            echo '<thead>';
            echo '<tr>';
            echo '<th></th>'; // Checkbox column
            echo '<th>ID</th>';
            echo '<th>Name</th>';
            echo '<th>Category</th>';
            echo '<th>Legends</th>';

            echo '<th>Color</th>';
            echo '<th>Quantity</th>';
            echo '<th>EMEI</th>';
            echo '<th>SN</th>';
            echo '<th>Ref RNSS</th>';
            echo '<th>Owner</th>';
            echo '<th>Custodian</th>';
            echo '<th>RNSS Acc</th>';
            echo '<th>Condition</th>';
            echo '<th>Purpose</th>';
            echo '<th>Remarks</th>';
            echo '<th>Action</th>'; // New column for actions
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td><input type="checkbox" name="gadget_ids[]" value="' . $row["gadget_id"] . '"></td>'; // Checkbox
                echo '<td>' . $row["gadget_id"] . '</td>';
                echo '<td>'. $row["gadget_name"] . '</td>';
                echo '<td>' . $row["categories_name"] . '</td>';
                echo '<td>' . $row["legends_name"] . '</td>';

                echo '<td>' . $row["color"] . '</td>';
                echo '<td>' . $row["qty"] . '</td>';
                echo '<td>' . $row["emei"] . '</td>';
                echo '<td>' . $row["sn"] . '</td>';
                echo '<td>' . $row["ref_rnss"] . '</td>';
                echo '<td>' . $row["owner"] . '</td>';
                echo '<td>' . $row["custodian"] . '</td>';
                echo '<td>' . $row["rnss_acc"] . '</td>';
                echo '<td>' . $row["condition"] . '</td>';
                echo '<td>' . $row["purpose"] . '</td>';
                echo '<td>' . $row["remarks"] . '</td>';
                echo '<td><button class="edit-button" onclick="editGadget(' . $row["gadget_id"] . ', \'' . $row["gadget_name"] . '\', \'' . $row["categories_id"] . '\', \'' . $row["color"] . '\', \'' . $row["qty"] . '\', \'' . $row["emei"] . '\', \'' . $row["sn"] . '\', \'' . $row["ref_rnss"] . '\', \'' . $row["owner"] . '\', \'' . $row["custodian"] . '\', \'' . $row["rnss_acc"] . '\', \'' . $row["condition"] . '\', \'' . $row["purpose"] . '\', \'' . $row["remarks"] . '\')">Edit</button></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
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

<!-- The Modal for Adding Gadgets -->
<div id="assignModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Add a New Gadget</h3>
        <form action="crudgadgetMonitor.php" method="post">
            <label for="gadget_name">Gadget Name:</label>
            <input type="text" id="gadget_name" name="gadget_name" placeholder="Enter gadget name" required>
            <label for="categories_id">Category:</label>
            <select id="categories_id" name="categories_id" required>
                <option value="">Select Category</option>
                <?php
                if ($categoriesResult->num_rows > 0) {
                    while($category = $categoriesResult->fetch_assoc()) {
                        echo '<option value="' . $category["categories_id"] . '">' . $category["categories_name"] . '</option>';
                    }
                }
                ?>
            </select>
            <label for="legends_id">Legends:</label>
            <select id="legends_id" name="legends_id" required>
                <option value="">Select Legends</option>
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
            <input type="text" id="color" name="color" placeholder="Enter color" required>
            <label for="qty">Quantity:</label>
            <input type="text" id="qty" name="qty" placeholder="Enter quantity" required>
            <label for="emei">EMEI:</label>
            <input type="text" id="emei" name="emei" placeholder="Enter EMEI" required>
            <label for="sn">SN:</label>
            <input type="text" id="sn" name="sn" placeholder="Enter serial number" required>
            <label for="ref_rnss">Ref RNSS:</label>
            <input type="text" id="ref_rnss" name="ref_rnss" placeholder="Enter ref RNSS" required>
            <label for="owner">Owner:</label>
            <input type="text" id="owner" name="owner" placeholder="Enter owner" required>
            <label for="custodian">Custodian:</label>
            <input type="text" id="custodian" name="custodian" placeholder="Enter custodian" required>
            <label for="rnss_acc">RNSS Account:</label>
            <input type="text" id="rnss_acc" name="rnss_acc" placeholder="Enter RNSS account" required>
            <label for="condition">Condition:</label>
            <input type="text" id="condition" name="condition" placeholder="Enter condition" required>
            <label for="purpose">Purpose:</label>
            <input type="text" id="purpose" name="purpose" placeholder="Enter purpose" required>
            <label for="remarks">Remarks:</label>
            <textarea id="remarks" name="remarks" placeholder="Enter remarks" required></textarea>
            <input type="submit" class="assign-button" value="Add Gadget" name="add_gadget">
        </form>
    </div>
</div>

<!-- The Modal for Editing Gadgets -->
<div id="editModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h3>Edit Gadget</h3>
        <form action="crudgadgetMonitor.php" method="post">
            <input type="hidden" id="edit_gadget_id" name="edit_gadget_id">
            <label for="edit_gadget_name">Gadget Name:</label>
            <input type="text" id="edit_gadget_name" name="edit_gadget_name" placeholder="Enter gadget name" required>
            <label for="edit_categories_id">Category:</label>
            <select id="edit_categories_id" name="edit_categories_id" required>
                <option value="">Select Category</option>
                <?php
                if ($categoriesResult->num_rows > 0) {
                    while($category = $categoriesResult->fetch_assoc()) {
                        echo '<option value="' . $category["categories_id"] . '">' . $category["categories_name"] . '</option>';
                    }
                }
                ?>
            </select>

            <label for="edit_legends_id">Legends:</label>
            <select id="edit_legends_id" name="edit_legends_id" required>
                <option value="">Select Legends</option>
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

            <label for="edit_color">Color:</label>
            <input type="text" id="edit_color" name="edit_color" placeholder="Enter color" required>
            <label for="edit_qty">Quantity:</label>
            <input type="text" id="edit_qty" name="edit_qty" placeholder="Enter quantity" required>
            <label for="edit_emei">EMEI:</label>
            <input type="text" id="edit_emei" name="edit_emei" placeholder="Enter EMEI" required>
            <label for="edit_sn">SN:</label>
            <input type="text" id="edit_sn" name="edit_sn" placeholder="Enter serial number" required>
            <label for="edit_ref_rnss">Ref RNSS:</label>
            <input type="text" id="edit_ref_rnss" name="edit_ref_rnss" placeholder="Enter ref RNSS" required>
            <label for="edit_owner">Owner:</label>
            <input type="text" id="edit_owner" name="edit_owner" placeholder="Enter owner" required>
            <label for="edit_custodian">Custodian:</label>
            <input type="text" id="edit_custodian" name="edit_custodian" placeholder="Enter custodian" required>
            <label for="edit_rnss_acc">RNSS Account:</label>
            <input type="text" id="edit_rnss_acc" name="edit_rnss_acc" placeholder="Enter RNSS account" required>
            <label for="edit_condition">Condition:</label>
            <input type="text" id="edit_condition" name="edit_condition" placeholder="Enter condition" required>
            <label for="edit_purpose">Purpose:</label>
            <input type="text" id="edit_purpose" name="edit_purpose" placeholder="Enter purpose" required>
            <label for="edit_remarks">Remarks:</label>
            <textarea id="edit_remarks" name="edit_remarks" placeholder="Enter remarks" required></textarea>
            <input type="submit" class="edit-button" value="Save Changes" name="edit_gadget">
        </form>
    </div>
</div>

<!-- JavaScript for modal functionality -->
<script>
    // Get the modals
    var modal = document.getElementById('assignModal');
    var editModal = document.getElementById('editModal');
    var messageModal = document.getElementById('messageModal');

    // When the user clicks the button, open the modal 
    function openModal() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    function closeModal() {
        modal.style.display = "none";
    }

    // Show message modal
    function showMessageModal(type, message) {
        var messageContent = document.getElementById('messageContent');
        messageContent.innerHTML = message;
        messageModal.style.display = "block";
        if (type === 'success') {
            messageContent.style.color = "green";
        } else if (type === 'error') {
            messageContent.style.color = "red";
        }
        // Automatically close message modal after 3 seconds
        setTimeout(function(){
            closeMessageModal();
        }, 3000);
    }

    // When the user clicks on <span> (x), close the message modal
    function closeMessageModal() {
        messageModal.style.display = "none";
    }

    // Show edit modal
    function editGadget(id, name, categories_id, color, qty, emei, sn, ref_rnss, owner, custodian, rnss_acc, condition, purpose, remarks) {
        document.getElementById('edit_gadget_id').value = id;
        document.getElementById('edit_gadget_name').value = name;
        document.getElementById('edit_categories_id').value = categories_id;
        document.getElementById('edit_color').value = color;
        document.getElementById('edit_qty').value = qty;
        document.getElementById('edit_emei').value = emei;
        document.getElementById('edit_sn').value = sn;
        document.getElementById('edit_ref_rnss').value = ref_rnss;
        document.getElementById('edit_owner').value = owner;
        document.getElementById('edit_custodian').value = custodian;
        document.getElementById('edit_rnss_acc').value = rnss_acc;
        document.getElementById('edit_condition').value = condition;
        document.getElementById('edit_purpose').value = purpose;
        document.getElementById('edit_remarks').value = remarks;

        // Set the selected category in the edit dropdown
        var categoryOptions = document.getElementById('edit_categories_id').options;
        for (var i = 0; i < categoryOptions.length; i++) {
            if (categoryOptions[i].value == categories_id) {
                categoryOptions[i].selected = true;
                break;
            }
        }

        editModal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the edit modal
    function closeEditModal() {
        editModal.style.display = "none";
    }
</script>

</body>
</html>


