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
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : "";
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : "";

// Clear message variables from session
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// SQL query to fetch categories data
$sql = "SELECT categories_id, categories_name FROM categories";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/Dashboard.css">
    <link rel="stylesheet" href="css/Ticket_style.css">
    <title>Manage Categories</title>
</head>

<body>
<!-- Side Navigation -->
<div class="side-nav">
<a href="#" class="logo-link"><img src="assets/img/smarttrack.png" alt="Your Logo" class="logo"></a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="ticketing.php" class="nav-item "><span class="icon-placeholder"></span>Borrow</a>
    <a href="category.php" class="nav-item active"><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
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

      

        <form action="crudCategories.php" method="post">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h2 style="color: #5D9C59;">Manage Categories</h2>
                <div>
                    <input type="submit" name="delete_category" value="Delete" class="btn-delete">
                </div>
            </div>
  <!-- Success and Error Messages -->
  <?php if (!empty($successMessage)) : ?>
            <div class="success-message">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)) : ?>
            <div class="error-message">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>
            <?php
            if ($result->num_rows > 0) {
                echo '<table>';
                echo '<thead>';
                echo '<tr>';
                echo '<th></th>'; // Checkbox column
                echo '<th>Category</th>';
                echo '<th>Action</th>'; // Edit button column
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                // Output data of each row
                while($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><input type="checkbox" name="select_category[]" value="' . $row["categories_id"] . '"></td>'; // Checkbox
                    echo '<td>' . $row["categories_name"] . '</td>';
                    echo '<td><button type="button" onclick="showEditCategoryModal(' . $row["categories_id"] . ', \'' . $row["categories_name"] . '\')" class="btn-edit">Edit</button></td>'; // Edit button
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
           
            } else {
                echo "No categories found.";
            }
            $conn->close();
            ?>
        </form>

        <!-- Add button at the bottom left -->
        <button type="button" onclick="showAddCategoryModal()" class="btn-add" style="margin-top: 20px;">Add</button>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddCategoryModal()">&times;</span>
        <form action="crudCategories.php" method="post">
            <h2>Add Category</h2>
            <label for="category_name">Category Name:</label>
            <input type="text" id="category_name" name="category_name" required>
            <input type="submit" name="add_category" value="Add Category">
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditCategoryModal()">&times;</span>
        <form action="crudCategories.php" method="post">
            <h2>Edit Category</h2>
            <input type="hidden" id="edit_category_id" name="edit_category_id">
            <label for="edit_category_name">Category Name:</label>
            <input type="text" id="edit_category_name" name="edit_category_name" required>
            <input type="submit" name="edit_category" value="Edit Category">
        </form>
    </div>
</div>

<!-- JavaScript for modal functionality -->
<script>
    var addCategoryModal = document.getElementById('addCategoryModal');
    var editCategoryModal = document.getElementById('editCategoryModal');


    // Show Add Category Modal
    function showAddCategoryModal() {
        addCategoryModal.style.display = "block";
    }

    // Close Add Category Modal
    function closeAddCategoryModal() {
        addCategoryModal.style.display = "none";
    }

    // Show Edit Category Modal
    function showEditCategoryModal(category_id, category_name) {
        document.getElementById('edit_category_id').value = category_id;
        document.getElementById('edit_category_name').value = category_name;
        editCategoryModal.style.display = "block";
    }

    // Close Edit Category Modal
    function closeEditCategoryModal() {
        editCategoryModal.style.display = "none";
    }

    // Function to hide messages after 2 seconds
    function hideMessages() {
        var messages = document.querySelectorAll('.success-message, .error-message');
        messages.forEach(function(message) {
            setTimeout(function() {
                message.style.display = 'none';
            }, 2000);
        });
    }

    // Call the function when the page loads
    window.onload = function() {
        hideMessages();
    };
</script>

<!-- CSS for buttons (for illustration purposes, you can adjust as needed) -->
<style>
    .btn-add, .btn-delete, .btn-edit {
        padding: 10px 15px;
        margin-right: 10px;
        color: #5D9C59;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-add {
        background-color: #DDF7E3;
    }
    .btn-edit {
        background-color: #DDF7E3; /* Green color */
    }
    .btn-edit:hover{
                background-color: #ddf7e3ac;
            }
            
            .btn-add:hover{
                background-color: #ddf7e3ac;
            }
    .success-message, .error-message {
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
}

.success-message {
    color: #5D9C59; /* Green text color */
}

.error-message {
    color: #D8000C; /* Red text color */
}
</style>

</body>
</html>
