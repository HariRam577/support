<?php
session_start();
include 'config/config.php'; // Include the config file
include 'php/db.php';        // Include the database connection file
include 'php/logger.php';    // Include the logger file

if (isset($_POST['login'])) {
    $admin_user = "admin";
    $admin_pass = "enova@123";
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username == $admin_user && $password == $admin_pass) {
        $_SESSION['admin'] = $admin_user;
        $_SESSION['user_id'] = 1; // Assuming 1 is the admin's user ID in the database
        log_message("Admin logged in: $admin_user"); // Log the admin login event
        header("Location: " . BASE_URL . "php/admin.php");
        exit();
    } else {
        // Check if the user is in the approved_users table
        $sql = "SELECT * FROM approved_users WHERE username=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin'] = $username;
            $_SESSION['user_id'] = $user['id']; // Set the user ID in the session
            log_message("User logged in: $username"); // Log the user login event
            header("Location: " . BASE_URL . "php/admin.php");
            exit();
        } else {
            $error = "Invalid Credentials!";
            log_message("Failed login attempt: $username"); // Log the failed login attempt
        }
    }
}

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile_number = $_POST['mobile_number'];

    $sql = "INSERT INTO users (name, email, mobile_number) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $name, $email, $mobile_number);

    if ($stmt->execute()) {
        $success = "New user registered successfully!";
        log_message("New user registered: $name ($email)"); // Log the new user registration
    } else {
        $error = "Error: " . $stmt->error;
        log_message("User registration failed: $name ($email) - " . $stmt->error); // Log the registration failure
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/font-awesome.css">
    <script src="<?php echo BASE_URL; ?>assets/js/font-awesome.js"></script>
    <title>Login</title>
</head>
<body>
<div class="container">
    <h2 class="mt-5">Welcome to Our Website</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="index.php" method="POST" class="mt-3">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" name="login">Login</button>
    </form>

    <button type="button" id="newUserBtn" class="btn btn-secondary mt-3"><i class="fa-solid fa-user-plus"></i> New User</button>
</div>

<!-- Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Register New User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="index.php" method="POST">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="mobile_number">Mobile Number:</label>
                        <input type="text" class="form-control" id="mobile_number" name="mobile_number" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="register">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
<script>
$(document).ready(function(){
    $('#newUserBtn').click(function(){
        $('#registerModal').modal('show');
    });
    $('.close').click(function(){
        $('#registerModal').modal('hide');
    });
});
</script>
</body>
</html>
