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

// Fetch legends for dropdown
$legendsResult = $conn->query("SELECT legends_id, legends_name FROM legends");

// Check if form is submitted (for deleting creative tools)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Handle delete action
    // Get creative tool IDs and delete them from the database
    if (isset($_POST['creative_ids'])) {
        $creative_ids = $_POST['creative_ids'];
        $creative_ids_str = "'" . implode("','", $creative_ids) . "'";
        $sql = "DELETE FROM creative_tools WHERE creative_id IN ($creative_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Creative tools deleted successfully.";
        } else {
            $errorMessage = "Error deleting creative tools: " . $conn->error;
        }
    } else {
        $errorMessage = "No creative tools selected to delete.";
    }
}

// SQL query to fetch creative tool data
$sql = "SELECT ct.creative_id, ct.creative_name, ct.qty, ct.emei, ct.sn, ct.ref_rnss, ct.owner, ct.custodian, ct.rnss_acc, ct.remarks, ct.descriptions, ct.categories_id, ct.legends_id, c.categories_name, l.legends_name
        FROM creative_tools ct 
        LEFT JOIN categories c ON ct.categories_id = c.categories_id
        LEFT JOIN legends l ON ct.legends_id = l.legends_id";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/category.css">
    <title>Manage Creative Tools</title>
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
            echo '<h2 style="color: #5D9C59;">Manage Creative Tools</h2>';
            echo '<input type="submit" name="delete" value="Delete">';
            echo '</div>';
            echo '<table>';
            
            echo '<thead>';
            echo '<tr>';
            echo '<th></th>'; // Checkbox column
            echo '<th>ID</th>';
            echo '<th>Name</th>';
            echo '<th>Category</th>';
            echo '<th>Legend</th>';
            echo '<th>Quantity</th>';
            echo '<th>EMEI</th>';
            echo '<th>SN</th>';
            echo '<th>Ref RNSS</th>';
            echo '<th>Owner</th>';
            echo '<th>Custodian</th>';
            echo '<th>RNSS Acc</th>';
            echo '<th>Remarks</th>';
            echo '<th>Descriptions</th>';
            echo '<th>Action</th>'; // New column for actions
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

