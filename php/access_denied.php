<?php
// session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== 'admin') {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="css/style.css">
        <title>Access Denied</title>
        <style>
            .access-denied {
                text-align: center;
                padding: 100px 20px;
            }
            .access-denied .icon {
                font-size: 100px;
                color: #dc3545;
            }
            .access-denied h1 {
                font-size: 36px;
                margin-top: 20px;
                color: #343a40;
            }
            .access-denied p {
                font-size: 18px;
                color: #6c757d;
            }
            .access-denied a {
                margin-top: 20px;
                display: inline-block;
            }
        </style>
    </head>
    <body>
        <div class="access-denied">
            <i class="fas fa-exclamation-triangle icon"></i>
            <h1>Access Denied</h1>
            <p>You do not have permission to view this page.</p>
            <a href="index.php" class="btn btn-primary">Go to Login</a>
        </div>
    </body>
    </html>';
    exit();
}
?>
