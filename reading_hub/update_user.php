<?php
require_once 'header.php';

// Redirect if not a librarian
if (getUserRole() !== 'librarian') {
    header("Location: student_dashboard.php");
    exit();
}

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $user_id = intval($_POST['user_id'] ?? 0);
    $full_name = trim($_POST['full_name'] ?? '');
    $year_level = trim($_POST['year_level'] ?? '');
    
    // Basic validation
    if ($user_id <= 0 || empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID or full name.']);
        exit();
    }
    
    // Fetch user role
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare error.']);
        exit();
    }
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Database execute error.']);
        $stmt->close();
        exit();
    }
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        $stmt->close();
        exit();
    }
    $user = $result->fetch_assoc();
    $role = $user['role'];
    $stmt->close();
    
    // Prevent librarians from editing other librarians
    if ($role === 'librarian' && $_SESSION['user_id'] !== $user_id) {
        echo json_encode(['success' => false, 'message' => 'You cannot edit other librarians.']);
        exit();
    }
    
    // Update based on role
    $updateSuccess = false;
    if ($role === 'student') {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, year_level = ? WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $full_name, $year_level, $user_id);
            $updateSuccess = $stmt->execute();
            $stmt->close();
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $full_name, $user_id);
            $updateSuccess = $stmt->execute();
            $stmt->close();
        }
    }
    
    if ($updateSuccess) {
        // Log audit (only if function exists)
        if (function_exists('logAudit')) {
            logAudit($_SESSION['user_id'], 'update_user', $user_id, 'Updated user details via AJAX');
        }
        echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $conn->error]);
    }
    $conn->close();
    exit();
}

// Non-AJAX: Redirect or handle direct access
header("Location: user_management.php");
exit();
?>