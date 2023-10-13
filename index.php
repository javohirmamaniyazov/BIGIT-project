<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include('connection.php');

$user_id = $_SESSION['user_id'];

$query = "SELECT username FROM users WHERE id = $user_id";
$result = mysqli_query($link, $query);

if ($result && mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $username = $row['username'];
}

$errors = array();

if (isset($_POST['submit'])) {
    // Validate the product name
    $product_name = $_POST['product_name'];
    if (empty($product_name) || strlen($product_name) < 5 || strlen($product_name) > 100) {
        $errors['product_name'] = "Product name must be between 5 and 100 characters.";
    }

    // Validate the product description
    $product_description = $_POST['product_description'];
    if (empty($product_description) || strlen($product_description) < 15 || strlen($product_description) > 1024) {
        $errors['product_description'] = "Product description must be between 15 and 1024 characters.";
    }

    // Validate the product cost
    $product_cost = $_POST['product_cost'];
    if (!is_numeric($product_cost)) {
        $errors['product_cost'] = "Product cost must be a valid positive number.";
    }

    // Validate the uploaded image
    $image_errors = validate_image($_FILES['product_image']);
    if (!empty($image_errors)) {
        $errors['product_image'] = $image_errors;
    }

    if (empty($errors)) {
        // Product data is valid, proceed to insert it into the database

        // Sanitize the product name, description, and cost to prevent SQL injection
        $product_name = mysqli_real_escape_string($link, $product_name);
        $product_description = mysqli_real_escape_string($link, $product_description);
        $product_cost = (float) $product_cost; // Ensure the cost is treated as a float

        // Upload the image and get the image path
        $image_path = upload_image('product_image');

        if ($image_path) {
            // Insert the product data into the database
            $user_id = $_SESSION['user_id'];
            $current_datetime = date('Y-m-d H:i:s');
            $insert_query = "INSERT INTO products (user_id, product_name, product_description, product_cost, product_image, created_at, updated_at) 
                             VALUES ('$user_id', '$product_name', '$product_description', $product_cost, '$image_path', '$current_datetime', '$current_datetime')";
            if (mysqli_query($link, $insert_query)) {

                header('Location: index.php');
                exit;
            } else {
                $errors['database_error'] = "Error inserting product data into the database: " . mysqli_error($link);
            }
        }
    }
}

// Function to validate the uploaded image
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

// Function to upload the image and return its path
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

$products_items = "SELECT * FROM products WHERE user_id = $user_id";
$result = mysqli_query($link, $products_items);

$products = array();

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}


mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Page</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="modal.css">
    <link rel="stylesheet" href="index.css">

</head>

<body style="margin: 0;">
    <div class="navbar">
        <div class="user-dropdown">
            <span class=""></span>
            <span class="user-name dropdown-icon" id="dropdown-icon">
                <?php echo $username; ?> &#9660;
            </span>
            <div class="user-dropdown-content" id="user-dropdown-content">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div style="display: flex; justify-content: space-between">
            <h1>Products</h1>
            <button id="createProductBtn" class="btn create-product-button">Create Product</button>
        </div>

        <?php if (!empty($products)) { ?>
            <table class="product-table">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th style="min-width: 100px;">Cost</th>
                    <th>Image</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
                <?php foreach ($products as $product) { ?>
                    <tr>
                        <td>
                            <?php echo $product['product_name']; ?>
                        </td>
                        <td>
                            <?php echo $product['product_description']; ?>
                        </td>
                        <td>$
                            <?php echo $product['product_cost']; ?>
                        </td>
                        <td><img src="<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>">
                        </td>
                        <td>
                            <?php echo $product['created_at']; ?>
                        </td>
                        <td>
                            <?php echo $product['updated_at']; ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <p>No products found.</p>
        <?php } ?>
    </div>



    <!-- Create Product Modal -->
    <div id="createProductModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeCreateProductModal">&times;</span>
            <h2>Create Product</h2>
            <form class="form" autocomplete="off" method="post" enctype="multipart/form-data">
                <div class="control">
                    <input type="text" name="product_name" placeholder="Product Name" style="width: 97%">
                    <?php if (isset($errors['product_name'])): ?>
                        <p class="error">
                            <?php echo $errors['product_name']; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="control">
                    <input type="text" name="product_description" placeholder="Product Description" style="width: 97%">
                    <?php if (isset($errors['product_description'])): ?>
                        <p class="error">
                            <?php echo $errors['product_description']; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="">
                    <input type="number" name="product_cost" placeholder="Product Cost" value="0" style="width: 97%">
                    <?php if (isset($errors['product_cost'])): ?>
                        <p class="error">
                            <?php echo $errors['product_cost']; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="control">
                    <input type="file" name="product_image" accept="image/jpeg, image/jpg, image/png" style="display: flex; justify-content: flex-start; margin: 10px 0; height: 40px; font-size: 16px;">
                    <?php if (isset($errors['product_image'])): ?>
                        <p class="error">
                            <?php echo implode('<br>', $errors['product_image']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="control">
                    <button type="submit" name="submit" class="btn" style="display: flex; justify-content: flex-end;">Create</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dropdownIcon = document.getElementById('dropdown-icon');
        const userDropdown = document.getElementById('user-dropdown-content');
        const createProductBtn = document.getElementById('createProductBtn');
        const createProductModal = document.getElementById('createProductModal');
        const closeCreateProductModal = document.getElementById('closeCreateProductModal');

        dropdownIcon.addEventListener('click', function () {
            userDropdown.classList.toggle('show');
        });

        createProductBtn.addEventListener('click', function () {
            createProductModal.style.display = 'block';
        });

        closeCreateProductModal.addEventListener('click', function () {
            createProductModal.style.display = 'none';
        });

        window.addEventListener('click', function (event) {
            if (!event.target.matches('.dropdown-icon')) {
                if (userDropdown.classList.contains('show')) {
                    userDropdown.classList.remove('show');
                }
            }
            if (event.target === createProductModal) {
                createProductModal.style.display = 'none';
            }
        });
    </script>

</body>

</html>