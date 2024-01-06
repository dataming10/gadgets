<?php
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Use prepared statements to update the user status
    $update_status_sql = "UPDATE users SET status = 0 WHERE id = ?";
    $stmt = $conn->prepare($update_status_sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success = "User status updated successfully.";
    } else {
        $error = "Error updating user status: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
header("Location: dashboard.php");
exit();
?>
