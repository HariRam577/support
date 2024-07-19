<?php
session_start();
include '../php/db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // Load Composer's autoloader

// Check if user is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Check if user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    echo "Error: User ID not found in session.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $priority_id = $_POST['priority'];
    $description = $_POST['description']; // HTML content from CKEditor
    $assigned_user_id = $_POST['assigned_user'];
    $status_id = $_POST['status'];
    $datetime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $submitted_date = $datetime->format('Y-m-d h:i:s A');
    // echo $submitted_date;exit;
    $updated_at = $datetime->format('Y-m-d h:i:s'); // Use the current datetime for updated_at
    // echo $updated_at;exit;
    // Fetch assigned user details
    $user_sql = "SELECT name, email FROM approved_users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $assigned_user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();

    if (!$user) {
        echo "Error: Assigned user not found.";
        exit();
    }

    $assigned_user_name = $user['name'];
    $assigned_user_email = $user['email'];

// Fetch assigned by user details
$assigned_by_sql = "SELECT name FROM approved_users WHERE username = ?";
$assigned_by_stmt = $conn->prepare($assigned_by_sql);

if (!$assigned_by_stmt) {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

$assigned_by_stmt->bind_param("s", $_SESSION['admin']);
$assigned_by_stmt->execute();
$assigned_by_result = $assigned_by_stmt->get_result();

if (!$assigned_by_result) {
    echo "Error executing query: " . $conn->error;
    exit();
}

$assigned_by_row = $assigned_by_result->fetch_assoc();

if (!$assigned_by_row) {
    echo "Error: Assigned by user not found. Username: " . htmlspecialchars($_SESSION['admin']);
    exit();
}

$assigned_by_name = $assigned_by_row['name'];


    // Fetch priority name
    $priority_sql = "SELECT name FROM priorities WHERE id = ?";
    $priority_stmt = $conn->prepare($priority_sql);
    $priority_stmt->bind_param("i", $priority_id);
    $priority_stmt->execute();
    $priority_result = $priority_stmt->get_result();
    $priority = $priority_result->fetch_assoc();

    if (!$priority) {
        echo "Error: Priority not found.";
        exit();
    }

    $priority_name = $priority['name'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert ticket into database
        $insert_sql = "INSERT INTO tickets (subject, priority_id, description, assigned_user_id, status_id, submitted_date, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sississ", $subject, $priority_id, $description, $assigned_user_id, $status_id, $submitted_date, $user_id);

        if ($stmt->execute()) {
            $ticket_id = $stmt->insert_id; // Get the inserted ticket ID

            // Insert ticket update into ticket_updates table
            $update_sql = "INSERT INTO ticket_updates (ticket_id, subject, status_id, assigned_to, description, updated_at, assigned_by)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $update_stmt = $conn->prepare($update_sql);
            // echo "<pre>"; print_r( $assigned_by_name);exit;
            $update_stmt->bind_param("isissss", $ticket_id, $subject, $status_id, $assigned_user_name, $description, $updated_at, $assigned_by_name);

            if ($update_stmt->execute()) {
                // Commit transaction
                $conn->commit();
            
                // Generate the URL for the ticket view page
                $view_ticket_url = "http://192.168.130.208/project1/php/view_ticket.php?id=$ticket_id";
            
                // Send email to assigned user using PHPMailer
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'mail.enovasolutions.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'production@enovasolutions.com';
                $mail->Password = 'Dollar$5';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom('production@enovasolutions.com', 'Admin');
                $mail->addAddress($assigned_user_email);
            
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'New Ticket Assigned: ' . $subject;
                $mail->Body = '
                <div style="border: 1px solid #ccc; padding: 20px; font-family: Arial, sans-serif; max-width: 600px; margin: auto;">
                    <h2 style="color: #007bff;">New Ticket Assigned</h2>
                    <p style="font-size: 16px; color: #333;">Dear ' . htmlspecialchars($assigned_user_name) . ',</p>
                    <p style="font-size: 16px; color: #333;">You have been assigned a new ticket. Please find the details below:</p>
                    <div style="border: 1px solid #eee; padding: 10px; margin-top: 20px;">
                        <p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>
                        <p><strong>Priority:</strong> ' . htmlspecialchars($priority_name) . '</p>
                        <p><strong>Description:</strong><br>' . $description . '</p> <!-- Use sanitized HTML content -->
                        <p><strong>Submitted Date:</strong> ' . htmlspecialchars($submitted_date) . '</p>
                        <p><strong>View Ticket:</strong> <a href="' . htmlspecialchars($view_ticket_url) . '">Click here</a></p>
                    </div>
                    <p style="font-size: 16px; color: #333;">Best regards,</p>
                    <p style="font-size: 16px; color: #007bff;">Admin</p>
                </div>';
            
                $mail->send();
            
                // Set success message in session
                $_SESSION['success_message'] = "Ticket assigned successfully.";
                header("Location: ../php/create_ticket.php");
                exit();
            }
            else {
                throw new Exception("Error inserting ticket update: " . $conn->error);
            }
        } else {
            throw new Exception("Error creating ticket: " . $conn->error);
        }
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();

        // Log error if necessary
        file_put_contents('error_log.txt', "Transaction failed: " . $e->getMessage() . "\n", FILE_APPEND);

        echo "Transaction failed: " . $e->getMessage();
    }
}

