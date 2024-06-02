<?php
session_start(); // Start the session at the beginning of the file

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['identity'] != 1) {
    // If not logged in or not an admin, redirect to the login page or show an error message
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db-connect.php';

// Fetch users data
$sqlUsers = "SELECT user_id, username FROM users WHERE identity = 0"; // Regular users
$regularUsersResult = $conn->query($sqlUsers);

$sqlAdmins = "SELECT user_id, username FROM users WHERE identity = 1"; // Admins
$adminsResult = $conn->query($sqlAdmins);

// Initialize message variables
$successMessage = isset($_GET['successMessage']) ? $_GET['successMessage'] : "";
$errorMessage = isset($_GET['errorMessage']) ? $_GET['errorMessage'] : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/category.css">
    <style>

.container {
    width: 40%; /* Adjust as needed */
    margin: 0 auto; /* Center the container horizontally */ 
    height: 50%;
}   





.user-table {
    width: 100%;
    border-collapse: collapse;
    
}

.user-table th, .user-table td {
    padding: 12px;
}

.user-table th {
    background-color: #f2f2f2;
    text-align: left;
}

.user-table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Modals */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #DDF7E3;
    margin: 10% auto;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover, .close:focus {
    color: #333;
    text-decoration: none;
    cursor: pointer;
}

button {
    background-color: #DDF7E3; /* Blue */
    border: none;
    color: #5D9C59;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 4px 2px;
    cursor: pointer;
    border-radius: 5px;
}

/* Style for the "Add User" button */
#addRegularUserBtn {
    background-color: #DDF7E3; /* Blue */
    color: #5D9C59;

}

/* Style for the "Delete User" button */
#deleteUserBtn {
    background-color: #DDF7E3; /* Red */
    color: #5D9C59;

}

    </style>
</head>
<body>
<!-- Side Navigation -->
<div class="side-nav">
    <a href="#" class="logo-link">        <img src="assets/img/techno.png" alt="Logo" class="logo">
</a>
    <a href="dashboard.php" class="nav-item "><span class="icon-placeholder"></span>Dashboard</a>
    <a href="category.php" class="nav-item"><span class="icon-placeholder"></span>Categories</a>
    <a href="legends.php" class="nav-item"><span class="icon-placeholder"></span>Device Location</a>
    <span class="non-clickable-item">Borrow</span>
    <a href="admin-borrow.php" class="nav-item"><span class="icon-placeholder"></span>Requests</a>
        <a href="admin-requestborrow.php" class="nav-item"><span class="icon-placeholder"></span>Approval</a>
        <a href="admin-fetchrequest.php" class="nav-item"><span class="icon-placeholder"></span>Returned</a>
    <span class="non-clickable-item">Office</span>
    <a href="officeSupplies.php" class="nav-item"><span class="icon-placeholder"></span>Supplies</a>
    <a href="creativeTools.php" class="nav-item"><span class="icon-placeholder"></span>Creative Tools</a>
    <a href="gadgetMonitor.php" class="nav-item"><span class="icon-placeholder"></span>Device Monitors</a>
    <span class="non-clickable-item">Vendors</span>
    <a href="vendor_owned.php" class="nav-item"><span class="icon-placeholder"></span>Owned Gadgets</a>
    <span class="non-clickable-item">Settings</span>
    <a href="users.php" class="nav-item active"><span class="icon-placeholder"></span>Users</a>
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
    </div>

<div class="container">
    <!-- Success and Error Messages -->
    <?php if ($successMessage != ""): ?>
        <div class="success-message"><?php echo $successMessage; ?></div>
    <?php endif; ?>
    <?php if ($errorMessage != ""): ?>
        <div class="error-message"><?php echo $errorMessage; ?></div>
    <?php endif; ?><!-- Regular Users Section -->
