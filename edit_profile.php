<?php
session_start();
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $currentPassword = $_POST['current_password'];

    // Check if the user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];

        // Fetch the user's current hashed password from the database using prepared statements
        $fetch_password_sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($fetch_password_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $fetch_password_result = $stmt->get_result();

        if ($fetch_password_result !== false && $fetch_password_result->num_rows === 1) {
            $row = $fetch_password_result->fetch_assoc();
            $hashedPasswordFromDB = $row['password'];

            // Verify the current password using password_verify
            if (password_verify($currentPassword, $hashedPasswordFromDB)) {
                // Password verification successful, proceed with the update
                $hashedNewPassword = password_hash($password, PASSWORD_DEFAULT);

                $update_user_sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($update_user_sql);
                $stmt->bind_param("ssi", $username, $hashedNewPassword, $user_id);

                if ($stmt->execute()) {
                    $success = "Profile updated successfully.";
                } else {
                    $error = "Error updating profile: " . $stmt->error;
                }
            } else {
                $error = "Incorrect current password. Please try again.";
            }
        } else {
            $error = "Error fetching current password: " . $conn->error;
        }
    } else {
        $error = "User not logged in. Please log in first.";
    }

    // Regenerate the session ID after a critical operation
    session_regenerate_id(true);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
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

        form {
            width: 300px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 8px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .success {
            color: green;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<?php include('includes/side_navbar.php'); ?>
    <h2>Edit Profile</h2>
    <form method="post" action="">
        <label>New Username:</label>
        <input type="text" name="username"><br>
        <label>New Password:</label>
        <input type="password" name="password"><br>
        <label>Current Password:</label>
        <input type="password" name="current_password" required><br>
        <button type="submit">Update Profile</button>
    </form>
    <?php if(isset($error)) { echo '<div style="color: red;">' . $error . '</div>'; } ?>
    <?php if(isset($success)) { echo '<div style="color: green;">' . $success . '</div>'; } ?>
</body>
</html>
