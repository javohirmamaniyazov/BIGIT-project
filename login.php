<?php
include('connection.php');

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($link, $query);

    if ($result) {
        if ($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $user_id = $row['id'];
    
            $_SESSION['user_id'] = $user_id;
    
            header('Location: index.php');
            exit;
        }else {
            $login_error = "Invalid email address.";
        }
    } else {
        $login_error = "Database error: " . mysqli_error($link);
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Add your head content here -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles/login.css">
</head>

<body>
    <form class="form" method="POST" action="login.php">
        <div class="control">
            <h1>Login</h1>
        </div>
        <div class="control block-cube block-input">
            <input name="email" type="email" placeholder="Email" />
            <div class="bg-top">
                <div class="bg-inner"></div>
            </div>
            <div class="bg-right">
                <div class="bg-inner"></div>
            </div>
            <div class="bg">
                <div class="bg-inner"></div>
            </div>
        </div>
        <div class="control block-cube block-input">
            <input name="password" type="password" placeholder="Password" />
            <div class="bg-top">
                <div class="bg-inner"></div>
            </div>
            <div class="bg-right">
                <div class="bg-inner"></div>
            </div>
            <div class="bg">
                <div class="bg-inner"></div>
            </div>
        </div>
        <button class="btn block-cube block-cube-hover" type="submit" name="login">
            <div class="bg-top">
                <div class="bg-inner"></div>
            </div>
            <div class="bg-right">
                <div class="bg-inner"></div>
            </div>
            <div class="bg">
                <div class="bg-inner"></div>
            </div>
            <div class="text">
                Log In
            </div>
        </button>
    </form>
</body>

</html>