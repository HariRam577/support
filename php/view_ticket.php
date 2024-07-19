<?php
session_start();
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit();
}

// Get ticket ID from URL
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch ticket details including status ID
$sql = "SELECT tickets.subject, priorities.name as priority, tickets.description, tickets.submitted_date,
        users.name as assigned_by, users.email as assigned_by_email, assigned_user.name as assigned_to, assigned_user.email as assigned_email, tickets.status_id
        FROM tickets
        JOIN priorities ON tickets.priority_id = priorities.id
        JOIN approved_users AS users ON tickets.created_by = users.id
        JOIN approved_users AS assigned_user ON tickets.assigned_user_id = assigned_user.id
        WHERE tickets.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();

if (!$ticket) {
    echo "Error: Ticket not found.";
    exit();
}

// Fetch all statuses for the select options
$statuses_sql = "SELECT id, name FROM statuses";
$statuses_result = $conn->query($statuses_sql);
$statuses = [];
while ($row = $statuses_result->fetch_assoc()) {
    $statuses[$row['id']] = $row['name'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $status_id = $_POST['status'];
    $description = $_POST['description'];

    // Update ticket
    $update_sql = "UPDATE tickets SET subject = ?, status_id = ?, description = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sisi", $subject, $status_id, $description, $ticket_id);
    $update_stmt->execute();

    // Insert into ticket_updates
    $update_history_sql = "INSERT INTO ticket_updates (ticket_id, subject, status_id, assigned_to, description) VALUES (?, ?, ?, ?, ?)";
    $assigned_to = $ticket['assigned_by'];
    $history_stmt = $conn->prepare($update_history_sql);
    $history_stmt->bind_param("isiss", $ticket_id, $subject, $status_id, $assigned_to, $description);
    $history_stmt->execute();

    // Determine email recipient based on status_id
    if ($status_id == 1 || $status_id == 2) {
        $recipientEmail = $ticket['assigned_email']; // Assigned To's email
        $recipientName = $ticket['assigned_to']; // Assigned To's name
    } else if ($status_id == 3) {
        $recipientEmail = $ticket['assigned_by_email']; // Assigned By's email
        $recipientName = $ticket['assigned_by']; // Assigned By's name
    } else {
        $recipientEmail = null; // Handle other status_id values if needed
    }
    //    echo $recipientEmail;exit;
    // Send email to appropriate user using PHPMailer
    if ($recipientEmail) {
        $mail = new PHPMailer(true);
        try {
            $view_ticket_url = "http://192.168.130.208/project1/php/view_ticket.php?id=$ticket_id";

            // Server settings
            $mail->isSMTP();
            $mail->Host = 'mail.enovasolutions.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'production@enovasolutions.com';
            $mail->Password = 'Dollar$5';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('production@enovasolutions.com', 'Admin');
            $mail->addAddress($recipientEmail, $recipientName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Ticket Updated: ' . htmlspecialchars($ticket['subject']);
            $mail->Body = '
            <div style="border: 1px solid #ccc; padding: 20px; font-family: Arial, sans-serif; max-width: 600px; margin: auto;">
                <h2 style="color: #007bff;">Ticket Updated</h2>
                <p style="font-size: 16px; color: #333;">Dear ' . htmlspecialchars($recipientName) . ',</p>
                <p style="font-size: 16px; color: #333;">The following ticket has been updated:</p>
                <div style="border: 1px solid #eee; padding: 10px; margin-top: 20px;">
                    <p><strong>Subject:</strong> ' . htmlspecialchars($ticket['subject']) . '</p>
                    <p><strong>Status:</strong> ' . htmlspecialchars($statuses[$status_id]) . '</p>
                    <p><strong>Description:</strong><br>' . $description . '</p>
                    <p><strong>View Ticket:</strong> <a href="' . htmlspecialchars($view_ticket_url) . '">Click here</a></p>
                </div>
                <p style="font-size: 16px; color: #333;">Best regards,</p>
                <p style="font-size: 16px; color: #007bff;">Admin</p>
            </div>';

            $mail->send();
            echo 'Email sent successfully to ' . htmlspecialchars($recipientName);
        } catch (Exception $e) {
            echo 'Email could not be sent. Mailer Error: ', $mail->ErrorInfo;
        } catch (Exception $e) {
            // Log error if necessary
            file_put_contents('error_log.txt', "Message could not be sent to $recipientName ($recipientEmail). Mailer Error: {$e->getMessage()}\n", FILE_APPEND);
            echo "Message could not be sent to $recipientName. Mailer Error: {$e->getMessage()}";
        }
    } else {
        echo "No valid recipient email found for status_id: $status_id";
    }

    $_SESSION['success_message'] = 'Ticket updated successfully!';
    header("Location: view_ticket.php?id=$ticket_id");
    exit();
}

// Fetch ticket update history
$history_sql = "SELECT * FROM ticket_updates WHERE ticket_id = ?";
$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param("i", $ticket_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$history_entries = $history_result->fetch_all(MYSQLI_ASSOC);

// Fetch all statuses for the select options again for displaying in the form
$statuses = $conn->query("SELECT * FROM statuses");
$statuses_array = [];
while ($status = $statuses->fetch_assoc()) {
    $statuses_array[] = $status;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/font-awesome.css">
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <script src="../assets/js/font-awesome.js"></script>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <title>View Ticket</title>
    <style>
        /* Your existing CSS styles */
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
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .modal-body {
            height: 400px !important;
            overflow-y: scroll;
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
    <div class="container mt-5 content">
        <?php include 'nav.php'; ?>
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success" role="alert">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        ?>
        <h2 class="">View Ticket</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=$ticket_id"); ?>" method="POST">
            <div class="d-flex">
                <div class="form-group col-md-4">
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject"
                        value="<?php echo htmlspecialchars($ticket['subject']); ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="priority">Priority</label>
                    <input type="text" class="form-control" id="priority" name="priority"
                        value="<?php echo htmlspecialchars($ticket['priority']); ?>" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <?php foreach ($statuses_array as $status): ?>
                            <option value="<?php echo $status['id']; ?>" <?php if ($status['id'] === $ticket['status_id'])
                                   echo 'selected'; ?>><?php echo $status['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label for="assigned_to">Assigned To</label>
                <input type="text" class="form-control" id="assigned_to" name="assigned_to"
                    value="<?php echo htmlspecialchars($ticket['assigned_to']); ?>" readonly>
            </div>
            <div class="form-group col-md-12">
                <label for="assigned_by">Assigned by</label>
                <input type="text" class="form-control" id="assigned_by" name="assigned_by"
                    value="<?php echo htmlspecialchars($ticket['assigned_by']); ?>" readonly>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="5" required>
            <?php echo htmlspecialchars($ticket['description']); ?>
        </textarea>
                    <script>
                        CKEDITOR.replace('description');
                    </script>
                </div>
            </div>


            <div class="form-row">
                <button type="submit" class="btn btn-primary">Update Ticket</button>
                <!-- Revision Button with Icon -->
                <button type="button" class="btn btn-secondary ml-2 rev" data-toggle="modal"
                    data-target="#revisionModal">
                    <i class="fas fa-history"></i> View Revisions
                </button>
            </div>

        </form>

        <!-- Revision Modal -->
        <div class="modal fade" id="revisionModal" tabindex="-1" role="dialog" aria-labelledby="revisionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="revisionModalLabel">Ticket Revisions</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php if (!empty($history_entries)): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history_entries as $entry): ?>
                                        <?php
                                        // Set the default time zone
                                        date_default_timezone_set('Asia/Kolkata');

                                        // Convert and format the date
                                        $formatted_date = date('d-m-Y h:i A', strtotime($entry['updated_at']));
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($formatted_date); ?></td>
                                            <td><?php echo htmlspecialchars($entry['subject']); ?></td>
                                            <td><?php echo htmlspecialchars($statuses_array[array_search($entry['status_id'], array_column($statuses_array, 'id'))]['name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($entry['assigned_to']); ?></td>
                                            <td><?php echo strip_tags($entry['description']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>

                            </table>
                        <?php else: ?>
                            <p>No revisions found for this ticket.</p>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                $('.rev').click(function () {
                    $('#revisionModal').modal('show');
                });
                $('.close').click(function () {
                    $('#revisionModal').modal('hide');
                });
            });
        </script>
        <script>
            // Initialize CKEditor
            CKEDITOR.replace('description', {
                // readOnly: true
            });
        </script>
</body>

</html>