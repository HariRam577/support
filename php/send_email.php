<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode($argv[1], true);
$id = $data['id'];
$user_email = $data['email'];

$mail = new PHPMailer(true);
try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = 'mail.enovasolutions.com'; // Set the SMTP server to send through
    $mail->SMTPAuth = true;
    $mail->Username = 'production@enovasolutions.com'; // SMTP username
    $mail->Password = 'Dollar$5'; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    //Recipients
    $mail->setFrom('production@enovasolutions.com', 'Admin');
    $mail->addAddress($user_email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Profile is Approved';
    $mail->Body    = 'Dear User,<br><br>Your profile has been approved. You can now access all features of our website.<br><br>Best regards,<br>Hari Ram';

    $mail->send();
} catch (Exception $e) {
    // Log error if necessary
    file_put_contents('error_log.txt', "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n", FILE_APPEND);
}
?>
