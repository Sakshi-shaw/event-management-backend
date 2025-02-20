<?php
require 'vendor/autoload.php';

$mail = new \PHPMailer\PHPMailer\PHPMailer();

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com'; // Use Gmail's SMTP server for testing
$mail->SMTPAuth = true;
$mail->Username = 'sakshishaw1375@gmail.com'; // Your email address
$mail->Password = 'suji ukrf bwtb lcpp'; // Your email password (for Gmail, consider using app password)
$mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->SMTPDebug = 2; // Enables verbose debugging output


$mail->setFrom('sakshishaw1375@gmail.com', 'Your Website');
$mail->addAddress('shawsakshi1375@gmail.com'); // Test email address
$mail->Subject = 'Test Email';
$mail->Body = 'This is a test email';

if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message sent successfully!';
}
