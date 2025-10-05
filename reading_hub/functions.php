<?php
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function redirectToLogin() {
    header("Location: login.php");
    exit();
}

function redirectToDashboard() {
    if (getUserRole() === 'librarian') {
        header("Location: librarian_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

function logAudit($userId, $actionType, $targetId = null, $details = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action_type, target_id, details) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $actionType, $targetId, $details);
    $stmt->execute();
    $stmt->close();
}
?>