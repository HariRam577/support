<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch user details
    $sql = "SELECT * FROM approved_users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile_number = $_POST['mobile_number'];
    
    // Update user details
    $sql = "UPDATE approved_users SET name=?, email=?, mobile_number=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $name, $email, $mobile_number, $id);
    $stmt->execute();
    
    $_SESSION['message'] = "User profile updated.";
    header("Location: approved_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Edit Approved User</title>
</head>
<body>
<div class="container">
    <h2 class="mt-5">Edit Approved User</h2>
    
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    
    <form method="post" action="">
        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
        </div>
        <div class="form-group">
            <label for="mobile_number">Mobile Number</label>
            <input type="text" class="form-control" id="mobile_number" name="mobile_number" value="<?php echo $user['mobile_number']; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
    <a href="approved_users.php" class="btn btn-secondary mt-3">Back to Approved Users</a>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
