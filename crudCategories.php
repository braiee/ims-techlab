<?php
session_start();

// Include database connection
include 'db-connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_category'])) {
        $name = $_POST['category_name'];
        $category_abv = $_POST['category_abv'];
        addCategory($name, $category_abv, $conn);
    } else if (isset($_POST['edit_category'])) {
        $id = $_POST['edit_category_id'];
        $name = $_POST['edit_category_name'];
        $category_abv = $_POST['edit_category_abv'];
        editCategory($id, $name, $category_abv, $conn);
    } else if (isset($_POST['delete_category'])) {
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

function addCategory($name, $category_abv, $conn) {
    $stmt = $conn->prepare("INSERT INTO categories (categories_name, abv) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $category_abv);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Category added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding category: " . $stmt->error;
    }
    $stmt->close();
    header("Location: category.php");
    exit();
}

function editCategory($id, $name, $category_abv, $conn) {
    $stmt = $conn->prepare("UPDATE categories SET categories_name = ?, abv = ? WHERE categories_id = ?");
    $stmt->bind_param("ssi", $name, $category_abv, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Category updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating category: " . $stmt->error;
    }
    $stmt->close();
    header("Location: category.php");
    exit();
}

function deleteCategories($ids, $conn) {
    $ids = implode(",", array_map('intval', $ids)); // Convert array to comma-separated string
    $sql = "DELETE FROM categories WHERE categories_id IN ($ids)";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Categories deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting categories: " . $conn->error;
    }
    header("Location: category.php");
    exit();
}
?>
