<?php
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = intval($_GET['id']);  // Ensure id is an integer

    // Use prepared statement to prevent SQL injection
    $status_query = $conn->prepare("SELECT status FROM users WHERE id = ?");
    $status_query->bind_param("i", $id);
    $status_query->execute();
    $status_result = $status_query->get_result();

    if ($status_result->num_rows == 1) {
        $row = $status_result->fetch_assoc();
        $current_status = $row['status'];

        // Toggle the status (activate/deactivate)
        $new_status = ($current_status == 1) ? 0 : 1;

        // Update the status in the database using a prepared statement
        $update_status_query = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $update_status_query->bind_param("ii", $new_status, $id);

        if ($update_status_query->execute()) {
            $success = "User status updated successfully.";
        } else {
            $error = "Error updating user status: " . $conn->error;
        }

        $update_status_query->close();
    } else {
        $error = "User not found.";
    }

    $status_query->close();
}

$conn->close();
header("Location: users_list.php");
exit();
?>
