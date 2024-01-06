<?php
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = intval($_GET['id']);  // Ensure id is an integer

    // Use prepared statement to prevent SQL injection
    $activate_item_sql = $conn->prepare("UPDATE items SET status = 1 WHERE id = ?");
    $activate_item_sql->bind_param("i", $id);

    if ($activate_item_sql->execute()) {
        $success = "Item activated successfully.";
    } else {
        $error = "Error activating item: " . $conn->error;
    }

    $activate_item_sql->close();
}

$conn->close();
header("Location: dashboard.php");
exit();
?>
