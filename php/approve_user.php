<?php
session_start();
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Adjust path if needed

if (!isset($_SESSION['admin'])) {
    echo "Error: Unauthorized access.";
    exit();
}

function generatePassword($length = 8) {
    $numbers = '0123456789';
    $specialChars = '!@#$%^&*';
    $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    $password = '';

    // Add 2 random numbers
    for ($i = 0; $i < 2; $i++) {
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
    }

    // Add 2 random special characters
    for ($i = 0; $i < 2; $i++) {
        $password .= $specialChars[rand(0, strlen($specialChars) - 1)];
    }

    // Add remaining characters
    for ($i = 0; $i < ($length - 4); $i++) {
        $password .= $letters[rand(0, strlen($letters) - 1)];
    }

    // Shuffle the password to ensure randomness
    return str_shuffle($password);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = intval($_POST['user_id']);
    $role = $_POST['role'];

    // Validate role
    $valid_roles = ['Admin', 'Client', 'Developer'];
    if (!in_array($role, $valid_roles)) {
        echo "Invalid role.";
        exit();
    }

    // Fetch user details
    $sql = "SELECT * FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Generate new username and password
        $username = 'c_' . strtolower($user['name']);
        $password = generatePassword(8); // Generate a random 8-character password

        // Hash the password for storage
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $url = "http://192.168.130.208/project1/index.php";

        // Start transaction
        $conn->begin_transaction();

        try {
            // Move user to approved_users table with role
            $sql = "INSERT INTO approved_users (name, username, email, mobile_number, password, role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssss', $user['name'], $username, $user['email'], $user['mobile_number'], $hashed_password, $role);
            $stmt->execute();

            // Remove user from users table
            $sql = "DELETE FROM users WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();

            // Commit transaction
            $conn->commit();

            // Send approval email with username and password
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'mail.enovasolutions.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'production@enovasolutions.com';
                $mail->Password = 'Dollar$5';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('production@enovasolutions.com', 'Admin');
                $mail->addAddress($user['email']);

                $mail->isHTML(true);
                $mail->Subject = 'Your Profile is Approved';
                $mail->Body = 'Dear ' . $user['name'] . ',<br><br>Your profile has been approved. Here are your login details:<br>Username: ' . $username . '<br>Password: ' . $password . '<br>Site URL: ' . $url . '<br><br>Best regards,<br>Admin';

                $mail->send();

                $_SESSION['message'] = "User role updated and email sent successfully.";
                echo '<div class="alert alert-success">User role updated and email sent successfully.</div>';
            } catch (Exception $e) {
                $_SESSION['message'] = "User role updated but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                echo '<div class="alert alert-warning">User role updated but email could not be sent. Mailer Error: ' . $mail->ErrorInfo . '</div>';
            }

        } catch (Exception $e) {
            $conn->rollback();
            echo "Failed to update user role. Please try again.";
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "Invalid request.";
}
?>
