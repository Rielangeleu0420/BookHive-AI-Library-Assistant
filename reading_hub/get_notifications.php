<?php
require_once 'functions.php';
global $conn;
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

$notifications = [];
$sql = "SELECT notif_id, type, message, date_sent, status FROM notifications WHERE user_id = ? ORDER BY date_sent DESC LIMIT 20";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

// Count unread
$unread_count = count(array_filter($notifications, fn($n) => $n['status'] === 'unread'));

echo json_encode(['success' => true, 'notifications' => $notifications, 'unread_count' => $unread_count]);
$conn->close();
?>