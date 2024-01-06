<?php
include('includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $quantity = intval($_POST['quantity']);

    // Upload image
    $target_dir = "uploads/";
    $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

    // Hash the file name to ensure uniqueness
    $hashed_filename = md5(uniqid()) . '.' . $imageFileType;
    $target_file = $target_dir . $hashed_filename;
    $uploadOk = 1;

    // Check if image file is a valid image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        $error = "File is not a valid image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["image"]["size"] > 5000000) {
        $error = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow only certain file formats
    $allowed_formats = array("jpg", "jpeg", "png", "gif");
    if (!in_array($imageFileType, $allowed_formats)) {
        $error = "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $error = "Sorry, your file was not uploaded.";
    } else {
        // If everything is ok, try to upload file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Use prepared statement to prevent SQL injection
            $insert_item_sql = $conn->prepare("INSERT INTO items (name, quantity, image) VALUES (?, ?, ?)");
            $insert_item_sql->bind_param("sis", $name, $quantity, $target_file);

            if ($insert_item_sql->execute()) {
                $success = "Item added successfully.";
                header("Location: dashboard.php");
            } else {
                $error = "Error: " . $conn->error;
            }

            $insert_item_sql->close();
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Inventory Item</title>
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
            width: 80%;
            max-width: 500px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="file"] {
            border: none;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
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

        @media only screen and (max-width: 600px) {
            form {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include('includes/side_navbar.php'); ?>
    <h2>Add Inventory Item</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="name" required><br>
        <label>Quantity:</label>
        <input type="number" name="quantity" required><br>
        <label>Image:</label>
        <input type="file" name="image" accept="image/*" required><br>
        <button type="submit">Add Item</button>
    </form>
    <?php if(isset($error)) { echo $error; } ?>
    <?php if(isset($success)) { echo $success; } ?>
</body>
</html>
