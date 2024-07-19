<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Get ticket ID from URL
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $subject = $_POST['subject'] ?? '';
    $status_id = $_POST['status'] ?? '';
    $description = $_POST['description'] ?? '';

    // Update ticket in database
    $update_sql = "UPDATE tickets SET subject = ?, status_id = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sisi", $subject, $status_id, $description, $ticket_id);

    if ($stmt->execute()) {
        // Redirect to view ticket page with success message
        header("Location: view_ticket.php?id={$ticket_id}&success=1");
        exit();
    } else {
        // Handle update failure
        $error_message = "Failed to update ticket. Please try again.";
    }
}

// Fetch ticket details
$sql = "SELECT tickets.subject, priorities.name as priority, tickets.description, tickets.submitted_date, 
               users.name as assigned_by, assigned_user.name as assigned_to, assigned_user.email as assigned_email, tickets.status_id
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <title>Update Ticket</title>
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
        <h2 class="content">Update Ticket</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id={$ticket_id}"; ?>" method="POST">
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
                        <?php foreach ($statuses as $status_id => $status_name): ?>
                            <option value="<?php echo $status_id; ?>" <?php if ($status_id === $ticket['status_id'])
                                echo 'selected'; ?>><?php echo $status_name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label for="assigned_to">Assigned To</label>
                <input type="text" class="form-control" id="assigned_to" name="assigned_to"
                    value="<?php echo htmlspecialchars($ticket['assigned_to']); ?>" readonly>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description"
                        rows="10"><?php echo htmlspecialchars($ticket['description']); ?></textarea>
                </div>
                <div class="form-row">
                    <button type="submit" class="btn btn-primary content">Update Ticket</button>
        </form>

    </div>

    <script>
        // Initialize CKEditor
        CKEDITOR.replace('description', {
            // readOnly: true
        });
    </script>
</body>

</html>
