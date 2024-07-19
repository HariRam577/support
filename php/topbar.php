<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Admin Dashboard</title>
    <style>
        /* Top bar styles */
        .top-bar {
            background-color: #343a40; /* Dark background color */
            color: #fff; /* Light text color */
            padding: 10px 20px; /* Padding around content */
            display: flex;
            justify-content: space-between; /* Space items evenly */
            align-items: center; /* Center vertically */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Shadow for depth */
        }

        .top-bar-logo {
            height: 40px; /* Adjust logo height */
            margin-right: 20px; /* Space between logo and other elements */
        }

        .top-bar-icons {
            display: flex;
            align-items: center;
        }

        .top-bar-icons .icon {
            color: #fff; /* Icon color */
            margin-right: 15px; /* Space between icons */
            font-size: 20px; /* Icon size */
            cursor: pointer; /* Pointer cursor for interaction */
        }

        .top-bar-icons .icon.logout {
            margin-left: auto; /* Push logout button to the right */
        }
    </style>
</head>
<body>
<div class="container-fluid">
<div class="top-bar">
    <div class="top-bar-icons">
        <div class="icon">
            <i class="fas fa-user"></i>
        </div>
        <div class="icon">
            <i class="fas fa-question-circle"></i>
        </div>
        <div class="icon logout">
            <a href="logout.php" style="color: #fff; text-decoration: none;position:absolute;z-index: 9999;">Logout</a>
        </div>
    </div>
</div>
</div>


<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
