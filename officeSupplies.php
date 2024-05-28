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
$sql = "SELECT office_supplies.office_id, office_supplies.office_name, office_supplies.qty, office_supplies.emei, office_supplies.sn, office_supplies.ref_rnss, office_supplies.owner, office_supplies.custodian, office_supplies.rnss_acc, office_supplies.remarks, office_supplies.status, categories.categories_name, legends.legends_name 
FROM office_supplies 
INNER JOIN categories ON office_supplies.categories_id = categories.categories_id 
INNER JOIN legends ON office_supplies.legends_id = legends.legends_id";
$result = $conn->query($sql);


// SQL query to fetch categories
$sql_categories = "SELECT * FROM categories";
$result_categories = $conn->query($sql_categories);

// SQL query to fetch legends
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
    <title>Manage Office Supplies</title>
</head>
<body>
    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
        <a href="dashboard.php" class="nav-item"><span class="icon-placeholder"></span>Dashboard</a>
        <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
        <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
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
                echo '<h2 style="color: #5D9C59;">Manage Office Supplies</h2>';
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
                echo '<th>Quantity</th>';
                echo '<th>EMEI</th>';
                echo '<th>SN</th>';
                echo '<th>Ref RNSS</th>';
                echo '<th>Owner</th>';
                echo '<th>Custodian</th>';
                echo '<th>RNSS Acc</th>';
                echo '<th>Remarks</th>';
                echo '<th>Status</th>'; // New column for status
                echo '<th>Action</th>'; // New column for actions
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><input type="checkbox" name="office_ids[]" value="' . $row["office_id"] . '"></td>'; // Checkbox
                    echo '<td>' . $row["office_id"] . '</td>';
                    echo '<td>' . $row["office_name"] . '</td>';
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
                    echo '<td>' . $row["status"] . '</td>'; // Display status
                    echo '<td><button class="edit-button" onclick="editOfficeSupply(' . $row["office_id"] . ', \'' . $row["office_name"] . '\', \'' . $row["qty"] . '\', \'' . $row["emei"] . '\', \'' . $row["sn"] . '\', \'' . $row["ref_rnss"] . '\', \'' . $row["owner"] . '\', \'' . $row["custodian"] . '\', \'' . $row["rnss_acc"] . '\', \'' . $row["remarks"] . '\')">Edit</button></td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
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
                <input type="text" id="office_name" name="office_name" placeholder="Enter office supply name" required>
                <label for="categories">Categories:</label>
                <select id="categories" name="categories">
                    <?php
                    // Populate categories dropdown
                    if ($result_categories->num_rows > 0) {
                        while ($row = $result_categories->fetch_assoc()) {
                            echo '<option value="' . $row["categories_id"] . '">' . $row["categories_name"] . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="legends">Legends:</label>
                <select id="legends" name="legends">
                    <?php
                    // Populate legends dropdown
                    if ($result_legends->num_rows > 0) {
                        while ($row = $result_legends->fetch_assoc()) {
                            echo '<option value="' . $row["legends_id"] . '">' . $row["legends_name"] . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="qty">Quantity:</label>
                <input type="number" id="qty" name="qty" placeholder="Enter quantity" required>
                <label for="emei">EMEI:</label>
                <input type="text" id="emei" name="emei" placeholder="Enter EMEI" required>
                <label for="sn">Serial Number:</label>
                <input type="text" id="sn" name="sn" placeholder="Enter serial number" required>
                <label for="ref_rnss">Reference RNSS:</label>
                <input type="text" id="ref_rnss" name="ref_rnss" placeholder="Enter reference RNSS" required>
                <label for="owner">Owner:</label>
                <input type="text" id="owner" name="owner" placeholder="Enter owner name" required>
                <label for="custodian">Custodian:</label>
                <input type="text" id="custodian" name="custodian" placeholder="Enter custodian name" required>
                <label for="rnss_acc">RNSS Acc:</label>
                <input type="text" id="rnss_acc" name="rnss_acc" placeholder="Enter RNSS Acc" required>
                <label for="remarks">Remarks:</label>
                <input type="text" id="remarks" name="remarks" placeholder="Enter remarks" required>
                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="Available">Available</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Returned">Returned</option>
                </select>
                <input type="submit" name="submit" value="Submit">
            </form>
        </div>
    </div>

    <!-- The Modal for Editing Office Supplies -->
    <div id="editModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Edit Office Supply</h3>
            <form id="editForm" action="editOfficeSupplies.php" method="post">
                <input type="hidden" id="edit_office_id" name="office_id">
                <label for="edit_office_name">Office Supply Name:</label>
                <input type="text" id="edit_office_name" name="office_name" required>
                <label for="edit_categories">Categories:</label>
                <select id="edit_categories" name="categories">
                    <?php
                    // Populate categories dropdown
                    if ($result_categories->num_rows > 0) {
                        while ($row = $result_categories->fetch_assoc()) {
                            echo '<option value="' . $row["categories_id"] . '">' . $row["categories_name"] . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="edit_legends">Legends:</label>
                <select id="edit_legends" name="legends">
                    <?php
                    // Populate legends dropdown
                    if ($result_legends->num_rows > 0) {
                        while ($row = $result_legends->fetch_assoc()) {
                            echo '<option value="' . $row["legends_id"] . '">' . $row["legends_name"] . '</option>';
                        }
                    }
                    ?>
                </select>
                <label for="edit_qty">Quantity:</label>
                <input type="number" id="edit_qty" name="qty" required>
                <label for="edit_emei">EMEI:</label>
                <input type="text" id="edit_emei" name="emei" required>
                <label for="edit_sn">Serial Number:</label>
                <input type="text" id="edit_sn" name="sn" required>
                <label for="edit_ref_rnss">Reference RNSS:</label>
                <input type="text" id="edit_ref_rnss" name="ref_rnss" required>
                <label for="edit_owner">Owner:</label>
                <input type="text" id="edit_owner" name="owner" required>
                <label for="edit_custodian">Custodian:</label>
                <input type="text" id="edit_custodian" name="custodian" required>
                <label for="edit_rnss_acc">RNSS Acc:</label>
                <input type="text" id="edit_rnss_acc" name="rnss_acc" required>
                <label for="edit_remarks">Remarks:</label>
                <input type="text" id="edit_remarks" name="remarks" required>
                <label for="edit_status">Status:</label>
                <select id="edit_status" name="status">
                    <option value="Available">Available</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Returned">Returned</option>
                </select>
                <input type="submit" name="submit" value="Submit">
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById("assignModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("assignModal").style.display = "none";
        }

        function closeEditModal() {
            document.getElementById("editModal").style.display = "none";
        }

        function showMessageModal(type, message) {
            const messageModal = document.getElementById("messageModal");
            const messageContent = document.getElementById("messageContent");

            if (type === "success") {
                messageContent.style.color = "green";
            } else if (type === "error") {
                messageContent.style.color = "red";
            }

            messageContent.innerHTML = message;
            messageModal.style.display = "block";
        }

        function closeMessageModal() {
            document.getElementById("messageModal").style.display = "none";
        }

        function editOfficeSupply(officeId, officeName, qty, emei, sn, refRnss, owner, custodian, rnssAcc, remarks, status) {
            document.getElementById("edit_office_id").value = officeId;
            document.getElementById("edit_office_name").value = officeName;
            document.getElementById("edit_qty").value = qty;
            document.getElementById("edit_emei").value = emei;
            document.getElementById("edit_sn").value = sn;
            document.getElementById("edit_ref_rnss").value = refRnss;
            document.getElementById("edit_owner").value = owner;
            document.getElementById("edit_custodian").value = custodian;
            document.getElementById("edit_rnss_acc").value = rnssAcc;
            document.getElementById("edit_remarks").value = remarks;
            document.getElementById("edit_status").value = status;

            document.getElementById("editModal").style.display = "block";
        }
    </script>
</body>
</html>
