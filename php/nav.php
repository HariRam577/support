<?php
// Check if session is not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';
// include '../config/config.php';

// Check if the user is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: ../php/index.php");
    exit();
}

$sql = "SELECT COUNT(*) as user_count FROM users";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$user_count = $row['user_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Admin Dashboard</title>
    <style>
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 280px; /* Fixed width for sidebar */
            background-color: #343a40; /* Dark background */
            padding-top: 60px; /* Adjust padding to align items */
            z-index: 100; /* Ensure sidebar is above content */
            color: #fff; /* Text color */
            /* overflow-y: auto; Enable scrolling for sidebar content */
        }

        .sidebar .navbar-brand img {
            max-width: 100%;
            height: auto;
        }

        .sidebar .nav-link {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #fff;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .sidebar .nav-link:hover {
            background-color: #495057; /* Darker background on hover */
        }

        /* Main Content Area Styles */
        .content {
            margin-left: 280px; /* Adjust margin to match sidebar width */
            padding: 20px;
        }

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

        /* General Styles */
        body {
            background-color: #f8f9fa; /* Light background */
            font-family: Arial, sans-serif;
            margin: 0; /* Remove default margin */
            padding: 0; /* Remove default padding */
        }

        .badge-info {
            background-color: #17a2b8; /* Info badge color */
        }

        .btn-logout {
            margin-top: auto; /* Push logout button to the bottom of the sidebar */
            color: #fff; /* Text color */
            padding: 10px 15px; /* Padding for the button */
            text-decoration: none; /* Remove underline from link */
        }

        .btn-logout:hover {
            background-color: #495057; /* Darker background on hover */
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .sidebar {
                width: 220px; /* Reduce width for smaller screens */
            }

            .content {
                margin-left: 220px; /* Adjust margin to match reduced sidebar width */
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%; /* Full width sidebar on smaller screens */
                height: auto; /* Allow sidebar to expand with content */
                position: static; /* Static position for scrolling */
                padding-top: 20px; /* Adjust padding for readability */
                margin-bottom: 20px; /* Space below sidebar */
            }

            .content {
                margin-left: 0; /* No margin on smaller screens */
                padding: 10px; /* Smaller padding for content */
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid"> <!-- Main container for the entire page -->
        <div class="row">
            <div class="col-md-3"> <!-- Sidebar column -->
                <div class="sidebar">
                    <a class="navbar-brand" href="admin.php">
                        <img src="uploads/technical-support-center-customer-service-internet-business-technology-concept-technical-support-center-customer-service-internet-119668737.webp" alt="Logo" class="logo">
                    </a>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <?php if ($_SESSION['admin'] == 'admin' || isset($_SESSION['admin'])): ?>
                            <!-- Display these links only for admin or approved users -->
                            <li class="nav-item">
                                <a class="nav-link" href="tickets.php">
                                    <i class="fas fa-ticket-alt"></i> Tickets
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="create_ticket.php">
                                    <i class="fas fa-plus"></i> Create New Ticket
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            </li>
                            <?php if ($_SESSION['admin'] == 'admin'): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="approved_users.php">
                                        <i class="fas fa-users"></i> Clients
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <i class="fas fa-cog"></i> Config
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($_SESSION['admin'] == 'admin'): ?>
                            <!-- Display this link only for admin -->
                            <li class="nav-item">
                                <a class="nav-link" href="registered_users.php">
                                    <i class="fas fa-bell"></i> Registered Users
                                    <span class="badge badge-info"><?php echo $user_count; ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['admin'])): ?>
                            <!-- Logout Button -->
                            <li class="nav-item">
                                <a class="nav-link btn-logout" href="../php/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
</body>
</html>
