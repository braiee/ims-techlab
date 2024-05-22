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

// Check if form is submitted (for deleting categories)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Handle delete action
    // Get category IDs and delete them from the database
    if (isset($_POST['category_ids'])) {
        $category_ids = $_POST['category_ids'];
        $category_ids_str = "'" . implode("','", $category_ids) . "'";
        $sql = "DELETE FROM categories WHERE categories_id IN ($category_ids_str)";
        
        if ($conn->query($sql) === TRUE) {
            $successMessage = "Categories deleted successfully.";
        } else {
            $errorMessage = "Error deleting categories: " . $conn->error;
        }
    } else {
        $errorMessage = "No categories selected to delete.";
    }
}

// SQL query to fetch category data
$sql = "SELECT categories_id, categories_name FROM categories";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/Dashboard.css">
    <link rel="stylesheet" href="css/category.css">
    <title>Manage Categories</title>
</head>

<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="creativeTools.php" class="nav-item active"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="category.php" class="nav-item active"><span class="icon-placeholder"></span>Category</a>
    <a href="ticketing.php" class="nav-item"><span class="icon-placeholder"></span>Borrow</a>
    <a href="logs.php" class="nav-item"><span class="icon-placeholder"></span>Logs</a>
    <li><a href="officeSupplies.php" class="active">Office Supplies</a></li>

    <a href="#user.php" class="nav-item"><span class="icon-placeholder"></span>User</a>
</div>
<!-- Header box container -->
<div class="header-box">
    <div class="header-box-content">
        <!-- Navigation links -->
        <ul class="nav-links">
            <!-- Display greeting message -->
            <?php
            // Check if the user is logged in
            if (isset($_SESSION["user_id"])) {
                // Display greeting message with username
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
            echo '<h2 style="color: #5D9C59;">Manage Categories</h2>';
            echo '<input type="submit" name="delete" value="Delete">';
            echo '</div>';
            echo '<table>';
            
            echo '<thead>';
            echo '<tr>';
            echo '<th></th>'; // Checkbox column
            echo '<th>Category ID</th>';
            echo '<th>Category Name</th>';
            echo '<th>Action</th>'; // New column for actions
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td><input type="checkbox" name="category_ids[]" value="' . $row["categories_id"] . '"></td>'; // Checkbox
                echo '<td>' . $row["categories_id"] . '</td>';
                echo '<td>' . $row["categories_name"] . '</td>';
                // Edit button column
                echo '<td><button class="edit-button" onclick="editCategory(' . $row["categories_id"] . ', \'' . $row["categories_name"] . '\')">Edit</button></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '</form>';
        } else {
            echo "No categories found.";
        }
        // Close connection
        $conn->close();
        ?>
        <!-- Button to open modal -->
        <button class="assign-button" onclick="openModal()">Add Category</button>
    </div>
</div>

<!-- The Modal -->
<div id="assignModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Add a New Category</h3>
        <form action="crudCategory.php" method="post">
            <label for="category_name">Category Name:</label>
            <input type="text" id="category_name" name="category_name" placeholder="Enter category name" required>
            <input type="submit" class="assign-button" value="Add Category" name="add_category">
        </form>
    </div>
</div>

<!-- The Modal for Editing Categories -->
<div id="editModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Edit Category</h3>
        <form action="crudCategory.php" method="post">
            <input type="hidden" id="edit_category_id" name="edit_category_id">
            <label for="edit_category">New Category Name:</label>
            <input type="text" id="edit_category" name="edit_category" placeholder="Enter new category name" required>
            <input type="submit" class="edit-button" value="Save Changes" name="edit_category">
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
    function editCategory(id, name) {
    console.log("Edit button clicked");
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_category').value = name;
    editModal.style.display = "block";
}


    // When the user clicks on <span> (x), close the edit modal
    function closeEditModal() {
        editModal.style.display = "none";
    }
</script>

</body>
</html>
