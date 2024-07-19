<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete user from users table
    $sql = "DELETE FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $_SESSION['message'] = "User has been deleted.";
}

header("Location: registered_users.php");
exit();
?>
