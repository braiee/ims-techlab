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

// Check if form is submitted (for adding or deleting creative tools)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        // Handle add action
        $creative_name = $_POST['creative_name'];
        $categories_id = $_POST['categories_id'];
        $legends_id = $_POST['legends_id'];
        $emei = $_POST['emei'];
        $sn = $_POST['sn'];
        $custodian = $_POST['custodian'];
        $rnss_acc = $_POST['rnss_acc'];
        $remarks = $_POST['remarks'];
        $descriptions = $_POST['descriptions'];

        // Insert data into database
        $sql = "INSERT INTO creative_tools (creative_name, categories_id, legends_id, emei, sn, custodian, rnss_acc, remarks, descriptions)
                VALUES ('$creative_name', '$categories_id', '$legends_id', '$emei', '$sn', '$custodian', '$rnss_acc', '$remarks', '$descriptions')";

        if ($conn->query($sql) === TRUE) {
            $successMessage = "Creative tool added successfully.";
        } else {
            $errorMessage = "Error adding creative tool: " . $conn->error;
        }
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_creative_tool'])) {
        // Handle delete action
        if (isset($_POST['creative_ids'])) {
            $creative_ids = $_POST['creative_ids'];
            $creative_ids_str = "'" . implode("','", $creative_ids) . "'";
            
            // Get current user's username
            $current_user = $_SESSION['username'];
            
            // Get current timestamp
            $current_timestamp = date("Y-m-d H:i:s");
            
            $sql = "UPDATE creative_tools SET status = 'Deleted', delete_timestamp = '$current_timestamp', deleted_by = '$current_user' WHERE creative_id IN ($creative_ids_str)";
            
            if ($conn->query($sql) === TRUE) {
                $successMessage = "Creative tools marked as deleted successfully.";
            } else {
                $errorMessage = "Error marking creative tools as deleted: " . $conn->error;
            }
        } else {
            $errorMessage = "No creative tools selected to delete.";
        }
    }
    
}



// Check if the form for editing a creative tool is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_creative_tool'])) {
    // Retrieve data from the form
    $edit_creative_id = $_POST['edit_creative_id'];
    $edit_creative_name = $_POST['edit_creative_name'];
    $edit_descriptions = $_POST['edit_descriptions'];
    $edit_emei = $_POST['edit_emei'];
    $edit_sn = $_POST['edit_sn'];
    $edit_custodian = $_POST['edit_custodian'];
    $edit_rnss_acc = $_POST['edit_rnss_acc'];
    $edit_remarks = $_POST['edit_remarks'];
    $edit_categories_id = $_POST['edit_categories_id'];
    $edit_legends_id = $_POST['edit_legends_id'];
    $status = $_POST['edit_status']; // Add status retrieval


    // SQL query to update data in creative_tools table
    $sql = "UPDATE creative_tools SET 
                creative_name='$edit_creative_name', 
                descriptions='$edit_descriptions', 
                emei='$edit_emei', 
                sn='$edit_sn',
                custodian='$edit_custodian', 
                rnss_acc='$edit_rnss_acc', 
                remarks='$edit_remarks', 
                categories_id='$edit_categories_id', 
                legends_id='$edit_legends_id',
                status='$status' 
            WHERE creative_id='$edit_creative_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Creative tool updated successfully.";
    } else {
        $_SESSION['message'] = "Error updating creative tool: " . $conn->error;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


// SQL query to fetch creative tool data
$sql = "SELECT ct.creative_id, ct.creative_name, ct.emei, ct.sn, ct.custodian, ct.rnss_acc, ct.remarks, ct.descriptions, ct.status, ct.categories_id, ct.legends_id, c.categories_name, l.legends_name
        FROM creative_tools ct 
        LEFT JOIN categories c ON ct.categories_id = c.categories_id
        LEFT JOIN legends l ON ct.legends_id = l.legends_id
        WHERE ct.status != 'Deleted'";
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

    <style>
        .table-container {
    max-height: 400px; /* Set a maximum height for the table container */
    overflow-y: auto; /* Enable vertical scrolling */
}
        .container {
            overflow-x: auto;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: left;
            white-space: nowrap; /* Prevent text wrapping */
            overflow: hidden;
        }

        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            padding-top: 60px;
        }

        .modal-content {
            background-color: #C7E8CA;
            margin: 5% auto; /* 5% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 30%; /* Could be more or less, depending on screen size */
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
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

        .assign-button {
            background-color: #DDF7E3;
            color: #5D9C59;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .assign-button:hover {
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

        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-size: 16px;
            text-align: center;
        }

        .success {
            color: #4F8A10;
        }

        .error {
            color: #D8000C;
        }
        .assign-modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0,0,0,0.4); /* Black with opacity */
    padding-top: 60px;
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

    </style>
