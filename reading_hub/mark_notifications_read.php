<?php
require_once 'functions.php';
global $conn;
if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
$notif_id = $data['notif_id'] ?? null;

if ($notif_id) {
    // Mark single notification
    $sql = "UPDATE notifications SET status = 'read' WHERE notif_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notif_id, $user_id);
} else {
    // Mark all as read
    $sql = "UPDATE notifications SET status = 'read' WHERE user_id = ? AND status = 'unread'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$success = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $success]);
?>