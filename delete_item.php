<?php
include('includes/config.php');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Use prepared statements to delete the item
    $delete_item_sql = "DELETE FROM items WHERE id = ?";
    $stmt = $conn->prepare($delete_item_sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success = "Item deleted successfully.";
    } else {
        $error = "Error deleting item: " . $stmt->error;
    }

    $stmt->close();
} else {
    $error = "Invalid item ID.";
}

$conn->close();

header("Location: dashboard.php");
exit();
?>
