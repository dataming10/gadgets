<?php
session_start();
include('includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

// Fetch only active items with quantity greater than 0
$sql_items = "SELECT * FROM items WHERE status = 1 AND quantity > 0";

$result_items = $conn->query($sql_items);

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

    <?php displayTable($result_items, $is_admin); ?>

<?php
function displayTable($result, $isAdmin)
{
    ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Image</th>
            <?php if ($isAdmin) ?>
        </tr>
        <?php if ($result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><img src="<?php echo $row['image']; ?>" alt="Image" style="width: 150px; height: 150px;"></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="4" style="text-align: center;">No items available.</td>
            </tr>
        <?php } ?>
    </table>
    <?php
}
?>
</body>
</html>