// Output data of each row
while ($row = $result->fetch_assoc()) {
    echo '<tr>';
    echo '<td><input type="checkbox" name="creative_ids[]" value="' . $row["creative_id"] . '"></td>'; // Checkbox
    echo '<td>' . $row["creative_id"] . '</td>';
    echo '<td>' . $row["creative_name"] . '</td>';
    echo '<td>' . $row["categories_name"] . '</td>';
    echo '<td>' . $row["legends_name"] . '</td>';
    echo '<td>' . $row["qty"] . '</td>';
    echo '<td>' . $row["emei"] . '</td>';
    echo '<td>' . $row["sn"] . '</td>';
    echo '<td>' . $row["ref_rnss"] . '</td>';
    echo '<td>' . $row["owner"] . '</td>';
    echo '<td>' . $row["custodian"] . '</td>';
    echo '<td>' . $row["rnss_acc"] . '</td>';
    echo '<td>' . $row["remarks"] . '</td>';
    echo '<td>' . $row["descriptions"] . '</td>';
    echo '<td><button class="edit-button" onclick="editCreativeTool(\'' . $row["creative_id"] . '\', \'' . $row["creative_name"] . '\', \'' . $row["qty"] . '\', \'' . $row["emei"] . '\', \'' . $row["sn"] . '\', \'' . $row["ref_rnss"] . '\', \'' . $row["owner"] . '\', \'' . $row["custodian"] . '\', \'' . $row["rnss_acc"] . '\', \'' . $row["remarks"] . '\', \'' . $row["descriptions"] . '\')">Edit</button></td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
echo '</form>';
} else {
echo "No creative tools found.";
}
$conn->close();
?>

                <!-- Button to open modal -->
                <button class="assign-button" onclick="openModal()">Add Creative Tool</button>
                </div>
                
                </div>
                <!-- The Modal for Adding Creative Tools -->
                <div id="assignModal" class="modal">
                    <!-- Modal content -->
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <h3>Add a New Creative Tool</h3>
                        <form action="crudCreativeTools.php" method="post">
                            <label for="creative_name">Creative Tool Name:</label>
                            <input type="text" id="creative_name" name="creative_name" placeholder="Enter creative tool name" required>
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
                            <label for="legends_id">Legend:</label>
                            <select id="legends_id" name="legends_id" required>
                                <option value="">Select Legend</option>
                                <?php
                                if ($legendsResult->num_rows > 0) {
                                    while($legend = $legendsResult->fetch_assoc()) {
                                        echo '<option value="' . $legend["legends_id"] . '">' . $legend["legends_name"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
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
                            <label for="remarks">Remarks:</label>
                            <textarea id="remarks" name="remarks" placeholder="Enter remarks" required></textarea>
                            <label for="descriptions">Descriptions:</label>
                            <textarea id="descriptions" name="descriptions" placeholder="Enter descriptions" required></textarea>
                            <input type="submit" class="assign-button" value="Add Creative Tool" name="add_creative_tool">
                        </form>
                    </div>
                </div>
                <!-- The Modal for Editing Creative Tools -->
                <div id="editModal" class="modal">
                    <!-- Modal content -->
                    <div class="modal-content">
                        <span class="close" onclick="closeEditModal()">&times;</span>
                        <h3>Edit Creative Tool</h3>
                        <form action="crudCreativeTool.php" method="post">
                            <input type="hidden" id="edit_creative_id" name="edit_creative_id">
                            <label for="edit_creative_name">Creative Tool Name:</label>
                            <input type="text" id="edit_creative_name" name="edit_creative_name" placeholder="Enter creative tool name" required>
                            <label for="edit_categories_id">Category:</label>
                            <select id="edit_categories_id" name="edit_categories_id" required>
                                <option value="">Select Category</option>
                                <?php
                                if ($categoriesResult->num_rows > 0) {
                                    $categoriesResult->data_seek(0); // Reset the pointer to the first row
                                    while($category = $categoriesResult->fetch_assoc()) {
                                        echo '<option value="' . $category["categories_id"] . '">' . $category["categories_name"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <label for="edit_legends_id">Legend:</label>
                            <select id="edit_legends_id" name="edit_legends_id" required>
                                <option value="">Select Legend</option>
                                <?php
                                if ($legendsResult->num_rows > 0) {
                                    $legendsResult->data_seek(0); // Reset the pointer to the first row
                                    while($legend = $legendsResult->fetch_assoc()) {
                                        echo '<option value="' . $legend["legends_id"] . '">' . $legend["legends_name"] . '</option>';
                                    }
                                }
                                ?>
                            </select>
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
            <label for="edit_remarks">Remarks:</label>
            <textarea id="edit_remarks" name="edit_remarks" placeholder="Enter remarks" required></textarea>
            <label for="edit_descriptions">Descriptions:</label>
            <textarea id="edit_descriptions" name="edit_descriptions" placeholder="Enter descriptions" required></textarea>
            <input type="submit" class="assign-button" value="Update Creative Tool" name="update_creative_tool">
        </form>
    </div>
</div>

<!-- JavaScript for Modals and Messages -->
<script>
    function openModal() {
        document.getElementById('assignModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('assignModal').style.display = 'none';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    function editCreativeTool(creative_id, creative_name, qty, emei, sn, ref_rnss, owner, custodian, rnss_acc, remarks, descriptions) {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('edit_creative_id').value = creative_id;
        document.getElementById('edit_creative_name').value = creative_name;
        document.getElementById('edit_qty').value = qty;
        document.getElementById('edit_emei').value = emei;
        document.getElementById('edit_sn').value = sn;
        document.getElementById('edit_ref_rnss').value = ref_rnss;
        document.getElementById('edit_owner').value = owner;
        document.getElementById('edit_custodian').value = custodian;
        document.getElementById('edit_rnss_acc').value = rnss_acc;
        document.getElementById('edit_remarks').value = remarks;
        document.getElementById('edit_descriptions').value = descriptions;
    }

    function showMessageModal(type, message) {
        var modal = document.getElementById('messageModal');
        var modalContent = document.getElementById('messageContent');

        modalContent.innerHTML = message;

        if (type === 'success') {
            modalContent.style.color = '#5D9C59'; // Success color
        } else if (type === 'error') {
            modalContent.style.color = '#FF0000'; // Error color
        }

        modal.style.display = 'block';
    }

    function closeMessageModal() {
        document.getElementById('messageModal').style.display = 'none';
    }
</script>
</body>
</html>