</head>

<body>
<!-- Side Navigation -->
<div class="side-nav">
<a href="#" class="logo-link">        <img src="assets/img/techno.png" alt="Logo" class="logo">
</a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="category.php" class="nav-item "><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item "><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Borrow</span>
        <a href="admin-borrow.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
        <a href="admin-requestborrow.php" class="nav-item"><span class="icon-placeholder"></span>Requests</a>
        <a href="admin-fetchrequest.php" class="nav-item"><span class="icon-placeholder"></span>Returned</a>

    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item "><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item active"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item"><span class="icon-placeholder"></span>Device Monitors</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
    <span class="non-clickable-item">Settings</span>
    <a href="users.php" class="nav-item "><span class="icon-placeholder"></span>Users</a>
    <a href="deleted_items.php" class="nav-item"><span class="icon-placeholder"></span>Bin</a>

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


<!-- Main Content -->
<div class="main-content">
    <div id="container" class="container">
        <?php
        if ($result->num_rows > 0) {
            
            echo '<form action="" method="post">';
            echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">';
            
            echo '<h2 style="color: #5D9C59;">Manage Creative Tools</h2>';
         
            
            echo '<input type="submit" name="delete_creative_tool" value="Delete">';
            echo '</div>';
               
            if (!empty($successMessage)) {
                echo '<div class="message success">' . $successMessage . '</div>';
            } elseif (!empty($errorMessage)) {
                echo '<div class="message error">' . $errorMessage . '</div>';
            }
            echo '<div class="table-container">'; // Add a container div for the table

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th></th>'; // Checkbox column
            echo '<th>ID</th>';
            echo '<th>Name</th>';
            echo '<th>Category</th>';
            echo '<th>Legend</th>';
            echo '<th>Status</th>';

            echo '<th>Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td><input type="checkbox" name="creative_ids[]" value="' . $row["creative_id"] . '"></td>';
                echo '<td>' . $row["creative_id"] . '</td>';
                echo '<td>' . $row["creative_name"] . '</td>';
                echo '<td>' . $row["categories_name"] . '</td>';
                echo '<td>' . $row["legends_name"] . '</td>';
                echo '<td>' . $row["status"] . '</td>'; // Display status

                echo '<td><button type="button" class="view-button" onclick=\'openViewModal(' . json_encode($row) . ')\'>View</button>';
                echo '<br>';
                echo '<button type="button" class="view-button edit-button" onclick=\'openEditModal(' . json_encode($row) . ')\'>Edit</button></td>';

                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
            echo '</form>';
        } else {
            echo '<p>No creative tools found.</p>';
        }
        ?>

        <br>

        <!-- Assign button -->
        <button type="button" class="assign-button" onclick="openModal()">Assign Creative Tool</button>

        <!-- The Modal for Assigning Creative Tools -->
        <div id="assignModal" class="modal">
            <!-- Modal content -->
            <div class="assign-modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h3>Assign Creative Tool</h3>
                <form action="" method="post">
                    <label for="creative_name" required>Name:</label>
                    <input type="text" name="creative_name" ><br>

                    <label for="emei">IMEI:</label>
                    <input type="text" name="emei" ><br>

                    <label for="sn">Serial Number:</label>
                    <input type="text" name="sn" ><br>


                    <label for="custodian">Custodian:</label>
                    <input type="text" name="custodian" ><br>

                    <label for="rnss_acc">RNSS Account:</label>
                    <input type="text" name="rnss_acc" ><br>

                    <label for="remarks">Remarks:</label>
                    <textarea name="remarks"></textarea><br>

                    <label for="descriptions">Descriptions:</label>
                    <textarea name="descriptions"></textarea><br>

                    <label for="categories_id">Category:</label>
                    <select name="categories_id" >
                        <?php while ($category = $categoriesResult->fetch_assoc()) : ?>
                            <option value="<?php echo $category['categories_id']; ?>"><?php echo $category['categories_name']; ?></option>
                        <?php endwhile; ?>
                    </select><br>

                    <label for="legends_id">Legend:</label>
                    <select name="legends_id">
                        <?php while ($legend = $legendsResult->fetch_assoc()) : ?>
                            <option value="<?php echo $legend['legends_id']; ?>"><?php echo $legend['legends_name']; ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label for="edit_status">Status:</label>
                <select name="edit_status" id="edit_status">
                    <option value="Available">Available</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Returned">Returned</option>
                </select><br>


                    <input type="submit" name="add" value="Add Creative Tool">
                </form>
            </div>
        </div>

        <!-- The Modal for Editing Creative Tools -->
        <div id="editModal" class="modal">
            <!-- Modal content -->
            <div class="assign-modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h3>Edit Creative Tool</h3>
                <form action="" method="post">
            <!-- Populate fields with existing data -->
            <input type="hidden" id="editCreativeId" name="edit_creative_id">
            
            <label for="edit_creative_name">Name:</label>
            <input type="text" id="edit_creative_name" name="edit_creative_name" ><br>
        
            
            <label for="edit_emei">IMEI:</label>
            <input type="text" id="edit_emei" name="edit_emei" ><br>
            
            <label for="edit_sn">Serial Number:</label>
            <input type="text" id="edit_sn" name="edit_sn" ><br>
    
            
            <label for="edit_custodian">Custodian:</label>
            <input type="text" id="edit_custodian" name="edit_custodian" ><br>
            
            <label for="edit_rnss_acc">RNSS Account:</label>
            <input type="text" id="edit_rnss_acc" name="edit_rnss_acc" ><br>
            
            <label for="edit_remarks">Remarks:</label>
            <textarea id="edit_remarks" name="edit_remarks" ></textarea><br>
            
            <label for="edit_descriptions">Descriptions:</label>
            <textarea id="edit_descriptions" name="edit_descriptions" ></textarea><br>
            
                       <!-- Categories dropdown -->
            <label for="edit_categories_id">Category:</label>
            <select name="edit_categories_id">
                <?php
                // Fetch and display categories options
                $categoriesResult = $conn->query("SELECT categories_id, categories_name FROM categories");
                while ($category = $categoriesResult->fetch_assoc()) {
                    echo '<option value="' . $category['categories_id'] . '">' . $category['categories_name'] . '</option>';
                }
                ?>
            </select><br>

            <!-- Legends dropdown -->
            <label for="edit_legends_id">Legend:</label>
            <select name="edit_legends_id">
                <?php
                // Fetch and display legends options
                $legendsResult = $conn->query("SELECT legends_id, legends_name FROM legends");
                while ($legend = $legendsResult->fetch_assoc()) {
                    echo '<option value="' . $legend['legends_id'] . '">' . $legend['legends_name'] . '</option>';
                }
                ?>
            </select>

            <label for="edit_status">Status:</label>
                <select name="edit_status" id="edit_status">
                    <option value="Available">Available</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Returned">Returned</option>
                </select><br>


            <input type="submit" name="edit_creative_tool" value="Save Changes">
        </form>
            </div>
        </div>

        <!-- The Modal for Viewing Creative Tools -->
        <div id="viewModal" class="modal">
            <!-- Modal content -->
            <div class="modal-content">
                <span class="close" onclick="closeViewModal()">&times;</span>
                <h3>View Creative Tool</h3>
                <div id="viewContent" class="view-content">
                    <table>
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td id="creativeId"></td>
                        </tr>
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td id="creativeName"></td>
                        </tr>
                        <tr>
                            <td><strong>Category:</strong></td>
                            <td id="creativeCategory"></td>
                        </tr>
                        <tr>
                            <td><strong>Location:</strong></td>
                            <td id="creativeLegend"></td>
                        </tr>
                        <tr>
                            <td><strong>IMEI:</strong></td>
                            <td id="creativeEMEI"></td>
                        </tr>
                        <tr>
                            <td><strong>Serial Number:</strong></td>
                            <td id="creativeSN"></td>
                        </tr>
                        <tr>
                            <td><strong>Reference RNSS:</strong></td>
                            <td id="creativeRefRNSS"></td>
                        </tr>
        
                        <tr>
                            <td><strong>Custodian:</strong></td>
                            <td id="creativeCustodian"></td>
                        </tr>
                        <tr>
                            <td><strong>RNSS Account:</strong></td>
                            <td id="creativeRNSSAcc"></td>
                        </tr>
                        <tr>
                            <td><strong>Remarks:</strong></td>
                            <td id="creativeRemarks"></td>
                        </tr>
                        <tr>
                            <td><strong>Descriptions:</strong></td>
                            <td id="creativeDescriptions"></td>
                        </tr>
                        <tr>
                    <td><strong>Status:</strong></td>
                    <td id="gadgetStatus"></td>
                </tr>

                        
                        </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Open modal for assigning creative tool
    function openModal() {
        var modal = document.getElementById("assignModal");
        modal.style.display = "block";
    }

    // Close modal for assigning creative tool
    function closeModal() {
        var modal = document.getElementById("assignModal");
        modal.style.display = "none";
    }

    // Open modal for viewing creative tool
    function openViewModal(row) {
        var modal = document.getElementById("viewModal");

        // Fill in data in modal
        document.getElementById("creativeId").textContent = row.creative_id;
        document.getElementById("creativeName").textContent = row.creative_name;
        document.getElementById("creativeCategory").textContent = row.categories_name;
        document.getElementById("creativeLegend").textContent = row.legends_name;
        document.getElementById("creativeEMEI").textContent = row.emei;
        document.getElementById("creativeSN").textContent = row.sn;
        document.getElementById("creativeCustodian").textContent = row.custodian;
        document.getElementById("creativeRNSSAcc").textContent = row.rnss_acc;
        document.getElementById("creativeRemarks").textContent = row.remarks;
        document.getElementById("creativeDescriptions").textContent = row.descriptions;
        document.getElementById("gadgetStatus").textContent = row.status;


        modal.style.display = "block";
    }

    // Close modal for viewing creative tool
    function closeViewModal() {
        var modal = document.getElementById("viewModal");
        modal.style.display = "none";
    }

// Open modal for editing creative tool
function openEditModal(row) {
    var modal = document.getElementById("editModal");

    // Populate fields with existing data
    document.getElementById("editCreativeId").value = row.creative_id;
    document.getElementById("edit_creative_name").value = row.creative_name;
    document.getElementById("edit_emei").value = row.emei;
    document.getElementById("edit_sn").value = row.sn;
    document.getElementById("edit_custodian").value = row.custodian;
    document.getElementById("edit_rnss_acc").value = row.rnss_acc;
    document.getElementById("edit_remarks").value = row.remarks;
    document.getElementById("edit_descriptions").value = row.descriptions;
    document.getElementById("edit_status").value = row.status;


    // Select the category option if it exists
    var categoryOption = document.querySelector("#edit_categories_id option[value='" + row.categories_id + "']");
    if (categoryOption) {
        categoryOption.selected = true;
    }

    // Select the legend option if it exists
    var legendOption = document.querySelector("#edit_legends_id option[value='" + row.legends_id + "']");
    if (legendOption) {
        legendOption.selected = true;
    }

    modal.style.display = "block";
}


    // Close modal for editing creative tool
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
