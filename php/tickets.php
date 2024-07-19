<?php
session_start();
include '../php/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Fetch total number of tickets for pagination
$total_tickets_sql = "SELECT COUNT(*) AS total FROM tickets";
$result_total_tickets = $conn->query($total_tickets_sql);
$total_tickets_row = $result_total_tickets->fetch_assoc();
$total_tickets = $total_tickets_row['total'];

// Get the current page number from the GET parameter, default to 1 if not set
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$records_per_page = 12;
$offset = ($page - 1) * $records_per_page;

// Fetch tickets query with pagination
$sql = "SELECT tickets.id, tickets.subject, priorities.name AS priority, tickets.submitted_date, 
               users.name AS assigned_by, assigned_user.name AS assigned_to, assigned_by_user.name AS assigned_by_user,
               statuses.name AS status_name
        FROM tickets
        JOIN priorities ON tickets.priority_id = priorities.id
        JOIN approved_users AS users ON tickets.created_by = users.id
        JOIN approved_users AS assigned_user ON tickets.assigned_user_id = assigned_user.id
        LEFT JOIN approved_users AS assigned_by_user ON tickets.assigned_by_user_id = assigned_by_user.id
        JOIN statuses ON tickets.status_id = statuses.id
        ORDER BY tickets.id DESC
        LIMIT $records_per_page OFFSET $offset";

// Execute the query
$result_tickets = $conn->query($sql);

// Check if query execution was successful
if (!$result_tickets) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/font-awesome.css">
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/font-awesome.js"></script>
    <style>
        .pagination {
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <?php include 'nav.php'; ?>

        <h2 class="content">Tickets</h2>

        <table id="tickets-table" class="table table-bordered content">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Priority</th>
                    <th>Assigned By</th>
                    <th>Assigned To</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
  // Database connection and query execution
// Assuming $conn is your database connection and $result_tickets is your result set

if ($result_tickets->num_rows > 0) {
    while ($row_ticket = $result_tickets->fetch_assoc()) {
        echo "<pre>";print_r($row_ticket);exit;
        // Convert submitted_date from UTC to IST (UTC+5:30) and format it in 12-hour format
        echo "<tr>
            <td>" . htmlspecialchars($row_ticket['id']) . "</td>
            <td><a href='view_ticket.php?id=" . htmlspecialchars($row_ticket['id']) . "'>" . htmlspecialchars($row_ticket['subject']) . "</a></td>
            <td>" . htmlspecialchars($row_ticket['priority']) . "</td>
            <td>" . htmlspecialchars($row_ticket['assigned_by']) . "</td>
            <td>" . htmlspecialchars($row_ticket['assigned_to']) . "</td>
            <td>" . htmlspecialchars($row_ticket['submitted_date']) . "</td>
        </tr>";
    }
}

else {
                    echo "<tr><td colspan='6'>No tickets found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div id="pagination" class="pagination">
            <?php
            // Calculate total pages
            $total_pages = ceil($total_tickets / $records_per_page);

            // Previous and Next buttons
            if ($page > 1) {
                echo "<a href='tickets.php?page=1'>&laquo; First</a>";
                $prev_page = $page - 1;
                echo "<a href='tickets.php?page=$prev_page'>&lsaquo; Previous</a>";
            }

            // Page numbers
            for ($i = max(1, $page - 2); $i <= min($page + 2, $total_pages); $i++) {
                echo "<a href='tickets.php?page=$i'>$i</a> ";
            }

            // Next and Last buttons
            if ($page < $total_pages) {
                $next_page = $page + 1;
                echo "<a href='tickets.php?page=$next_page'>Next &rsaquo;</a>";
                echo "<a href='tickets.php?page=$total_pages'>Last &raquo;</a>";
            }
            ?>
        </div>
    </div>
</body>

</html>
