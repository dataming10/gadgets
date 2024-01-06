<?php
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Use prepared statements to update the item status
    $update_status_sql = "UPDATE items SET status = 0 WHERE id = ?";
    $stmt = $conn->prepare($update_status_sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success = "Item status updated successfully.";
    } else {
        $error = "Error updating item status: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
header("Location: dashboard.php");
exit();
?>
