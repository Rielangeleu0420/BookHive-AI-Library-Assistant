<?php
// delete_author.php
require_once 'functions.php';

if (!isLoggedIn() || getUserRole() !== 'librarian') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author_id = $_POST['author_id'] ?? null;
    
    if (!$author_id) {
        echo json_encode(['success' => false, 'message' => 'Author ID is required']);
        exit();
    }
    
    // Check if author has books
    $check_sql = "SELECT COUNT(*) as book_count FROM books WHERE author_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $author_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    if ($data['book_count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete author with existing books. Please reassign or delete the books first.']);
        exit();
    }
    
    // Get author name for audit log
    $author_sql = "SELECT author_name FROM authors WHERE author_id = ?";
    $stmt = $conn->prepare($author_sql);
    $stmt->bind_param("i", $author_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $author = $result->fetch_assoc();
    $stmt->close();
    
    // Delete author
    $delete_sql = "DELETE FROM authors WHERE author_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $author_id);
    
    if ($stmt->execute()) {
        logAudit($_SESSION['user_id'], 'delete_author', $author_id, 'Deleted author: ' . $author['author_name']);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete author']);
    }
    $stmt->close();
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>