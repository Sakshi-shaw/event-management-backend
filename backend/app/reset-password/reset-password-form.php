<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "roomallocation";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
}

header('Content-Type: application/json');

$token = $_GET['token'] ?? null;

if ($token) {
    $stmt = $conn->prepare("SELECT id FROM students WHERE reset_token = ? AND token_expiration > NOW()
                            UNION
                            SELECT id FROM teachers WHERE reset_token = ? AND token_expiration > NOW()");
    $stmt->bind_param("ss", $token, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'status' => 'success',
            'form' => '<form action="update-password.php" method="POST">
                          <input type="hidden" name="token" value="' . htmlspecialchars($token) . '" />
                          <label for="new_password">New Password:</label>
                          <input type="password" name="new_password" required />
                          <button type="submit">Reset Password</button>
                       </form>'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'No token provided.']);
}

$conn->close();
?>
