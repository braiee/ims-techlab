<?php
session_start();

// Include database connection
include 'db-connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_category'])) {
        $name = $_POST['category_name'];
        addCategory($name, $conn);
    } else if (isset($_POST['edit_category'])) {
        $id = $_POST['edit_category_id']; // Change 'category_id' to 'edit_category_id'
        $name = $_POST['edit_category_name']; // Change 'category_name' to 'edit_category_name'
        editCategory($id, $name, $conn);
    } else if (isset($_POST['delete_category'])) { // Change 'delete_categories' to 'delete_category'
        if (!empty($_POST['select_category'])) {
            $ids = $_POST['select_category'];
            deleteCategories($ids, $conn);
        } else {
            $_SESSION['error_message'] = "No categories selected for deletion.";
            header("Location: category.php");
            exit();
        }
    }
}

function addCategory($name, $conn) {
    $sql = "INSERT INTO categories (categories_name) VALUES ('$name')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Category added successfully!";
        header("Location: category.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error adding category: " . $conn->error;
        header("Location: category.php");
        exit();
    }
}

function editCategory($id, $name, $conn) {
    $sql = "UPDATE categories SET categories_name='$name' WHERE categories_id='$id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Category updated successfully!";
        header("Location: category.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating category: " . $conn->error;
        header("Location: category.php");
        exit();
    }
}

function deleteCategories($ids, $conn) {
    $ids = implode(",", array_map('intval', $ids)); // Convert array to comma-separated string
    $sql = "DELETE FROM categories WHERE categories_id IN ($ids)";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Categories deleted successfully!";
        header("Location: category.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error deleting categories: " . $conn->error;
        header("Location: category.php");
        exit();
    }
}
?>
