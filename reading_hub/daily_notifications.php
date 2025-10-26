<?php
// daily_notifications.php - Run this as a cron job once per day (e.g., at 9 AM)
require_once 'config.php';
require_once 'functions.php';

// Log script execution
error_log("Daily notifications script started at " . date('Y-m-d H:i:s'));

// Get all overdue loans (status = 'overdue')
$overdue_sql = "SELECT l.user_id, COUNT(*) as overdue_count FROM loans l WHERE l.status = 'overdue' GROUP BY l.user_id";
$overdue_result = $conn->query($overdue_sql);

$overdue_notifications_sent = 0;
$due_soon_notifications_sent = 0;

while ($row = $overdue_result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $overdue_count = $row['overdue_count'];

    // Notify student (only if not already notified today)
    $check_student = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = 'overdue' AND DATE(date_sent) = CURDATE()";
    $stmt_check = $conn->prepare($check_student);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->fetch_row()[0] == 0) {
        $message = "You have $overdue_count overdue book(s). Please return them to avoid additional penalties.";
        $insert_sql = "INSERT INTO notifications (user_id, type, message, date_sent) VALUES (?, 'overdue', ?, NOW())";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("is", $user_id, $message);
        $stmt_insert->execute();
        $stmt_insert->close();
        $overdue_notifications_sent++;
    }
    $stmt_check->close();

    // Notify all librarians (without duplicate check for librarians)
    $librarians = $conn->query("SELECT user_id FROM users WHERE role = 'librarian'");
    while ($librarian = $librarians->fetch_assoc()) {
        $librarian_id = $librarian['user_id'];
        $lib_message = "User ID $user_id has $overdue_count overdue book(s).";
        $insert_lib = "INSERT INTO notifications (user_id, type, message, date_sent) VALUES (?, 'overdue', ?, NOW())";
        $stmt_lib = $conn->prepare($insert_lib);
        $stmt_lib->bind_param("is", $librarian_id, $lib_message);
        $stmt_lib->execute();
        $stmt_lib->close();
    }
}

// Get all loans due soon (within 3 days, status = 'borrowed')
$due_soon_sql = "SELECT l.user_id, COUNT(*) as due_soon_count FROM loans l WHERE l.status = 'borrowed' AND l.due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND l.due_date >= CURDATE() GROUP BY l.user_id";
$due_soon_result = $conn->query($due_soon_sql);

while ($row = $due_soon_result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $due_soon_count = $row['due_soon_count'];

    // Notify student (only if not already notified today)
    $check_student = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = 'due_soon' AND DATE(date_sent) = CURDATE()";
    $stmt_check = $conn->prepare($check_student);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->fetch_row()[0] == 0) {
        $message = "You have $due_soon_count book(s) due soon (within 3 days).";
        $insert_sql = "INSERT INTO notifications (user_id, type, message, date_sent) VALUES (?, 'due_soon', ?, NOW())";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("is", $user_id, $message);
        $stmt_insert->execute();
        $stmt_insert->close();
        $due_soon_notifications_sent++;
    }
    $stmt_check->close();

    // Notify all librarians (without duplicate check for librarians)
    $librarians = $conn->query("SELECT user_id FROM users WHERE role = 'librarian'");
    while ($librarian = $librarians->fetch_assoc()) {
        $librarian_id = $librarian['user_id'];
        $lib_message = "User ID $user_id has $due_soon_count book(s) due soon.";
        $insert_lib = "INSERT INTO notifications (user_id, type, message, date_sent) VALUES (?, 'due_soon', ?, NOW())";
        $stmt_lib = $conn->prepare($insert_lib);
        $stmt_lib->bind_param("is", $librarian_id, $lib_message);
        $stmt_lib->execute();
        $stmt_lib->close();
    }
}

// Log results
error_log("Daily notifications completed: $overdue_notifications_sent overdue notifications, $due_soon_notifications_sent due soon notifications sent");

$conn->close();
?>