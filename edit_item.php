<?php
include('includes/config.php');

$remove_image_sql = ""; // Initialize the variable with an empty string

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $id = intval($_POST['id']); // Ensure id is an integer
    $name = htmlspecialchars($_POST['name']);
    $quantity = intval($_POST['quantity']);

    // Check if the user wants to remove the existing image
    $remove_image = isset($_POST['remove_image']) ? intval($_POST['remove_image']) : 0;

    // Update the item in the database
    if ($remove_image) {
        // Use prepared statement to remove the image from the database
        $remove_image_sql = $conn->prepare("UPDATE items SET name = ?, quantity = ?, image = NULL WHERE id = ?");
        $remove_image_sql->bind_param("sii", $name, $quantity, $id);
        $remove_image_sql->execute();

        if (!empty($row['image']) && is_file($row['image'])) {
            unlink($row['image']); // Delete the file from the server
        }
    } else {
        // Check if a new image file is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_path = uploadImage($_FILES['image']);
            if ($image_path) {
                // Use prepared statement to update the item with the new image path
                $remove_image_sql = $conn->prepare("UPDATE items SET name = ?, quantity = ?, image = ? WHERE id = ?");
                $remove_image_sql->bind_param("ssii", $name, $quantity, $image_path, $id);

                if (!empty($row['image']) && is_file($row['image'])) {
                    unlink($row['image']); // Delete the old file from the server
                }
            } else {
                $error = "Error uploading image.";
            }
        } else {
            // Update the item without changing the image
            $remove_image_sql = $conn->prepare("UPDATE items SET name = ?, quantity = ? WHERE id = ?");
            $remove_image_sql->bind_param("sii", $name, $quantity, $id);
        }
    }

    if ($remove_image_sql !== "") {
        if ($remove_image_sql->execute()) {
            $success = "Item updated successfully.";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Error updating item: " . $conn->error;
        }
    } else {
        $error = "No valid query string.";
    }
    $remove_image_sql->close();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch item details for editing using a prepared statement
    $fetch_item_sql = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $fetch_item_sql->bind_param("i", $id);
    $fetch_item_sql->execute();

    $result = $fetch_item_sql->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $quantity = $row['quantity'];
    } else {
        $error = "Item not found.";
    }

    $fetch_item_sql->close();
}

$conn->close();

// Function to handle image upload
function uploadImage($file) {
    $target_dir = "uploads/";
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    // Hash the file name to ensure uniqueness
    $hashed_filename = md5(uniqid()) . '.' . $imageFileType;
    
    $target_file = $target_dir . $hashed_filename;
    $uploadOk = 1;

    // Check if the image file is a valid image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }

    // Check file size
    if ($file["size"] > 50000000) {
        return false;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        return false;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        return false;
    } else {
        // If everything is ok, try to upload file
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        } else {
            return false;
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Edit Inventory Item</title>
    <style>
     body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        form {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="file"] {
            margin-top: 5px;
        }

        input[type="checkbox"] {
            vertical-align: middle;
            margin-right: 5px;
        }

        button {
            background-color: #4caf50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12pt;
        }

        a{
            background-color: darkred;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: #ff0000;
            margin-top: 10px;
        }

        .success {
            color: #008000;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            form {
                width: 80%;
            }
        }
    </style>
</head>
<body>
    <h2>Edit Inventory Item</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo $name; ?>" required><br>
        <label>Quantity:</label>
        <input type="number" name="quantity" value="<?php echo $quantity; ?>" required><br>
        <label>Change Image:</label>
        <input type="file" name="image"><br>
        <label>Remove Image: </label>
        <input type="checkbox" name="remove_image" value="1"><br>
        <button type="submit" name="submit">Update Item</button>
        <a href="dashboard.php"  onclick="return confirm('Are you sure you want to discard this edit?')">Discard Edit</a>
    </form>
    <?php if(isset($error)) { echo $error; } ?>
    <?php if(isset($success)) { echo $success; } ?>
</body>
</html>
