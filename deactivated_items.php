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

// Fetch deactivated items using prepared statements
$sql_deactivated_items = "SELECT * FROM items WHERE status = 0";
$stmt_deactivated_items = $conn->prepare($sql_deactivated_items);

if ($stmt_deactivated_items) {
    $stmt_deactivated_items->execute();
    $result_deactivated_items = $stmt_deactivated_items->get_result();
} else {
    // Handle the error appropriately (e.g., log it)
    die("Error in prepared statement: " . $conn->error);
}

$conn->close();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Deactivated Items</title>
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
            margin-bottom: 20px;
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

        img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        a {
            text-decoration: none;
            color: #333;
            margin-right: 10px;
        }

        a:hover {
            color: #4CAF50;
        }

        .action-column {
            display: flex;
        }

        .action-column a:last-child {
            margin-right: 0;
        }

        @media only screen and (max-width: 600px) {
            table {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include('includes/side_navbar.php'); ?>
    <h2>Deactivated Items</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Quantity</th>
            <th>Image</th>
            <th>Actions</th>
            <?php if($is_admin) { echo '<th>Action</th>'; } ?>
        </tr>
        <?php while($row = $result_deactivated_items->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><img src="<?php echo $row['image']; ?>" alt="Image" style="width: 150px; height: 150px;"></td>
                <?php if($is_admin == 0) { ?>
                    <td>
                    <a href="delete_item.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a> |
                        <a href="activate_item.php?id=<?php echo $row['id']; ?>">Activate</a>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
