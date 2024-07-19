<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';
// include '../config/config.php';
define('BASE_URL', 'http://localhost/project1/');
// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Handle form submission to edit or delete user details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = $_POST['id'];

        $sql = "DELETE FROM approved_users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "The profile has been deleted successfully";
            header("Location: approved_users.php");
            exit();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } else {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $mobile_number = $_POST['mobile_number'];
        $profile_image = $_POST['existing_profile_image']; // Use existing image if no new image uploaded



        // Handle file upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES['profile_image']['tmp_name'];
            $file_name = basename($_FILES['profile_image']['name']);

            // Get the absolute path to the uploads directory
            $upload_dir = dirname(__FILE__) . '/../uploads/';
            $file_path = $upload_dir . $file_name;

            // Move the uploaded file to the specified directory
            if (move_uploaded_file($file_tmp_name, $file_path)) {
                // Save relative path to the database
                $profile_image = 'uploads/' . $file_name;
            } else {
                echo "Error uploading file.";
                exit();
            }
        }



        // Update user details including profile image path
        $sql = "UPDATE approved_users SET name=?, email=?, mobile_number=?, profile_image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $mobile_number, $profile_image, $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Profile has been updated successfully";
            header("Location: approved_users.php");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

// Handle AJAX request to fetch user details
if (isset($_GET['fetch_user']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM approved_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $results = $stmt->get_result();
    $user = $results->fetch_assoc();
    echo json_encode($user);
    exit();
}


// Fetch all users for display
$sql = "SELECT * FROM approved_users";
$results = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Clients</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .circle-shape {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background-color: #007bff;
            color: #fff;
            margin-right: 15px;
        }

        .circle-shape img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
        }

        .flex-grow-1 {
            flex-grow: 1;
        }

        .flex-grow-1 h5 {
            margin-bottom: 5px;
            font-size: 1.25rem;
            font-weight: bold;
        }

        .flex-grow-1 p {
            margin: 0;
            color: #666;
        }

        .d-flex {
            display: flex !important;
            flex-wrap: wrap;
            justify-content: end;
            width: 75px;
        }

        .btn-group {
            display: flex;
            align-items: center;
        }

        .btn-group .btn {
            margin-left: 5px;
        }

        .btn-group .btn i {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .circle-shape {
                width: 50px;
                height: 50px;
            }

            .flex-grow-1 h5 {
                font-size: 1rem;
            }

            .btn-group {
                flex-direction: column;
                align-items: flex-end;
            }

            .btn-group .btn {
                margin-left: 0;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include "access_denied.php"; ?>
        <?php include "nav.php"; ?>

        <h2 class="mt-5">Our Clients</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div id="sessionMessage" class="alert alert-success">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="row dashboard">
    <?php while ($rows = $results->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="circle-shape">
                        <?php if (!empty($rows['profile_image']) && file_exists(__DIR__ . '/../uploads/' . $rows['profile_image'])): ?>
                            <img src="<?php echo $BASE_URL.'uploads/' . htmlspecialchars($rows['profile_image']); ?>" alt="Profile Image">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <h5><?php echo htmlspecialchars($rows['name']); ?></h5>
                        <p><i class="fas fa-envelope"></i>
                            <?php
                            $email = htmlspecialchars($rows['email']);
                            $maxLength = 17;
                            echo (strlen($email) > $maxLength) ? substr($email, 0, $maxLength) . '...' : $email;
                            ?>
                        </p>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($rows['mobile_number']); ?></p>
                    </div>
                    <div class="d-flex clients">
                        <a href="#" class="btn btn-secondary btn-sm edit" data-toggle="modal"
                            data-id="<?php echo $rows['id']; ?>" onclick="editUser(<?php echo $rows['id']; ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="#" class="btn btn-danger btn-sm delete" data-toggle="modal"
                            data-id="<?php echo $rows['id']; ?>" data-name="<?php echo htmlspecialchars($rows['name']); ?>"
                            onclick="confirmDelete(<?php echo $rows['id']; ?>, '<?php echo htmlspecialchars($rows['name']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

    </div>
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="approved_users.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- <input type="hidden" name="id" id="editUserId">
                        <input type="hidden" name="existing_profile_image" id="existingProfileImage">
                        <div class="form-group">
                            <label for="editProfileImage">Profile Image</label>
                            <input type="file" class="form-control-file" id="editProfileImage" name="profile_image">
                            <img id="existingProfileImagePreview" src="#" alt="Existing Profile Image"
                                style="max-width: 100%; max-height: 200px; display: none;">
                        </div> -->
                        <div class="form-group">
                            <label for="editUserName">Name</label>
                            <input type="text" class="form-control" id="editUserName" name="name">
                        </div>
                        <div class="form-group">
                            <label for="editUserEmail">Email</label>
                            <input type="email" class="form-control" id="editUserEmail" name="email">
                        </div>
                        <div class="form-group">
                            <label for="editUserMobile">Mobile Number</label>
                            <input type="text" class="form-control" id="editUserMobile" name="mobile_number">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary cancel" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cancel" data-dismiss="modal">Cancel</button>
                    <form action="approved_users.php" method="post">
                        <input type="hidden" name="id" id="deleteUserId">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script>
        // Function to handle edit user modal
        function editUser(id) {
            $.ajax({
                url: 'approved_users.php',
                type: 'GET',
                data: { fetch_user: true, id: id },
                success: function (response) {
                    var user = JSON.parse(response);
                    $('#editUserId').val(user.id);
                    $('#editUserName').val(user.name);
                    $('#editUserEmail').val(user.email);
                    $('#editUserMobile').val(user.mobile_number);
                    // $('#existingProfileImage').val(user.profile_image);
                    // Update the profile image preview
                    $baseURL = 'http://localhost/project1';
                    if (user.profile_image) {
                        $('#existingProfileImagePreview').attr('src', $baseURL + '/' + user.profile_image).show();
                    } else {
                        $('#existingProfileImagePreview').hide();
                    }

                    $('#editModal').modal('show');
                }
            });
        }

        // Function to handle delete confirmation modal
        function confirmDelete(id, name) {
            $('#deleteUserId').val(id);
            $('#deleteUserName').text(name);
            $('#deleteModal').modal('show');
        }

        // Close modals on close button click
        $(document).ready(function () {
            $('.close, .cancel').click(function () {
                $('#editModal, #deleteModal').modal('hide');
            });

            // Display session message and fade out after 5 seconds
            <?php if (isset($_SESSION['message'])): ?>
                $('#sessionMessage').fadeIn().delay(5000).fadeOut();
            <?php endif; ?>
        });
    </script>
</body>

</html>