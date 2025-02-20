<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "roomallocation";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get email from the frontend
$data = json_decode(file_get_contents("php://input"));
$email = $data->email;

// Check if the email exists in either 'students' or 'teachers' table
$sql = "SELECT id FROM students WHERE email = '$email' UNION SELECT id FROM teachers WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Generate a unique token
    $token = bin2hex(random_bytes(50));
    
    // Store the token and expiration in the database
    $sql = "UPDATE students SET reset_token = '$token', token_expiration = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = '$email'";
    $conn->query($sql);
    $sql = "UPDATE teachers SET reset_token = '$token', token_expiration = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = '$email'";
    $conn->query($sql);

    // Send the reset email
    $resetLink = "http://localhost/room-allocation/backend/reset-password-form.php?token=$token";
    $subject = "Password Reset Request";
    $message = "To reset your password, click the link below:\n\n$resetLink";
    $headers = "From: noreply@roomallocation.com";

    if (mail($email, $subject, $message, $headers)) {
        echo json_encode(['success' => true, 'message' => 'Reset email sent.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Email not found.']);
}

$conn->close();
?>
