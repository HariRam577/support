<?php
session_start();
include '../php/db.php'; // Adjust the path to include the db.php file

// Check if the user is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php"); // Adjust the path to redirect to the index.php file
    exit();
}

$sql = "SELECT COUNT(*) as user_count FROM users";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $user_count = $row['user_count'];
} else {
    $user_count = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css"> <!-- Adjust the path to the CSS file -->
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Adjust the path to the CSS file -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Admin Dashboard</title>
    <style>
        .dashboard-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .dashboard-card h5 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .dashboard-card p {
            font-size: 24px;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php include '../php/nav.php'; ?> <!-- Adjust the path to include the nav.php file -->
    
    <h2 class="mt-5">Admin Dashboard</h2>
    <div class="row dashboard">
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>All Traffic</h5>
                <p>87.2k <span class="text-danger">-30%</span></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>Organic Traffic</h5>
                <p>65.7k <span class="text-danger">-30%</span></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>Visibility</h5>
                <p>12.9% <span class="text-danger">-6%</span></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>Indexed Pages</h5>
                <p>482 <span class="text-success">+10%</span></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>Website Score</h5>
                <p>36 <span class="text-danger">-30%</span></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>Impressions</h5>
                <p>1.8M <span class="text-danger">-32%</span></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>Clicks</h5>
                <p>58.3k <span class="text-danger">-34%</span></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>Average Position</h5>
                <p>21.6 <span class="text-danger">+20%</span></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>CTR</h5>
                <p>3.2% <span class="text-danger">-5%</span></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>Ranked Keywords</h5>
                <p>9.8k <span class="text-danger">-13%</span></p>
            </div>
        </div>
    </div>
    <a href="logout.php" class="btn btn-danger mt-3">Logout</a> <!-- Adjust the path to the logout.php file -->
</div>

<script src="../assets/js/jquery.min.js"></script> <!-- Adjust the path to the jQuery file -->
<script src="../assets/js/bootstrap.min.js"></script> <!-- Adjust the path to the Bootstrap JS file -->
</body>
</html>
