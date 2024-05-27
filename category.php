<?php
session_start();

// Include database connection
include 'db-connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/Dashboard.css">
    <link rel="stylesheet" href="css/categorY.css">
    <style>
       .table-container {
            margin-left: 220px; /* Adjusted for side navigation bar width */
            padding: 20px;
            overflow-x: auto; /* Allows horizontal scrolling */
            background-color: #C7E8CA;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 600px; /* Ensures the table doesn't get too small */
            border-radius: 5px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            text-transform: uppercase;
            font-weight: bold;
            color: #45a049;

        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
       
        .add-category-btn {
            margin-top: 20px;
        }
        .add-category-btn button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .add-category-btn button:hover {
            background-color: #45a049;
        }
        .edit-btn {
            padding: 6px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .edit-btn:hover {
            background-color: #45a049;
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
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
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
    </style>
    <title>Manage Categories</title>
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

<!-- Table container -->
<div class="table-container">
    <h2 style="color: #45a049;">Categories</h2>
    <table>
        <thead>
            <tr>
            <th>SELECT</th>
                <th>Category ID</th>
                <th>Category Name</th>
                <th>Actions</th>

            </tr>
        </thead>
        <tbody>
        <?php
            // Include database connection
            include 'db-connect.php';

            // Fetch data from the database
            $sql = "SELECT categories_id, categories_name FROM categories";
            $result = $conn->query($sql);

            // Check if there are any rows returned
            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    
                    echo "<td><input type='checkbox' name='select_category[]' value='" . $row["categories_id"] . "'></td>";
                    echo "<td>" . $row["categories_id"] . "</td>";
                    echo "<td>" . $row["categories_name"] . "</td>";
                    echo "<td><button class='edit-btn' data-id='" . $row["categories_id"] . "' data-name='" . $row["categories_name"] . "'>Edit</button></td>";

                    // Add more table data columns here if needed
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No categories found</td></tr>";
            }

            // Close the database connection
            $conn->close();
            ?>
        </tbody>
    </table>

   <!-- Button to add category -->
   <div class="add-category-btn">
        <button id="addCategoryBtn">Add Category</button>
    </div>
</div>

<!-- Modal for adding/editing a category -->
<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <h2 id="modalTitle">Add Category</h2>
        <form action="crudcategory.php" method="post">
            <input type="hidden" id="category_id" name="category_id">
            <label for="category_name">Category Name:</label><br>
            <input type="text" id="category_name" name="category_name" required><br><br>
            <input type="submit" id="modalSubmit" name="add_category" value="Add">
        </form>
    </div>
</div>

<script>
    // Get the modal
    var modal = document.getElementById('modal');

    // Get the button that opens the modal
    var btn = document.getElementById('addCategoryBtn');

    // Get the <span> element that closes the modal
    var span = document.getElementById('closeModal');

    // Get all edit buttons
    var editButtons = document.getElementsByClassName('editBtn');

    // When the user clicks the button, open the modal
    btn.onclick = function() {
        document.getElementById('modalTitle').innerText = "Add Category";
        document.getElementById('modalSubmit').value = "Add";
        document.getElementById('modalSubmit').name = "add_category";
        document.getElementById('category_id').value = "";
        document.getElementById('category_name').value = "";
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Add click event to edit buttons
    for (var i = 0; i < editButtons.length; i++) {
        editButtons[i].onclick = function() {
            document.getElementById('modalTitle').innerText = "Edit Category";
            document.getElementById('modalSubmit').value = "Edit";
            document.getElementById('modalSubmit').name = "edit_category";
            document.getElementById('category_id').value = this.getAttribute('data-id');
            document.getElementById('category_name').value = this.getAttribute('data-name');
            modal.style.display = "block";
        }
    }
</script>

</div>

</body>
</html>
