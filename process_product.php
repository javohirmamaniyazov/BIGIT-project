<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include('connection.php');

$user_id = $_SESSION['user_id'];

// Check if the form was submitted
if (isset($_POST['submit'])) {
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];

    // Validate the product name
    if (empty($product_name) || strlen($product_name) < 5 || strlen($product_name) > 100) {
        $_SESSION['product_error'] = "Product name must be between 5 and 100 characters.";
        header('Location: create_product.php');
        exit;
    }

    // Validate the product description
    if (empty($product_description) || strlen($product_description) < 15 || strlen($product_description) > 1024) {
        $_SESSION['product_error'] = "Product description must be between 15 and 1024 characters.";
        header('Location: create_product.php');
        exit;
    }

    // Validate the uploaded image
    $image_errors = validate_image($_FILES['product_image']);
    if (!empty($image_errors)) {
        $_SESSION['product_error'] = implode("<br>", $image_errors);
        header('Location: create_product.php');
        exit;
    }

    // Sanitize the product name and description to prevent SQL injection
    $product_name = mysqli_real_escape_string($link, $product_name);
    $product_description = mysqli_real_escape_string($link, $product_description);

    // Upload the image and get the image path
    $image_path = upload_image('product_image');

    if ($image_path) {
        // Insert the product data into the database
        $insert_query = "INSERT INTO products (user_id, product_name, product_description, image_path) 
                         VALUES ('$user_id', '$product_name', '$product_description', '$image_path')";
        if (mysqli_query($link, $insert_query)) {
            $_SESSION['product_success'] = "Product added successfully.";
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['product_error'] = "Error inserting product data into the database: " . mysqli_error($link);
            header('Location: create_product.php');
            exit;
        }
    }
}

mysqli_close($link);

function validate_image($image)
{
    $errors = array();

    if ($image['error'] === UPLOAD_ERR_OK) {
        // Check image type
        $allowed_types = array('image/jpeg', 'image/jpg', 'image/png');
        if (!in_array($image['type'], $allowed_types)) {
            $errors[] = "Invalid image format. Allowed formats: JPEG, JPG, PNG.";
        }

        // Check image size
        $max_size = 1024 * 1024; // 1MB
        if ($image['size'] > $max_size) {
            $errors[] = "Image size exceeds the maximum allowed size (1MB).";
        }
    } else {
        $errors[] = "Error uploading image. Please try again.";
    }

    return $errors;
}

function upload_image($field_name)
{
    $upload_dir = 'uploads/';
    $file_name = $_FILES[$field_name]['name'];
    $file_tmp = $_FILES[$field_name]['tmp_name'];
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $unique_name = uniqid() . '.' . $file_extension;
    $target_file = $upload_dir . $unique_name;

    if (move_uploaded_file($file_tmp, $target_file)) {
        return $target_file;
    } else {
        return false;
    }
}
?>