// Fetch priorities, approved users, and statuses for the form
$priorities = $conn->query("SELECT * FROM priorities");
$approved_users = $conn->query("SELECT * FROM approved_users");
$statuses = $conn->query("SELECT * FROM statuses");

$statuses_array = [];
$new_status_id = null;
while ($status = $statuses->fetch_assoc()) {
    $statuses_array[] = $status;
    if (strtolower($status['name']) === 'new') {
        $new_status_id = $status['id'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Create New Ticket</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .content {
            margin: 0 auto;
            padding: 20px;
            max-width: 1300px !important;
            /* Adjust max-width as needed */
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        form {
            width: 100%;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .form-group {
            margin: 10px;
            opacity: 0;
            animation: slideIn 0.5s forwards;
        }

        .form-group:nth-child(1) {
            animation-delay: 0.2s;
        }

        .form-group:nth-child(2) {
            animation-delay: 0.4s;
        }

        .form-group:nth-child(3) {
            animation-delay: 0.6s;
        }

        .form-group.col-md-12 {
            flex: 1 0 100%;
        }

        .form-group.col-md-4 {
            flex: 1 0 30%;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        button[type="submit"] {
            align-self: flex-end;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .success-message {
            display: none;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-group.col-md-4 {
                flex: 1 0 100%;
                /* Full width on smaller screens */
                margin-left: 0;
                margin-right: 0;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <?php include '../php/nav.php'; ?>
    <div class="container content col-md-12">

        <h2 class="mt-5">Create New Ticket</h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message" id="successMessage">
                <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); // Clear the message after displaying it
                ?>
            </div>
        <?php endif; ?>

        <form id="createTicketForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="priority">Priority</label>
                    <select class="form-control" id="priority" name="priority" required>
                        <?php while ($row = $priorities->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <?php foreach ($statuses_array as $status): ?>
                            <option value="<?php echo $status['id']; ?>" <?php echo $status['id'] == $new_status_id ? 'selected' : ''; ?>>
                                <?php echo $status['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="assigned_user">Assigned User</label>
                    <select class="form-control" id="assigned_user" name="assigned_user" required>
                        <?php while ($row = $approved_users->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" required></textarea>
                    <script>
                        CKEDITOR.replace('description');
                    </script>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
    <script>
        $(document).ready(function () {
            if ($('#successMessage').length) {
                $('#successMessage').fadeIn().delay(3000).fadeOut();
            }
        });
    </script>
</body>

</html>