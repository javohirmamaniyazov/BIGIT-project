<?php
include('connection.php');

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

function is_valid_email($email, $link)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $email = mysqli_real_escape_string($link, $email);
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($link, $query);

    return (mysqli_num_rows($result) == 0);
}

function is_valid_username($username)
{
    return preg_match('/^[a-zA-Z0-9]{2,32}$/', $username);
}

function is_valid_password($password)
{
    return (strlen($password) >= 6 && strlen($password) <= 64 && preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password) && preg_match('/[0-9]/', $password));
}

$registration_errors = array();

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($link, $_POST['username']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $password = $_POST['password'];

    if (!is_valid_email($email, $link)) {
        $registration_errors[] = "Invalid email or email already exists.";
    }
    if (!is_valid_username($username)) {
        $registration_errors[] = "Invalid username (min 2, max 32 characters, letters and numbers only).";
    }
    if (!is_valid_password($password)) {
        $registration_errors[] = "Invalid password (min 6, max 64 characters, at least 1 lowercase, 1 uppercase, and 1 number).";
    }

    if (empty($registration_errors)) {
        // Check for a successful database connection
        if ($link) {
            $password = password_hash($password, PASSWORD_BCRYPT);
            $current_datetime = date('Y-m-d H:i:s');
            $sql = "INSERT INTO users (username, email, password, created_at, updated_at, rules) VALUES ('$username', '$email', '$password', '$current_datetime', '$current_datetime', 1)";
            if (mysqli_query($link, $sql)) {
                // Get the last inserted user ID
                $user_id = mysqli_insert_id($link);

                // Store the user ID in the session
                $_SESSION['user_id'] = $user_id;

                // Redirect to index.php
                header('Location: index.php');
                exit;
            } else {
                $registration_errors[] = "Database error: " . mysqli_error($link);
            }
        } else {
            $registration_errors[] = "Database connection failed.";
        }
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles/login.css">
</head>

<body>
    <form class="form" autocomplete="off" action="register.php" method="post">
        <div class="control">
            <h1>
                Register
            </h1>
        </div>
        <div class="control block-cube block-input">
            <input name="username" type="text" placeholder="Username" />
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

        <div class='controll' style="margin-bottom: 20px;">
            <label for="agree-checkbox">
                <input type="checkbox" style="width: 15px; height: 15px;" id="agree-checkbox" name="agree">
                I agree to the rules
            </label>
        </div>

        <button class="btn block-cube block-cube-hover" name="submit" type="submit">
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
                Register
            </div>
        </button>
    </form>
    <?php

    if (!empty($registration_errors)) {
        echo '<script>';
        echo 'var registrationErrors = ' . json_encode($registration_errors) . ';';
        echo 'if (registrationErrors.length > 0) { alert("Registration Errors:\\n" + registrationErrors.join("\\n")); }';
        echo '</script>';
    }
    ?>
</body>

</html>