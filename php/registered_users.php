<?php
session_start();
include 'db.php';

// Check if the user is an admin
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$sql = "SELECT * FROM users";
$results = $conn->query($sql);

// Fetch roles
$roles_sql = "SELECT * FROM roles";
$roles_result = $conn->query($roles_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/font-awesome.css">
    <script src="js/font-awesome.js"></script>
    <title>Registered Users</title>
</head>
<body>
<div class="container content">
    <?php
    include 'access_denied.php';
    include 'nav.php';
    ?>

    <h2 class="mt-5">Registered Users</h2>
    <div id="ajaxMessage" class="mt-3"></div>
    <?php if ($message): ?>
        <div id="sessionMessage" >
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile Number</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($rows = $results->fetch_assoc()): ?>
        <tr>
            <td><?php echo $rows['id']; ?></td>
            <td><?php echo $rows['name']; ?></td>
            <td><?php echo $rows['email']; ?></td>
            <td><?php echo $rows['mobile_number']; ?></td>
            <td>
                <button class="btn btn-primary btn-sm approve" data-id="<?php echo $rows['id']; ?>"><i class="fa-solid fa-paper-plane"></i> Approve</button>
                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $rows['id']; ?>)"><i class="fa-solid fa-trash"></i> Remove</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

    <a href="admin.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<!-- Role Selection Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalLabel">Select User Role</h5>
                <button type="button" class="close closed" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="roleForm">
                    <input type="hidden" name="user_id" id="modalUserId">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="roleAdmin" value="Admin" checked>
                        <label class="form-check-label" for="roleAdmin">Admin</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="roleClient" value="Client">
                        <label class="form-check-label" for="roleClient">Client</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="roleDeveloper" value="Developer">
                        <label class="form-check-label" for="roleDeveloper">Developer</label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary closed" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Role</button>
                    </div>
                </form>
              
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Confirm Delete</h5>
                <button type="button" class="close closed" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary cancel" data-dismiss="modal">Cancel</button>
                <a href="#" id="deleteLink" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
    function confirmDelete(id) {
        $('#deleteLink').attr('href', 'delete_user.php?id=' + id);
        $('#confirmDeleteModal').modal('show');
    }

    $(document).ready(function(){
        // Close modal on clicking the close or cancel button
        $('.closed, .cancel').click(function(){
            $('#confirmDeleteModal, #roleModal').modal('hide');
        });

        // Open role modal and set user ID
        $('.approve').click(function(){
            var userId = $(this).data('id');
            $('#modalUserId').val(userId);
            $('#roleModal').modal('show');
        });

        // Handle role form submission
        $('#roleForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: 'approve_user_process.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#ajaxMessage').html(response).addClass("alert alert-success");
                    setTimeout(function() {
                        $('#roleModal').modal('hide');
                        setTimeout(function() {
                            location.reload(); // Reload the page to reflect changes
                        }, 3000); // Display the message for 5 seconds before reloading the page
                    }); // Display the message for 5 seconds before hiding the modal
                },
                error: function(xhr, status, error) {
                    $('#ajaxMessage').html('<div class="alert alert-danger">An error occurred. Please try again.</div>');
                }
            });
        });

        // Display session message and fade out after 5 seconds
        <?php if ($message): ?>
            $('#sessionMessage').fadeIn().delay(5000).fadeOut();
        <?php endif; ?>
    });
</script>
</body>
</html>
