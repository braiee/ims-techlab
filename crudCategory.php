<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db-connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_category'])) {
        $name = $_POST['category_name'];
        addCategory($name, $conn);
    } else if (isset($_POST['delete_category'])) {
        $id = $_POST['category_id'];
        deleteCategory($id, $conn);
    } else if (isset($_POST['edit_category'])) {
        $id = $_POST['category_id'];
        $name = $_POST['category_name'];
        editCategory($id, $name, $conn);
    }
}

function addCategory($name, $conn) {
    $sql = "INSERT INTO categories (categories_name) VALUES ('$name')";
    if ($conn->query($sql) === TRUE) {
        header("Location: category.php");
        exit();
} else {
echo "Error adding category: " . $conn->error;
}
}

function deleteCategory($id, $conn) {
$sql = "DELETE FROM categories WHERE categories_id = $id";
if ($conn->query($sql) === TRUE) {
header("Location: category.php");
exit();
} else {
echo "Error deleting category: " . $conn->error;
}
}

function editCategory($id, $name, $conn) {
$sql = "UPDATE categories SET categories_name = '$name' WHERE categories_id = $id";
if ($conn->query($sql) === TRUE) {
header("Location: category.php");
exit();
} else {
echo "Error editing category: " . $conn->error;
}
}

$conn->close();
?>
       
