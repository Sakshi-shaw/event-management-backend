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

if ($_POST['token'] && $_POST['new_password']) {
    $token = $_POST['token'];
    $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    // Update password and clear the token
    $sql = "UPDATE students SET password = '$newPassword', reset_token = NULL, token_expiration = NULL WHERE reset_token = '$token'";
    $conn->query($sql);
    $sql = "UPDATE teachers SET password = '$newPassword', reset_token = NULL, token_expiration = NULL WHERE reset_token = '$token'";
    $conn->query($sql);

    echo "Password updated successfully!";
} else {
    echo "Invalid request.";
}

$conn->close();
?>
