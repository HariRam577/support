<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <title>Home</title>
</head>
<body>
<div class="container">
    <h2 class="mt-5">Welcome, <?php echo $user['name']; ?></h2>
    <nav class="nav nav-pills flex-column flex-sm-row mt-3">
        <a class="flex-sm-fill text-sm-center nav-link" href="home.php">Home</a>
        <a class="flex-sm-fill text-sm-center nav-link" href="tickets.php">Tickets</a>
        <?php if ($user['role'] == 'admin'): ?>
            <a class="flex-sm-fill text-sm-center nav-link" href="config.php">Config</a>
            <a class="flex-sm-fill text-sm-center nav-link" href="create_ticket.php">Create New Ticket</a>
        <?php endif; ?>
    </nav>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