<div class="user-container">
    <div class="user-management">
        <h1>Regular Users</h1>
        <!-- Add User Button -->
        <div class="add-user-btn">
            <button id="addRegularUserBtn">Add User</button>
        </div>
        <!-- Delete User Button -->
        <button id="deleteUserBtn">Delete </button>

        <!-- Regular Users Table -->
        <table class="user-table">
            <!-- Table Headings -->
            <thead>
                <tr>
                    <th></th> <!-- Checkbox column -->
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <!-- Table Body -->
            <tbody>
                <?php
                // Output data of each regular user
                while ($user = $regularUsersResult->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><input type="checkbox" class="user-checkbox" value="' . $user["user_id"] . '"></td>';
                    echo '<td>' . $user["username"] . '</td>';
                    echo '<td><button class="edit-user-btn" data-user-id="' . $user["user_id"] . '">Edit</button></td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
    <!-- Admins Section -->
    <div class="user-container">
    <div class="user-management">
        <h1>Admins</h1>
        <!-- Admins Table -->
        <table class="user-table">
            <!-- Table Headings -->
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <!-- Table Body -->
            <tbody>
                <?php
                // Output data of each admin
                while ($admin = $adminsResult->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $admin["username"] . '</td>';
                    echo '<td><button class="edit-user-btn" data-user-id="' . $admin["user_id"] . '">Edit</button></td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit User</h2>
        <form action="crudUsers.php" method="post">
            <input type="hidden" id="edit_user_id" name="edit_user_id">
            <label for="edit_username">New Username:</label>
            <input type="text" id="edit_username" name="edit_username" placeholder="Enter new username" required>
            <label for="edit_password">New Password:</label>
            <input type="password" id="edit_password" name="edit_password" placeholder="Enter new password" required>
            <button type="submit" name="edit_user_credentials">Save Changes</button>
        </form>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New User</h2>
        <form action="crudUsers.php" method="post">
            <label for="new_user_username">Username:</label>
            <input type="text" id="new_user_username" name="new_user_username" placeholder="Enter username" required>
            <label for="new_user_password">Password:</label>
            <input type="password" id="new_user_password" name="new_user_password" placeholder="Enter password" required>
            <button type="submit" name="add_user">Add User</button>
        </form>
    </div>
</div>

<!-- Script to handle modal display -->
<script>
    // Display Edit User Modal
    var editUserBtns = document.querySelectorAll('.edit-user-btn');
    editUserBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var userId = this.getAttribute('data-user-id');
            var modal = document.getElementById('editUserModal');
            var userIdInput = modal.querySelector('#edit_user_id');
            userIdInput.value = userId;
            modal.style.display = "block";
        });
    });

    // Display Add User Modal
    var addUserBtn = document.getElementById('addRegularUserBtn');
    addUserBtn.addEventListener('click', function () {
        var modal = document.getElementById('addUserModal');
        modal.style.display = "block";
    });

    // Get the delete button
    var deleteUserBtn = document.getElementById('deleteUserBtn');

    // Attach event listener for delete button
    deleteUserBtn.addEventListener('click', function () {
        var selectedUsers = document.querySelectorAll('.user-checkbox:checked');
        var userIds = Array.from(selectedUsers).map(function (user) {
            return user.value;
        });

        // If no users are selected, return
        if (userIds.length === 0) {
            return;
        }

        // Confirm deletion
        var confirmDelete = confirm("Are you sure you want to delete the selected users?");
        if (confirmDelete) {
            // Redirect to the delete handler script with the selected user IDs
            window.location.href = "crudUSers.php?action=delete&user_ids=" + userIds.join(',');
        }
    });

    // Get the modal
    var modals = document.getElementsByClassName('modal');

    // Get the <span> element that closes the modal
    var spans = document.getElementsByClassName("close");

    // When the user clicks the <span> (x), close the modal
    for (var i = 0; i < spans.length; i++) {
        spans[i].onclick = function () {
            for (var j = 0; j <modals.length; j++) {
                    modals[j].style.display = "none";
                }
            }
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            for (var i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = "none";
                }
            }
        }
    </script>

</body>
</html>

