<?php
session_start();
include '../php/db.php'; // Assuming db.php includes your database connection

// Check if the user is logged in
if (!isset($_SESSION['admin']) && !isset($_SESSION['approved_user'])) {
    header("Location: ../index.php");
    exit();
}

// Initialize $user as null
$user = null;

// Fetch user details based on the logged-in user
$username = $_SESSION['admin'] ?? $_SESSION['approved_user'];

$sql = "SELECT * FROM approved_users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists in the database
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc(); // Fetch user data
}

// Handle form submission for editing profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update user details in the database
    $newName = $_POST['name'];
    $newEmail = $_POST['email'];
    $newMobileNumber = $_POST['mobile_number'];
    $newUsername = $username; // Retain the current username

    // Check if the user wants to change the password
    $changePassword = isset($_POST['change_password']) ? true : false;
    
    if ($changePassword) {
        $newPassword = $_POST['new_password']; // Unhashed new password input
        
        // Hash the new password (in a real application, hash securely)
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    } else {
        // Keep the existing password
        $hashedPassword = isset($user['password']) ? $user['password'] : '';
    }

    // Update user details in the database
    $updateSql = "UPDATE approved_users SET name = ?, email = ?, mobile_number = ?, password = ? WHERE username = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('sssss', $newName, $newEmail, $newMobileNumber, $hashedPassword, $username);
    $updateStmt->execute();

    // Set session message for success
    $_SESSION['message'] = "Profile updated successfully!";
    $_SESSION['message_type'] = "success";

    // Redirect to prevent form resubmission on refresh
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .password-toggle {
            position: relative;
        }
        .password-toggle input[type="password"], .password-toggle input[type="text"] {
            padding-right: 35px;
        }
        .password-toggle .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container content">
    <?php include 'nav.php'; // Include navigation file ?>
    
    <h2 class="mt-5 content">User Profile</h2>
    <p class="content pt-0">Welcome, <?php echo isset($user['name']) ? htmlspecialchars($user['name']) : 'User'; ?></p>

    <!-- Display session message if set -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php
        // Clear session message after displaying
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
        <script>
            // Automatically close alert after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        </script>
    <?php endif; ?>

    <!-- Display user profile details if $user is not null -->
    <?php if ($user): ?>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Profile Details</h5>
                <p class="card-text"><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p class="card-text"><strong>Mobile Number:</strong> <?php echo htmlspecialchars($user['mobile_number']); ?></p>
                <p class="card-text"><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <button type="button" class="btn btn-primary edit" data-toggle="modal" data-target="#editProfileModal">
                    Edit Profile
                </button>
            </div>
        </div>
    <?php else: ?>
        <p>User profile not found.</p>
    <?php endif; ?>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" readonly class="form-control" id="name" name="name" value="<?php echo isset($user['name']) ? htmlspecialchars($user['name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="mobile_number">Mobile Number</label>
                            <input type="text" class="form-control" id="mobile_number" name="mobile_number" value="<?php echo isset($user['mobile_number']) ? htmlspecialchars($user['mobile_number']) : ''; ?>" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="changePasswordCheckbox" name="change_password">
                            <label class="form-check-label" for="changePasswordCheckbox">
                                Change Password
                            </label>
                        </div>
                        <div id="passwordFields" style="display: none;">
                            <div class="form-group password-toggle">
                                <label for="newPassword">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="Enter new password">
                                    <div class="input-group-append">
                                        <span class="input-group-text toggle-password"><i class="fas fa-eye"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
 
    <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
    // Toggle password visibility
    $('.toggle-password').click(function(){
        $(this).toggleClass('active');
        var input = $(this).closest('.password-toggle').find('input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
        } else {
            input.attr('type', 'password');
        }
    });

    // Toggle password fields based on checkbox state
    $('#changePasswordCheckbox').change(function() {
        if ($(this).is(':checked')) {
            $('#passwordFields').show();
        } else {
            $('#passwordFields').hide();
        }
    });

    // Show edit profile modal on click
    $('.edit').click(function(){
        $('#editProfileModal').modal('show');
    });

    // Close modal on close button click
    $('.close').click(function(){
        $('#editProfileModal').modal('hide');
    });
</script>
</body>
</html>
