<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

// Session fixation protection
session_regenerate_id(true);

// Check if the current user is an admin
if ($is_admin == 1) {
    header("Location: access_denied.php");
    exit();
}

// Fetch only non-admin users using prepared statements
$sql_users = "SELECT * FROM users WHERE is_admin = 1";
$result_users = $conn->query($sql_users);

// Function to update user role using prepared statements
function updateAdminStatus($user_id, $is_admin) {
    global $conn;

    $update_admin_sql = "UPDATE users SET is_admin = ? WHERE id = ?";
    $update_admin_stmt = $conn->prepare($update_admin_sql);
    $update_admin_stmt->bind_param("ii", $is_admin, $user_id);

    if ($update_admin_stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Function to update user status using prepared statements
function updateUserStatus($user_id, $status) {
    global $conn;

    $update_status_sql = "UPDATE users SET status = ? WHERE id = ?";
    $update_status_stmt = $conn->prepare($update_status_sql);
    $update_status_stmt->bind_param("ii", $status, $user_id);

    if ($update_status_stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Handle form submission for updating admin status and user status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $user_id_to_update = $_POST['user_id'];
    $is_admin = $_POST['is_admin'];
    $status = $_POST['status'];

    // Ensure that the current user is not trying to change their own admin status or status
    if ($user_id_to_update == $user_id) {
        $error = "You cannot change your own admin status or account status.";
    } else {
        // Update the user's admin status and account status using prepared statements
        if (updateAdminStatus($user_id_to_update, $is_admin) && updateUserStatus($user_id_to_update, $status)) {
            $success = "User admin status and account status updated successfully.";
            header("Location: users_list.php");
            exit();
        } else {
            $error = "Error updating user admin status or account status.";
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $user_id_to_delete = $_POST['user_id'];

    // Ensure that the current user is not trying to delete themselves
    if ($user_id_to_delete == $user_id) {
        $error = "You cannot delete your own account.";
    } else {
        // Delete the user using prepared statements
        $delete_user_sql = "DELETE FROM users WHERE id = ?";
        $delete_user_stmt = $conn->prepare($delete_user_sql);
        $delete_user_stmt->bind_param("i", $user_id_to_delete);

        if ($delete_user_stmt->execute()) {
            $success = "User deleted successfully.";
            header("Location: users_list.php");
            exit();
        } else {
            $error = "Error deleting user.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users List</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: #fff;
            text-align: center;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }

        label {
            margin: 10px;
        }

        select, button {
            padding: 10px;
            font-size: 14px;
            margin: 10px;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .message, .error {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
            padding: 10px;
        }
    </style>
</head>
<body>
<?php include('includes/side_navbar.php'); ?>
    <h2>Users List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Status</th>
            <th>Admin status</th>
            <th>Actions</th>
            <?php if ($is_admin) { echo '<th>Action</th>'; } ?>
        </tr>
        <?php while($row = $result_users->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo ($row['status'] == 1) ? 'Active' : 'Inactive'; ?></td>
                <td><?php echo ($row['is_admin'] == 0) ? 'Yes' : 'No'; ?></td>
                <?php if ($is_admin == 0) { ?>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            
                            <!-- Form for updating admin and user status -->
                            <label>Change Status:</label>
                            <select name="is_admin">
                                <option value="0" <?php echo ($row['is_admin'] == 0) ? 'selected' : ''; ?> onclick="return confirm('Are you sure you want to make this an admin?')">Admin</option>
                                <option value="1" <?php echo ($row['is_admin'] == 1) ? 'selected' : ''; ?>>User</option>
                            </select>
                            <select name="status">
                                <option value="1" <?php echo ($row['status'] == 1) ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($row['status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <button type="submit" name="submit">Update Status</button>
                            <!-- Add a "Delete" button -->
        <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete User</button>
                        </form>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table>
    <?php if(isset($error)) { echo $error; } ?>
    <?php if(isset($success)) { echo $success; } ?>
</body>
</html>
