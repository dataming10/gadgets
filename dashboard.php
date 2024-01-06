<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

// Redirect users to dashboard if they are not admins
if ($is_admin != 0) {
    header("Location: user_view.php");
    exit();
}

// Fetch only active items and users using prepared statements
$sql_items = "SELECT * FROM items WHERE status = 1";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

// Fetch only active users using prepared statements
$sql_users = "SELECT * FROM users WHERE status = 1";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->execute();
$result_users = $stmt_users->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
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
    <h2>Inventory Items</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Image</th>
            <th>Actions</th>
            <?php if($is_admin) { echo '<th>Action</th>'; } ?>
        </tr>
        <?php while($row = $result_items->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td style="color: <?php echo ($row['quantity'] == 0) ? 'red' : 'inherit'; ?>"><?php echo $row['quantity']; ?></td>
                <td><img src="<?php echo $row['image']; ?>" alt="Image" style="width: 150px; height: 150px;"></td>
                <?php if($is_admin == 0) { ?>
                    <td>
                        <a href="edit_item.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="delete_item.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a> |
                        <?php
                    $status_text = ($row['status'] == 1) ? 'Deactivate' : 'Activate';
                    $status_link = 'activate_deactivate_item.php?id=' . $row['id'];
                ?>
                <a href="<?php echo $status_link; ?>" onclick="return confirm('Are you sure you want to deactivate this item?')"><?php echo $status_text; ?></a>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table>

    <?php if($is_admin) { ?>
        <h2>Users</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Action</th>
            </tr>
            <?php while($row = $result_users->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['username']; ?></td>
                    <td><a href="deactivate_user.php?id=<?php echo $row['id']; ?>">Deactivate</a></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
</body>
</html>
