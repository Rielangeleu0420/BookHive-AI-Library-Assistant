<?php
require_once 'functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getUserRole() !== 'librarian') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$userId = $data['user_id'] ?? 0;
$newPassword = $data['new_password'] ?? '';

if ($userId <= 0 || empty($newPassword) || strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID or password.']);
    exit();
}

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the password in the database
$sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $hashedPassword, $userId);

if ($stmt->execute()) {
    logAudit($_SESSION['user_id'], 'change_password', $userId, 'Password changed for user ID ' . $userId);
    echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
}

$stmt->close();
$conn->close();
?>